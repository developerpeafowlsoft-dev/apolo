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

            $taxAmt = (float)($order->total_tax_amount ?? $order->tax_amount ?? 0);
            $taxableAmt = (float)($order->total_taxable_amount ?? ($order->payable_amount - $taxAmt));
            $totalPayable = (float)$order->payable_amount;
            $discountAmt = (float)($order->coupon_discount_amount ?? 0);

            $salesAccount = $this->getOrCreateSystemAccount('POS Showroom Sales A/c', 'SALES_POS', 'Sales Accounts', 'Revenue');
            $cgstOutput = $this->getOrCreateSystemAccount('CGST Output A/c', 'CGST_OUT', 'Duties & Taxes', 'Liability');
            $sgstOutput = $this->getOrCreateSystemAccount('SGST Output A/c', 'SGST_OUT', 'Duties & Taxes', 'Liability');
            $igstOutput = $this->getOrCreateSystemAccount('IGST Output A/c', 'IGST_OUT', 'Duties & Taxes', 'Liability');

            // Payment Tender Account
            $paymentMethod = strtolower($order->payment_method->value ?? $order->payment_method ?? 'cash');
            if ($paymentMethod === 'card' || str_contains($paymentMethod, 'card')) {
                $tenderAccount = $this->getOrCreateSystemAccount('Paytm EDC Card Clearing A/c', 'CARD_PAYTM_CLEARING', 'Bank Accounts', 'Asset');
            } elseif ($paymentMethod === 'upi' || str_contains($paymentMethod, 'upi')) {
                $tenderAccount = $this->getOrCreateSystemAccount('PhonePe UPI Clearing A/c', 'UPI_PPE_CLEARING', 'Bank Accounts', 'Asset');
            } else {
                $tenderAccount = $this->getOrCreateSystemAccount('Counter Cash Drawer A/c', 'CASH_COUNTER_1', 'Cash-in-Hand', 'Asset');
            }

            $entries = [];

            // DEBIT: Payment Tender / Cash Drawer
            $entries[] = [
                'account_id' => $tenderAccount->id,
                'type' => 'Dr',
                'amount' => $totalPayable,
                'description' => "Sales Collection for Order #{$order->order_code}",
            ];

            // CREDIT: Sales Account
            $entries[] = [
                'account_id' => $salesAccount->id,
                'type' => 'Cr',
                'amount' => $taxableAmt,
                'description' => "Gross Sales Revenue for Order #{$order->order_code}",
            ];

            // CREDIT: Tax Ledgers
            if ($taxAmt > 0) {
                if ($placeOfSupply === 'intra_state') {
                    $halfTax = round($taxAmt / 2, 2);
                    $entries[] = [
                        'account_id' => $cgstOutput->id,
                        'type' => 'Cr',
                        'amount' => $halfTax,
                        'description' => "CGST 9% Output for Order #{$order->order_code}",
                    ];
                    $entries[] = [
                        'account_id' => $sgstOutput->id,
                        'type' => 'Cr',
                        'amount' => $taxAmt - $halfTax,
                        'description' => "SGST 9% Output for Order #{$order->order_code}",
                    ];
                } else {
                    $entries[] = [
                        'account_id' => $igstOutput->id,
                        'type' => 'Cr',
                        'amount' => $taxAmt,
                        'description' => "IGST 18% Output for Order #{$order->order_code}",
                    ];
                }
            }

            $voucherData = [
                'voucher_type' => 'Sales',
                'seq_prefix' => 'SAL-',
                'date' => now()->toDateString(),
                'narration' => "Auto GST Sales Voucher for Invoice #{$order->order_code}",
                'shop_id' => $order->shop_id,
                'financial_year_id' => $order->financial_year_id ?? 1,
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
            $taxAmt = round($returnTotal * 0.18 / 1.18, 2);
            $taxableAmt = $returnTotal - $taxAmt;

            $salesReturnAccount = $this->getOrCreateSystemAccount('Sales Return A/c', 'SALES_RETURN', 'Sales Accounts', 'Revenue');
            $cgstOutput = $this->getOrCreateSystemAccount('CGST Output A/c', 'CGST_OUT', 'Duties & Taxes', 'Liability');
            $sgstOutput = $this->getOrCreateSystemAccount('SGST Output A/c', 'SGST_OUT', 'Duties & Taxes', 'Liability');
            $cashAccount = $this->getOrCreateSystemAccount('Counter Cash Drawer A/c', 'CASH_COUNTER_1', 'Cash-in-Hand', 'Asset');

            $entries = [];
            $entries[] = [
                'account_id' => $salesReturnAccount->id,
                'type' => 'Dr',
                'amount' => $taxableAmt,
                'description' => "Sales Return for Return Note #{$posReturn->return_code}",
            ];

            if ($taxAmt > 0) {
                $halfTax = round($taxAmt / 2, 2);
                $entries[] = [
                    'account_id' => $cgstOutput->id,
                    'type' => 'Dr',
                    'amount' => $halfTax,
                    'description' => "CGST Reversal for Return Note #{$posReturn->return_code}",
                ];
                $entries[] = [
                    'account_id' => $sgstOutput->id,
                    'type' => 'Dr',
                    'amount' => $taxAmt - $halfTax,
                    'description' => "SGST Reversal for Return Note #{$posReturn->return_code}",
                ];
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
                'financial_year_id' => 1,
                'entries' => $entries,
            ];

            return $this->voucherService->create($voucherData);
        });
    }
}
