<?php

namespace App\Services\Accounting;

use App\Models\Order;
use App\Models\POSReturn;
use App\Models\InwardInvoice;
use App\Models\Account;
use App\Models\AccountGroup;
use App\Models\AccountType;
use App\Models\Voucher;
use App\Models\Shop;
use App\Models\State;
use Exception;
use Illuminate\Support\Facades\DB;

class GSTPostingService
{
    protected $voucherService;

    public function __construct(VoucherService $voucherService)
    {
        $this->voucherService = $voucherService;
    }

    /**
     * Extract statutory tax-inclusive GST and taxable base using Rule 35 formula.
     * Taxable = round((Gross * 100) / (100 + Rate), 2)
     * Total GST = round(Gross - Taxable, 2)
     *
     * @param float $grossPrice Total price paid by customer (tax-inclusive)
     * @param float $taxPercent Configured tax percentage (e.g. 5, 12, 18, 28)
     * @param string $placeOfSupply 'intra_state' or 'inter_state'
     * @return array
     */
    public static function extractInclusiveTax(float $grossPrice, float $taxPercent, string $placeOfSupply = 'intra_state'): array
    {
        if ($taxPercent <= 0 || $grossPrice <= 0) {
            return [
                'gross_price' => round($grossPrice, 2),
                'taxable_value' => round($grossPrice, 2),
                'tax_amount' => 0.00,
                'cgst' => 0.00,
                'sgst' => 0.00,
                'igst' => 0.00,
                'tax_percentage' => 0.00,
            ];
        }

        $taxable = round(($grossPrice * 100) / (100 + $taxPercent), 2);
        $totalGst = round($grossPrice - $taxable, 2);

        $isInter = ($placeOfSupply === 'inter_state');
        $cgst = 0.00;
        $sgst = 0.00;
        $igst = 0.00;

        if ($isInter) {
            $igst = $totalGst;
        } else {
            $cgst = round($totalGst / 2, 2);
            $sgst = round($totalGst - $cgst, 2);
        }

        return [
            'gross_price' => round($grossPrice, 2),
            'taxable_value' => $taxable,
            'tax_amount' => $totalGst,
            'cgst' => $cgst,
            'sgst' => $sgst,
            'igst' => $igst,
            'tax_percentage' => $taxPercent,
        ];
    }

    /**
     * Resolve Place of Supply: Is the sale Intra-State (CGST + SGST) or Inter-State (IGST)?
     *
     * @param int $shopId
     * @param mixed $customerAddress (Address model, string, or null)
     * @return string 'intra_state' or 'inter_state'
     */
    public function resolvePlaceOfSupply(int $shopId, $customerAddress = null): string
    {
        $onlineAccounting = app(OnlineOrderAccountingService::class);
        $shop = Shop::find($shopId);
        $sellerStateCode = $onlineAccounting->getSellerGstStateCode($shop);

        if (!$customerAddress) {
            return 'intra_state';
        }

        $stateText = null;
        if ($customerAddress instanceof \App\Models\Address) {
            $stateText = $customerAddress->state ?? $customerAddress->area ?? '';
        } elseif (is_string($customerAddress)) {
            $stateText = $customerAddress;
        }

        if (empty($stateText)) {
            return 'intra_state';
        }

        $destStateCode = $sellerStateCode;
        foreach ($onlineAccounting->getStateCodeMap() as $stateName => $code) {
            if (stripos($stateText, $stateName) !== false) {
                $destStateCode = $code;
                break;
            }
        }

        return ($sellerStateCode === $destStateCode) ? 'intra_state' : 'inter_state';
    }

    /**
     * Get or automatically create a default system ledger account under a specific group.
     */
    public function getOrCreateSystemAccount(string $accountName, string $accountCode, string $groupName, string $accountTypeName = 'Asset'): Account
    {
        $account = Account::where('code', $code = strtoupper($accountCode))->first();
        if ($account) {
            return $account;
        }

        $type = AccountType::firstOrCreate(['name' => $accountTypeName]);
        $group = AccountGroup::firstOrCreate(
            ['code' => strtoupper(str_replace(' ', '_', $groupName))],
            ['name' => $groupName, 'account_type_id' => $type->id, 'is_editable' => 1]
        );

        return Account::create([
            'name' => $accountName,
            'code' => $code,
            'account_group_id' => $group->id,
            'is_active' => 1,
            'is_default' => 1,
        ]);
    }

    /**
     * Automatically post a double-entry Sales Journal Voucher for an order.
     */
    public function postSalesInvoiceVoucher(Order $order): Voucher
    {
        return DB::transaction(function () use ($order) {
            $placeOfSupply = $this->resolvePlaceOfSupply($order->shop_id, $order->address);

            // payable_amount = total_amount + delivery_charge, and total_amount is
            // tax-inclusive goods. Deriving the sales leg as (payable - tax) folded
            // the delivery charge into Sales revenue, overstating turnover and
            // giving the shipping fee the wrong GST treatment.
            $totalPayable   = (float)$order->payable_amount;
            $deliveryCharge = (float)($order->delivery_charge ?? 0);
            $taxAmt         = (float)($order->tax_amount ?? 0);
            $productTotal   = (float)($order->total_amount ?? ($totalPayable - $deliveryCharge));
            $taxableAmt     = max(0, round($productTotal - $taxAmt, 2));

            $salesAccount = $this->getOrCreateSystemAccount('POS Showroom Sales A/c', 'SALES_POS', 'Sales Accounts', 'Revenue');
            $cgstOutput = $this->getOrCreateSystemAccount('CGST Output A/c', 'CGST_OUT', 'Duties & Taxes', 'Liability');
            $sgstOutput = $this->getOrCreateSystemAccount('SGST Output A/c', 'SGST_OUT', 'Duties & Taxes', 'Liability');
            $igstOutput = $this->getOrCreateSystemAccount('IGST Output A/c', 'IGST_OUT', 'Duties & Taxes', 'Liability');
            $deliveryIncome = $this->getOrCreateSystemAccount('Delivery Charges Collected A/c', 'REV_DELIVERY', 'Direct Incomes', 'Revenue');
            $roundOffAccount = $this->getOrCreateSystemAccount('Round-Off (ROF) A/c', 'EXP_ROF', 'Indirect Expenses', 'Expenses');

            // Payment Tender Account
            $paymentMethod = strtolower($order->payment_method->value ?? $order->payment_method ?? 'cash');
            if ($paymentMethod === 'card' || str_contains($paymentMethod, 'card')) {
                $tenderAccount = $this->getOrCreateSystemAccount('Paytm EDC Card Clearing A/c', 'EDC_CLEARING', 'Bank Accounts', 'Asset');
            } elseif ($paymentMethod === 'upi' || str_contains($paymentMethod, 'upi')) {
                $tenderAccount = $this->getOrCreateSystemAccount('PhonePe UPI Clearing A/c', 'UPI_CLEARING', 'Bank Accounts', 'Asset');
            } else {
                $tenderAccount = $this->getOrCreateSystemAccount('Counter Cash Drawer A/c', 'CASH_DRAWER', 'Cash-in-Hand', 'Asset');
            }

            $entries = [];

            // DEBIT: Payment Tender / Cash Drawer
            $entries[] = [
                'account_id' => $tenderAccount->id,
                'type' => 'Dr',
                'amount' => $totalPayable,
                'description' => "Sales Collection for Order #{$order->order_code}",
            ];

            // CREDIT: Sales Account (goods only, net of tax)
            if ($taxableAmt > 0) {
                $entries[] = [
                    'account_id' => $salesAccount->id,
                    'type' => 'Cr',
                    'amount' => $taxableAmt,
                    'description' => "Sales Revenue for Order #{$order->order_code}",
                ];
            }

            // CREDIT: Delivery Charge Income - its own head, not sales revenue
            if ($deliveryCharge > 0) {
                $entries[] = [
                    'account_id' => $deliveryIncome->id,
                    'type' => 'Cr',
                    'amount' => $deliveryCharge,
                    'description' => "Delivery Charge for Order #{$order->order_code}",
                ];
            }

            // CREDIT: Tax Ledgers
            if ($taxAmt > 0) {
                if ($placeOfSupply === 'intra_state') {
                    $halfTax = round($taxAmt / 2, 2);
                    $entries[] = [
                        'account_id' => $cgstOutput->id,
                        'type' => 'Cr',
                        'amount' => $halfTax,
                        'description' => "Output CGST for Order #{$order->order_code}",
                    ];
                    $entries[] = [
                        'account_id' => $sgstOutput->id,
                        'type' => 'Cr',
                        'amount' => $taxAmt - $halfTax,
                        'description' => "Output SGST for Order #{$order->order_code}",
                    ];
                } else {
                    $entries[] = [
                        'account_id' => $igstOutput->id,
                        'type' => 'Cr',
                        'amount' => $taxAmt,
                        'description' => "Output IGST (Inter-State) for Order #{$order->order_code}",
                    ];
                }
            }

            // Absorb sub-rupee rounding. Anything larger means a component of the
            // order (a discount, say) is not modelled here - fail loudly rather
            // than bury it in round-off.
            $drSum = 0.0; $crSum = 0.0;
            foreach ($entries as $ent) {
                if ($ent['type'] === 'Dr') { $drSum += $ent['amount']; } else { $crSum += $ent['amount']; }
            }
            $residual = round($drSum - $crSum, 2);

            if (abs($residual) > 1.00) {
                throw new Exception(sprintf(
                    'Sales voucher for order #%s is out by %.2f. Payable %.2f, goods %.2f, delivery %.2f, tax %.2f, discount %.2f - an unmodelled component.',
                    $order->order_code, $residual, $totalPayable, $taxableAmt, $deliveryCharge, $taxAmt,
                    (float)($order->discount ?? 0) + (float)($order->coupon_discount ?? 0)
                ));
            }

            if (abs($residual) >= 0.01) {
                $entries[] = [
                    'account_id' => $roundOffAccount->id,
                    'type' => $residual > 0 ? 'Cr' : 'Dr',
                    'amount' => abs($residual),
                    'description' => "Round off for Order #{$order->order_code}",
                ];
            }

            $voucherData = [
                'voucher_type' => 'Sales',
                'seq_prefix' => 'SAL-',
                'date' => now()->toDateString(),
                'narration' => "Auto GST Sales Voucher for Invoice #{$order->order_code}",
                'shop_id' => $order->shop_id,
                'financial_year_id' => $order->financial_year_id ?? $this->resolveFinancialYearId($order->created_at ?? now()),
                'entries' => $entries,
            ];

            return $this->voucherService->create($voucherData);
        });
    }

    /**
     * Automatically post a Sales Return Credit Note voucher.
     */
    public function postSalesReturnVoucher(POSReturn $posReturn): Voucher
    {
        return DB::transaction(function () use ($posReturn) {
            $returnTotal = (float)$posReturn->total_amount;

            // The rate must follow the original sale. Assuming 18% reversed the
            // wrong tax on every garment return - apparel is commonly 5% or 12%,
            // and an inter-state sale carries IGST rather than CGST+SGST.
            $rate = $this->resolveReturnTaxRate($posReturn);
            $split = self::extractInclusiveTax($returnTotal, $rate, $this->resolveReturnPlaceOfSupply($posReturn));
            $taxAmt = (float)$split['tax_amount'];
            $taxableAmt = (float)$split['taxable_value'];

            $salesReturnAccount = $this->getOrCreateSystemAccount('Sales Return A/c', 'SALES_RET', 'Sales Accounts', 'Revenue');
            $cgstOutput = $this->getOrCreateSystemAccount('CGST Output A/c', 'CGST_OUT', 'Duties & Taxes', 'Liability');
            $sgstOutput = $this->getOrCreateSystemAccount('SGST Output A/c', 'SGST_OUT', 'Duties & Taxes', 'Liability');
            $cashAccount = $this->getOrCreateSystemAccount('Counter Cash Drawer A/c', 'CASH_DRAWER', 'Cash-in-Hand', 'Asset');

            $entries = [];
            $entries[] = [
                'account_id' => $salesReturnAccount->id,
                'type' => 'Dr',
                'amount' => $taxableAmt,
                'description' => "Sales Return for Return Note #{$posReturn->return_code}",
            ];

            if ($taxAmt > 0) {
                if ((float)$split['igst'] > 0) {
                    $igstOutput = $this->getOrCreateSystemAccount('IGST Output A/c', 'IGST_OUT', 'Duties & Taxes', 'Liability');
                    $entries[] = [
                        'account_id' => $igstOutput->id,
                        'type' => 'Dr',
                        'amount' => (float)$split['igst'],
                        'description' => "IGST Reversal @{$rate}% for Return Note #{$posReturn->return_code}",
                    ];
                } else {
                    $entries[] = [
                        'account_id' => $cgstOutput->id,
                        'type' => 'Dr',
                        'amount' => (float)$split['cgst'],
                        'description' => "CGST Reversal @{$rate}% for Return Note #{$posReturn->return_code}",
                    ];
                    $entries[] = [
                        'account_id' => $sgstOutput->id,
                        'type' => 'Dr',
                        'amount' => (float)$split['sgst'],
                        'description' => "SGST Reversal @{$rate}% for Return Note #{$posReturn->return_code}",
                    ];
                }
            }

            $entries[] = [
                'account_id' => $cashAccount->id,
                'type' => 'Cr',
                'amount' => $returnTotal,
                'description' => "Cash Refund for Return Note #{$posReturn->return_code}",
            ];

            $voucherData = [
                'voucher_type' => 'Credit Note',
                'seq_prefix' => 'CN-',
                'date' => now()->toDateString(),
                'narration' => "Credit Note Tax Reversal for POS Return #{$posReturn->return_code}",
                'shop_id' => $posReturn->shop_id,
                'financial_year_id' => $this->resolveFinancialYearId($posReturn->created_at ?? now()),
                'entries' => $entries,
            ];

            return $this->voucherService->create($voucherData);
        });
    }

    /**
     * Financial year covering a date. Both posting methods previously fell back to
     * financial_year_id = 1, which on this database is 2016-2017.
     */
    protected function resolveFinancialYearId($date): int
    {
        $d = $date instanceof \DateTimeInterface ? $date->format('Y-m-d') : (string)$date;

        $fy = DB::table('financial_years')
            ->whereDate('start_date', '<=', $d)
            ->whereDate('end_date', '>=', $d)
            ->orderByDesc('start_date')
            ->value('id');

        return (int)($fy ?: DB::table('financial_years')->where('is_active', 1)->orderByDesc('start_date')->value('id') ?: 1);
    }

    /**
     * GST rate for a return, taken from the lines of the original sale where they
     * can be read, falling back to the shop's default VAT rate rather than a
     * hardcoded 18%.
     */
    protected function resolveReturnTaxRate(POSReturn $posReturn): float
    {
        $rate = null;

        if (!empty($posReturn->order_id)) {
            $rate = DB::table('order_products')
                ->where('order_id', $posReturn->order_id)
                ->whereNotNull('tax_percentage')
                ->where('tax_percentage', '>', 0)
                ->value('tax_percentage');
        }

        if (!$rate) {
            $rate = DB::table('vat_taxes')->where('is_active', 1)->orderBy('id')->value('percentage');
        }

        return (float)($rate ?: 0);
    }

    protected function resolveReturnPlaceOfSupply(POSReturn $posReturn): string
    {
        $order = !empty($posReturn->order_id) ? Order::withoutGlobalScopes()->find($posReturn->order_id) : null;

        return $this->resolvePlaceOfSupply((int)$posReturn->shop_id, $order?->address);
    }
}
