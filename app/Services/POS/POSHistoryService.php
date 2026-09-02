<?php

namespace App\Services\POS;

use App\Models\Order;
use App\Models\ProductBarcode;
use App\Models\POSReturn;
use App\Models\HoldBill;
use App\Models\Account;
use App\Enums\OrderStatus;
use App\Services\Accounting\VoucherService;
use Illuminate\Support\Facades\DB;
use Exception;

class POSHistoryService
{
    protected $voucherService;

    public function __construct(VoucherService $voucherService)
    {
        $this->voucherService = $voucherService;
    }

    /**
     * Resolve matching counter IDs for a given counter ID (mapping across historical/synced counters).
     */
    public static function resolveMatchingCounterIds($counterId): array
    {
        if (empty($counterId)) {
            return [];
        }

        $selectedCounter = \App\Models\CounterMaster::find($counterId);
        if (!$selectedCounter) {
            return [(int)$counterId];
        }

        $code = $selectedCounter->code;
        $nameNorm = preg_replace('/[^a-z0-9]/', '', strtolower($selectedCounter->counter_name));
        $shortNorm = preg_replace('/[^a-z0-9]/', '', strtolower($selectedCounter->counter_short_name));

        $matchingIds = \App\Models\CounterMaster::all()->filter(function ($item) use ($selectedCounter, $code, $nameNorm, $shortNorm) {
            $iName = preg_replace('/[^a-z0-9]/', '', strtolower($item->counter_name));
            $iShort = preg_replace('/[^a-z0-9]/', '', strtolower($item->counter_short_name));
            return $item->id == $selectedCounter->id
                || (!empty($code) && $item->code === $code)
                || (!empty($nameNorm) && ($iName === $nameNorm || $iShort === $nameNorm))
                || (!empty($shortNorm) && ($iName === $shortNorm || $iShort === $shortNorm));
        })->pluck('id')->toArray();

        if (empty($matchingIds)) {
            $matchingIds = [(int)$selectedCounter->id];
        }

        return array_values(array_unique($matchingIds));
    }

    /**
     * Fetch list of previous billing history invoices with server-side filters and optimization.
     */
    public function getHistory(array $filters, $shopId, $perPage = 15)
    {
        // Ignore PosOrderFalse scope to fetch POS orders
        $query = Order::withoutGlobalScopes()
            ->where('shop_id', $shopId)
            ->where('pos_order', true)
            ->with(['customer.user', 'payments', 'counter', 'posReturns', 'products', 'salesman', 'cashier'])
            ->orderBy('created_at', 'desc');

        // Apply filters
        if (!empty($filters['counter_id'])) {
            $matchingCounterIds = self::resolveMatchingCounterIds($filters['counter_id']);
            if (!empty($matchingCounterIds)) {
                $query->whereIn('counter_id', $matchingCounterIds);
            }
        }

        // Unified Search Filter
        $search = trim($filters['search'] ?? '');
        if (empty($search)) {
            // Check if invoice_no or customer was passed individually
            $inv = trim($filters['invoice_no'] ?? '');
            $cust = trim($filters['customer'] ?? '');
            if ($inv === $cust && !empty($inv)) {
                $search = $inv;
            }
        }

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                if (str_contains($search, '-')) {
                    $lastHyphenPos = strrpos($search, '-');
                    $prefix = substr($search, 0, $lastHyphenPos);
                    $code = substr($search, $lastHyphenPos + 1);
                    $q->where(function ($sq) use ($prefix, $code) {
                        $sq->where('prefix', $prefix)->where('order_code', $code);
                    });
                } else {
                    $q->where('order_code', 'like', "%{$search}%");
                }

                $q->orWhereHas('customer.user', function ($cq) use ($search) {
                    $cq->where('name', 'like', "%{$search}%")
                       ->orWhere('phone', 'like', "%{$search}%");
                });

                $q->orWhereHas('products', function ($pq) use ($search) {
                    $pq->where('name', 'like', "%{$search}%")
                       ->orWhere('barcode_number', 'like', "%{$search}%");
                });
            });
        } else {
            // Check individual filters if specific fields were provided without a unified search term
            if (!empty($filters['invoice_no']) && empty($filters['customer'])) {
                $invoice = $filters['invoice_no'];
                if (str_contains($invoice, '-')) {
                    $lastHyphenPos = strrpos($invoice, '-');
                    $prefix = substr($invoice, 0, $lastHyphenPos);
                    $code = substr($invoice, $lastHyphenPos + 1);
                    $query->where('prefix', $prefix)->where('order_code', $code);
                } else {
                    $query->where('order_code', 'like', "%{$invoice}%");
                }
            }

            if (!empty($filters['customer']) && empty($filters['invoice_no'])) {
                $cust = $filters['customer'];
                $query->whereHas('customer.user', function ($q) use ($cust) {
                    $q->where('name', 'like', "%{$cust}%")
                      ->orWhere('phone', 'like', "%{$cust}%");
                });
            }
        }

        if (!empty($filters['barcode'])) {
            $bc = $filters['barcode'];
            $query->whereHas('products', function ($q) use ($bc) {
                $q->where('barcode_number', $bc);
            });
        }

        if (!empty($filters['product_name'])) {
            $name = $filters['product_name'];
            $query->whereHas('products', function ($q) use ($name) {
                $q->where('name', 'like', "%{$name}%");
            });
        }

        if (!empty($filters['salesman_id'])) {
            $salesmanId = $filters['salesman_id'];
            $query->whereHas('products', function ($q) use ($salesmanId) {
                $q->where('order_products.salesman_id', $salesmanId);
            });
        }

        if (!empty($filters['payment_mode'])) {
            $pm = $filters['payment_mode'];
            $query->where('payment_method', 'like', "%{$pm}%");
        }

        if (!empty($filters['status'])) {
            $st = strtolower($filters['status']);
            if ($st === 'delivered' || $st === 'completed') {
                $query->where('order_status', OrderStatus::DELIVERED->value);
            } else if ($st === 'canceled' || $st === 'cancelled') {
                $query->where('order_status', OrderStatus::CANCELLED->value);
            } else {
                $query->where('order_status', $filters['status']);
            }
        }

        if (!empty($filters['date_from']) && !empty($filters['date_to'])) {
            $query->whereBetween('created_at', [$filters['date_from'] . ' 00:00:00', $filters['date_to'] . ' 23:59:59']);
        }

        if (!empty($filters['amount'])) {
            $query->where('payable_amount', '>=', (float)$filters['amount']);
        }

        return $query->paginate($perPage);
    }

    /**
     * Fetch key summary stats.
     */
    public function getStats($shopId, $counterId = null): array
    {
        $today = now()->format('Y-m-d');

        $salesTodayQuery = Order::withoutGlobalScopes()
            ->where('shop_id', $shopId)
            ->where('pos_order', true)
            ->whereDate('created_at', $today)
            ->where('order_status', OrderStatus::DELIVERED->value);

        $billsTodayQuery = Order::withoutGlobalScopes()
            ->where('shop_id', $shopId)
            ->where('pos_order', true)
            ->whereDate('created_at', $today);

        $holdsActiveQuery = HoldBill::where('shop_id', $shopId)
            ->where('status', 'active');

        $voidedBillsQuery = Order::withoutGlobalScopes()
            ->where('shop_id', $shopId)
            ->where('pos_order', true)
            ->where('order_status', OrderStatus::CANCELLED->value);

        $returnsCountQuery = POSReturn::where('shop_id', $shopId)
            ->whereDate('created_at', $today);

        if (!empty($counterId)) {
            $matchingCounterIds = self::resolveMatchingCounterIds($counterId);
            if (!empty($matchingCounterIds)) {
                $salesTodayQuery->whereIn('counter_id', $matchingCounterIds);
                $billsTodayQuery->whereIn('counter_id', $matchingCounterIds);
                $holdsActiveQuery->whereIn('counter_id', $matchingCounterIds);
                $voidedBillsQuery->whereIn('counter_id', $matchingCounterIds);
                $returnsCountQuery->whereIn('counter_id', $matchingCounterIds);
            }
        }

        $salesToday = $salesTodayQuery->sum('payable_amount');
        $billsToday = $billsTodayQuery->count();
        $holdsActive = $holdsActiveQuery->count();
        $voidedBills = $voidedBillsQuery->count();
        $returnsCount = $returnsCountQuery->count();

        $avgBill = $billsToday > 0 ? ($salesToday / $billsToday) : 0;

        return [
            'sales_today' => (float)$salesToday,
            'bills_today' => (int)$billsToday,
            'holds_active' => (int)$holdsActive,
            'cancelled_bills' => (int)$voidedBills,
            'average_bill' => (float)$avgBill,
            'returns_today' => (int)$returnsCount,
        ];
    }

    /**
     * Void (Cancel) order and revert accounting ledger journal and inventory stock.
     */
    public function voidOrder($orderId, $shopId): Order
    {
        return DB::transaction(function () use ($orderId, $shopId) {
            $order = Order::withoutGlobalScopes()
                ->where('shop_id', $shopId)
                ->where('id', $orderId)
                ->lockForUpdate()
                ->firstOrFail();

            if ($order->order_status->value === OrderStatus::CANCELED->value) {
                throw new Exception("This order has already been voided/cancelled.");
            }

            // Mark order cancelled
            $order->update([
                'order_status' => OrderStatus::CANCELED->value
            ]);

            // Revert stock & marks barcodes unsold
            $products = DB::table('order_products')->where('order_id', $order->id)->get();
            foreach ($products as $op) {
                $p = \App\Models\Product::withoutGlobalScopes()->find($op->product_id);
                if ($p) {
                    $p->update([
                        'quantity' => $p->quantity + $op->quantity
                    ]);
                }

                if ($op->barcode_number) {
                    ProductBarcode::where('barcode_number', $op->barcode_number)
                        ->update(['is_sold' => 0]);
                }
            }

            // Reverse Double-Entry Journal Entry
            $salesAccountId = Account::whereIn('code', ['SALES_POS', 'SALES'])->value('id') ?? Account::where('name', 'like', '%Sales%')->value('id') ?? 9;
            $cashAccountId  = Account::whereIn('code', ['CASH_DRAWER', 'CASH', 'PETTY_CASH'])->value('id') ?? Account::where('name', 'like', '%Cash%')->value('id') ?? 16;
            $bankAccountId  = Account::whereIn('code', ['BANK_HDFC', 'BANK'])->value('id') ?? Account::where('name', 'like', '%Bank%')->value('id') ?? 18;
            $cgstOutputId   = Account::whereIn('code', ['CGST_OUT', 'GST_OUT_C'])->value('id') ?? Account::where('name', 'like', '%CGST Output%')->value('id') ?? 1;
            $sgstOutputId   = Account::whereIn('code', ['SGST_OUT', 'GST_OUT_S'])->value('id') ?? Account::where('name', 'like', '%SGST Output%')->value('id') ?? 2;

            $entries = [];
            $totalTax = (float)$order->tax_amount;
            $payable = (float)$order->payable_amount;
            $salesAmt = $payable - $totalTax;

            // Debit Sales (Sales reversal)
            if ($salesAmt > 0) {
                $entries[] = [
                    'account_id' => $salesAccountId,
                    'type' => 'Dr',
                    'amount' => $salesAmt,
                    'description' => 'Void order sales reversal for #' . $order->prefix . '-' . $order->order_code
                ];
            }

            // Debit CGST / SGST output (Tax reversal)
            if ($totalTax > 0) {
                $taxSplit = round($totalTax / 2, 2);
                if ($taxSplit > 0) {
                    $entries[] = [
                        'account_id' => $cgstOutputId,
                        'type' => 'Dr',
                        'amount' => $taxSplit,
                        'description' => 'Void order CGST reversal for #' . $order->prefix . '-' . $order->order_code
                    ];
                    $entries[] = [
                        'account_id' => $sgstOutputId,
                        'type' => 'Dr',
                        'amount' => $taxSplit,
                        'description' => 'Void order SGST reversal for #' . $order->prefix . '-' . $order->order_code
                    ];
                }
            }

            // Credit Cash / Bank depending on order payment method
            $paymentMethod = $order->payment_method->value ?? $order->payment_method;
            if ($paymentMethod === 'Split Payment') {
                // Tally each split payment
                $payments = $order->payments;
                foreach ($payments as $sp) {
                    $accId = ($sp->payment_method->value === \App\Enums\PaymentMethod::CASH->value) ? $cashAccountId : $bankAccountId;
                    $entries[] = [
                        'account_id' => $accId,
                        'type' => 'Cr',
                        'amount' => (float)$sp->amount,
                        'description' => 'Void order payment cash/bank reversal for #' . $order->prefix . '-' . $order->order_code
                    ];
                }
            } else {
                $accId = ($paymentMethod === \App\Enums\PaymentMethod::CASH->value) ? $cashAccountId : $bankAccountId;
                $entries[] = [
                    'account_id' => $accId,
                    'type' => 'Cr',
                    'amount' => (float)$order->payments->sum('amount'),
                    'description' => 'Void order payment reversal for #' . $order->prefix . '-' . $order->order_code
                ];
            }

            // Post reversal Journal Voucher
            $date = now();
            $fy = \App\Models\FinancialYear::where('start_date', '<=', $date)
                ->where('end_date', '>=', $date)
                ->where('is_active', 1)
                ->first();

            if ($fy) {
                $this->voucherService->postJournalVoucher($entries, $order->shop, $fy, 'JV');
            }

            return $order;
        });
    }
}
