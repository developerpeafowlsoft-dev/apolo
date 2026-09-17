<?php

namespace App\Services\Accounting;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Models\Account;
use App\Models\FinancialYear;
use App\Models\Order;
use App\Models\State;
use App\Models\Voucher;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class OnlineOrderAccountingService
{
    protected VoucherService $voucherService;

    public function __construct(VoucherService $voucherService)
    {
        $this->voucherService = $voucherService;
    }

    /**
     * Standard Indian GST 2-digit State Code Mapping.
     */
    public function getStateCodeMap(): array
    {
        return [
            'Jammu and Kashmir' => 1, 'Himachal Pradesh' => 2, 'Punjab' => 3, 'Chandigarh' => 4,
            'Uttarakhand' => 5, 'Haryana' => 6, 'Delhi' => 7, 'Rajasthan' => 8,
            'Uttar Pradesh' => 9, 'Bihar' => 10, 'Sikkim' => 11, 'Arunachal Pradesh' => 12,
            'Nagaland' => 13, 'Manipur' => 14, 'Mizoram' => 15, 'Tripura' => 16,
            'Meghalaya' => 17, 'Assam' => 18, 'West Bengal' => 19, 'Jharkhand' => 20,
            'Odisha' => 21, 'Chhattisgarh' => 22, 'Madhya Pradesh' => 23, 'Gujarat' => 24,
            'Maharashtra' => 27, 'Andhra Pradesh' => 28, 'Karnataka' => 29, 'Goa' => 30,
            'Kerala' => 32, 'Tamil Nadu' => 33, 'Telangana' => 36,
        ];
    }

    /**
     * Dynamically resolve Seller / Origin GST State Code.
     */
    public function getSellerGstStateCode(?\App\Models\Shop $shop = null): int
    {
        if (!$shop) {
            $shop = generaleSetting('shop');
        }

        // 1. Extract 2-digit state prefix if GSTIN is registered
        $gstin = $shop?->gst_no ?? generaleSetting('setting')?->gstin;
        if ($gstin && strlen(trim($gstin)) >= 2 && is_numeric(substr(trim($gstin), 0, 2))) {
            return (int) substr(trim($gstin), 0, 2);
        }

        // 2. Extract state from shop address or general settings
        $addressText = $shop?->address ?? generaleSetting('setting')?->address ?? '';
        if (!empty($addressText)) {
            foreach ($this->getStateCodeMap() as $stateName => $code) {
                if (stripos($addressText, $stateName) !== false) {
                    return $code;
                }
            }
        }

        return 24; // Default fallback to Gujarat
    }

    /**
     * Dynamically resolve Customer Destination GST State Code.
     */
    public function getDestinationGstStateCode(Order $order): int
    {
        if (!$order->address_id && $order->pos_order) {
            return $this->getSellerGstStateCode($order->shop);
        }

        if (!$order->relationLoaded('address')) {
            $order->load('address');
        }

        $address = $order->address;
        if (!$address) {
            return $this->getSellerGstStateCode($order->shop);
        }

        $stateText = $address->state ?? $address->area ?? '';
        if (empty($stateText)) {
            return $this->getSellerGstStateCode($order->shop);
        }

        foreach ($this->getStateCodeMap() as $stateName => $code) {
            if (stripos($stateText, $stateName) !== false) {
                return $code;
            }
        }

        $stateModel = State::where('name', 'LIKE', '%' . trim($stateText) . '%')->first();
        if ($stateModel && isset($this->getStateCodeMap()[$stateModel->name])) {
            return $this->getStateCodeMap()[$stateModel->name];
        }

        return $this->getSellerGstStateCode($order->shop);
    }

    /**
     * Resolve Place of Supply dynamically: Seller State Code == Destination State Code ? Intra-State : Inter-State.
     */
    public function resolvePlaceOfSupply(Order $order): string
    {
        $sellerCode = $this->getSellerGstStateCode($order->shop);
        $destinationCode = $this->getDestinationGstStateCode($order);

        return ($sellerCode === $destinationCode) ? 'intra_state' : 'inter_state';
    }

    /**
     * Alias for destination state code.
     */
    public function getGstStateCode(Order $order): int
    {
        return $this->getDestinationGstStateCode($order);
    }

    /**
     * Post a Sales Journal Voucher for an online web order (Prepaid or COD Delivered).
     * Strictly idempotent: will never post duplicate vouchers.
     */
    public function postOnlineOrderSalesVoucher(Order $order): ?Voucher
    {
        // 1. Idempotency Check: if voucher already exists on order or in DB, return it
        if ($order->voucher_id) {
            $existingVoucher = Voucher::find($order->voucher_id);
            if ($existingVoucher) {
                return $existingVoucher;
            }
        }

        $orderIdentifier = '#' . $order->prefix . $order->order_code;
        $duplicateCheck = Voucher::where('shop_id', $order->shop_id)
            ->where('voucher_type', 'Sales')
            ->where('narration', 'like', "%{$orderIdentifier}%")
            ->first();

        if ($duplicateCheck) {
            $order->update(['voucher_id' => $duplicateCheck->id]);
            return $duplicateCheck;
        }

        // 2. Validate Order State: Only post if Paid or Delivered
        $paymentStatusStr = is_object($order->payment_status) ? $order->payment_status->value : (string)$order->payment_status;
        $orderStatusStr   = is_object($order->order_status) ? $order->order_status->value : (string)$order->order_status;

        $isPaid = strtolower($paymentStatusStr) === 'paid' || $paymentStatusStr === PaymentStatus::PAID->value;
        $isDelivered = strtolower($orderStatusStr) === 'delivered' || $orderStatusStr === OrderStatus::DELIVERED->value;

        if (!$isPaid && !$isDelivered) {
            // Unconfirmed / Pending COD order: Do not recognize revenue prematurely
            return null;
        }

        return DB::transaction(function () use ($order, $orderIdentifier) {
            $date = $order->created_at ? $order->created_at->toDateString() : now()->toDateString();

            $financialYear = FinancialYear::where('start_date', '<=', $date)
                ->where('end_date', '>=', $date)
                // Which year a transaction BELONGS to is decided by its date alone.
                // Filtering on is_active here sent every backdated entry to the
                // fallback year once only the current year was left active.
                ->first();

            $fyId = $financialYear ? $financialYear->id : 1;

            $placeOfSupply = $this->resolvePlaceOfSupply($order);

            // Accounts resolution from Chart of Accounts
            $acc = $this->resolveAccountIds([
                'sales_web'       => ['SALES_WEB', 'SALES_POS'],
                'gateway_clearing'=> ['PG_CLEARING', 'BANK_HDFC'],
                'cod_receivable'  => ['CUST_WEB', 'CUST_WALKIN'],
                'delivery_income' => ['REV_DELIVERY'],
                'cgst_output'     => ['CGST_OUT'],
                'sgst_output'     => ['SGST_OUT'],
                'igst_output'     => ['IGST_OUT'],
                'round_off'       => ['EXP_ROF'],
            ]);
            $salesWebAccountId = $acc['sales_web'];
            $gatewayClearingId = $acc['gateway_clearing'];
            $codReceivableId   = $acc['cod_receivable'];
            $deliveryIncomeId  = $acc['delivery_income'];
            $cgstOutputId      = $acc['cgst_output'];
            $sgstOutputId      = $acc['sgst_output'];
            $igstOutputId      = $acc['igst_output'];
            $roundAccountId    = $acc['round_off'];

            $isCod = strtolower((string)(is_object($order->payment_method) ? $order->payment_method->value : $order->payment_method)) === 'cash payment'
                || strtolower((string)(is_object($order->payment_method) ? $order->payment_method->value : $order->payment_method)) === 'cash';

            $debitAccountId = $isCod ? $codReceivableId : $gatewayClearingId;
            $debitDesc      = $isCod ? "COD Courier Receivable for {$orderIdentifier}" : "Payment Gateway Settlement for {$orderIdentifier}";

            $totalPayable   = (float)$order->payable_amount;
            $deliveryCharge = (float)($order->delivery_charge ?? 0);
            $totalTaxAmt    = (float)($order->tax_amount ?? 0);
            $productTotal   = (float)($order->total_amount ?? ($totalPayable - $deliveryCharge));

            // Product Taxable Value: Product selling price is tax-inclusive
            $taxableProductAmount = max(0, round($productTotal - $totalTaxAmt, 2));

            $entries = [];

            // 1. DEBIT: Receivable / Gateway Clearing (Total customer collected amount)
            $entries[] = [
                'account_id' => $debitAccountId,
                'type' => 'Dr',
                'amount' => $totalPayable,
                'description' => $debitDesc,
            ];

            // 2. CREDIT: E-Commerce Product Sales (Taxable amount)
            if ($taxableProductAmount > 0) {
                $entries[] = [
                    'account_id' => $salesWebAccountId,
                    'type' => 'Cr',
                    'amount' => $taxableProductAmount,
                    'description' => "Online Sales Revenue for {$orderIdentifier}",
                ];
            }

            // 3. CREDIT: Delivery Charge Income (Customer shipping fee)
            if ($deliveryCharge > 0) {
                $entries[] = [
                    'account_id' => $deliveryIncomeId,
                    'type' => 'Cr',
                    'amount' => $deliveryCharge,
                    'description' => "Customer Delivery Charge for {$orderIdentifier}",
                ];
            }

            // 4. CREDIT: Output GST (Intra-State: CGST+SGST, Inter-State: IGST)
            if ($totalTaxAmt > 0) {
                if ($placeOfSupply === 'intra_state') {
                    $halfTax = round($totalTaxAmt / 2, 2);
                    $remainingTax = round($totalTaxAmt - $halfTax, 2);
                    if ($halfTax > 0) {
                        $entries[] = [
                            'account_id' => $cgstOutputId,
                            'type' => 'Cr',
                            'amount' => $halfTax,
                            'description' => "Output CGST for {$orderIdentifier}",
                        ];
                    }
                    if ($remainingTax > 0) {
                        $entries[] = [
                            'account_id' => $sgstOutputId,
                            'type' => 'Cr',
                            'amount' => $remainingTax,
                            'description' => "Output SGST for {$orderIdentifier}",
                        ];
                    }
                } else {
                    $entries[] = [
                        'account_id' => $igstOutputId,
                        'type' => 'Cr',
                        'amount' => $totalTaxAmt,
                        'description' => "Output IGST (Inter-State) for {$orderIdentifier}",
                    ];
                }
            }

            // 5. Minor paise Round Off adjustment
            $drSum = 0.0; $crSum = 0.0;
            foreach ($entries as $ent) {
                if ($ent['type'] === 'Dr') $drSum += $ent['amount']; else $crSum += $ent['amount'];
            }
            $roundOff = round($drSum - $crSum, 2);
            if (abs($roundOff) >= 0.01) {
                $entries[] = [
                    'account_id' => $roundAccountId,
                    'type' => $roundOff > 0 ? 'Cr' : 'Dr',
                    'amount' => abs($roundOff),
                    'description' => 'Round off adjustment',
                ];
            }

            $voucher = $this->voucherService->create([
                'voucher_type' => 'Sales',
                'date' => $date,
                'narration' => "Online Order Sales Voucher for {$orderIdentifier}",
                'shop_id' => $order->shop_id,
                'financial_year_id' => $fyId,
                'seq_prefix' => 'WEB-' . $order->shop_id . '-',
                'entries' => $entries,
            ]);

            $order->update(['voucher_id' => $voucher->id]);

            return $voucher;
        });
    }

    /**
     * Post a Credit Note accounting reversal when an accounted online order is cancelled/returned.
     */
    public function postOnlineOrderCancellationVoucher(Order $order): ?Voucher
    {
        if (!$order->voucher_id) {
            // Order was never recognized in accounting, no reversal needed
            return null;
        }

        $orderIdentifier = '#' . $order->prefix . $order->order_code;
        $existingCN = Voucher::where('shop_id', $order->shop_id)
            ->where('voucher_type', 'Credit Note')
            ->where('narration', 'like', "%Cancellation Credit Note for {$orderIdentifier}%")
            ->first();

        if ($existingCN) {
            return $existingCN;
        }

        return DB::transaction(function () use ($order, $orderIdentifier) {
            $date = now()->toDateString();
            $financialYear = FinancialYear::where('start_date', '<=', $date)
                ->where('end_date', '>=', $date)
                // Which year a transaction BELONGS to is decided by its date alone.
                // Filtering on is_active here sent every backdated entry to the
                // fallback year once only the current year was left active.
                ->first();

            $fyId = $financialYear ? $financialYear->id : 1;
            $placeOfSupply = $this->resolvePlaceOfSupply($order);

            $acc = $this->resolveAccountIds([
                'sales_return'    => ['SALES_RET', 'SALES_WEB'],
                'gateway_clearing'=> ['PG_CLEARING', 'BANK_HDFC'],
                'cod_receivable'  => ['CUST_WEB', 'CUST_WALKIN'],
                'delivery_income' => ['REV_DELIVERY'],
                'cgst_output'     => ['CGST_OUT'],
                'sgst_output'     => ['SGST_OUT'],
            ]);
            $salesReturnAccountId = $acc['sales_return'];
            $gatewayClearingId    = $acc['gateway_clearing'];
            $codReceivableId      = $acc['cod_receivable'];
            $deliveryIncomeId     = $acc['delivery_income'];
            $cgstOutputId         = $acc['cgst_output'];
            $sgstOutputId         = $acc['sgst_output'];
            $igstOutputId         = Account::where('code', 'IGST_OUT')->value('id') ?? 3;
            $roundAccountId       = Account::where('code', 'EXP_ROF')->value('id') ?? 31;

            $isCod = strtolower((string)(is_object($order->payment_method) ? $order->payment_method->value : $order->payment_method)) === 'cash payment'
                || strtolower((string)(is_object($order->payment_method) ? $order->payment_method->value : $order->payment_method)) === 'cash';

            $creditAccountId = $isCod ? $codReceivableId : $gatewayClearingId;

            $totalPayable   = (float)$order->payable_amount;
            $deliveryCharge = (float)($order->delivery_charge ?? 0);
            $totalTaxAmt    = (float)($order->tax_amount ?? 0);
            $productTotal   = (float)($order->total_amount ?? ($totalPayable - $deliveryCharge));

            $taxableProductAmount = max(0, round($productTotal - $totalTaxAmt, 2));

            $entries = [];

            // 1. DEBIT: Sales Return A/c (Reversing product sales)
            if ($taxableProductAmount > 0) {
                $entries[] = [
                    'account_id' => $salesReturnAccountId,
                    'type' => 'Dr',
                    'amount' => $taxableProductAmount,
                    'description' => "Online Sales Return reversal for {$orderIdentifier}",
                ];
            }

            // 2. DEBIT: Delivery Charge Income reversal
            if ($deliveryCharge > 0) {
                $entries[] = [
                    'account_id' => $deliveryIncomeId,
                    'type' => 'Dr',
                    'amount' => $deliveryCharge,
                    'description' => "Delivery charge reversal for {$orderIdentifier}",
                ];
            }

            // 3. DEBIT: Output GST reversal
            if ($totalTaxAmt > 0) {
                if ($placeOfSupply === 'intra_state') {
                    $halfTax = round($totalTaxAmt / 2, 2);
                    $remainingTax = round($totalTaxAmt - $halfTax, 2);
                    if ($halfTax > 0) {
                        $entries[] = [
                            'account_id' => $cgstOutputId,
                            'type' => 'Dr',
                            'amount' => $halfTax,
                            'description' => "Output CGST Reversal for {$orderIdentifier}",
                        ];
                    }
                    if ($remainingTax > 0) {
                        $entries[] = [
                            'account_id' => $sgstOutputId,
                            'type' => 'Dr',
                            'amount' => $remainingTax,
                            'description' => "Output SGST Reversal for {$orderIdentifier}",
                        ];
                    }
                } else {
                    $entries[] = [
                        'account_id' => $igstOutputId,
                        'type' => 'Dr',
                        'amount' => $totalTaxAmt,
                        'description' => "Output IGST Reversal for {$orderIdentifier}",
                    ];
                }
            }

            // 4. CREDIT: Receivable / Clearing (Reversing balance)
            $entries[] = [
                'account_id' => $creditAccountId,
                'type' => 'Cr',
                'amount' => $totalPayable,
                'description' => "Clearing reversal for {$orderIdentifier}",
            ];

            // 5. Minor paise Round Off adjustment
            $drSum = 0.0; $crSum = 0.0;
            foreach ($entries as $ent) {
                if ($ent['type'] === 'Dr') $drSum += $ent['amount']; else $crSum += $ent['amount'];
            }
            $roundOff = round($drSum - $crSum, 2);
            if (abs($roundOff) >= 0.01) {
                $entries[] = [
                    'account_id' => $roundAccountId,
                    'type' => $roundOff > 0 ? 'Cr' : 'Dr',
                    'amount' => abs($roundOff),
                    'description' => 'Round off adjustment',
                ];
            }

            return $this->voucherService->create([
                'voucher_type' => 'Credit Note',
                'date' => $date,
                'narration' => "Cancellation Credit Note for {$orderIdentifier}",
                'shop_id' => $order->shop_id,
                'financial_year_id' => $fyId,
                'seq_prefix' => 'CN-' . $order->shop_id . '-',
                'entries' => $entries,
            ]);
        });
    }

    /**
     * Resolve ledger accounts by code, in one query, failing loudly.
     *
     * These lookups previously ended in a literal id - `?? 1`, `?? 21`, `?? 32`.
     * If a code were ever missing the posting would silently land in whatever
     * account happened to hold that id. A missing chart entry is a setup fault
     * and should stop the posting, not quietly misfile it.
     *
     * @param  array<string, string[]>  $spec  label => codes to try, in order
     * @return array<string, int>
     */
    protected function resolveAccountIds(array $spec): array
    {
        $codes = [];
        foreach ($spec as $chain) {
            foreach ((array)$chain as $code) {
                $codes[] = $code;
            }
        }

        $found = Account::whereIn('code', array_values(array_unique($codes)))
            ->pluck('id', 'code')
            ->toArray();

        $resolved = [];
        $missing = [];

        foreach ($spec as $label => $chain) {
            foreach ((array)$chain as $code) {
                if (!empty($found[$code])) {
                    $resolved[$label] = (int)$found[$code];
                    break;
                }
            }
            if (!isset($resolved[$label])) {
                $missing[] = $label . ' (tried ' . implode(', ', (array)$chain) . ')';
            }
        }

        if (!empty($missing)) {
            throw new Exception(
                'Chart of accounts is missing: ' . implode('; ', $missing)
                . '. Seed these ledgers before posting.'
            );
        }

        return $resolved;
    }
}
