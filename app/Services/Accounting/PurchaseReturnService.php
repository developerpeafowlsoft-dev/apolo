<?php

namespace App\Services\Accounting;

use App\Models\Account;
use App\Models\FinancialYear;
use App\Models\InwardInvoice;
use App\Models\InwardProduct;
use App\Models\Product;
use App\Models\ProductBarcode;
use App\Models\ProductPurchase;
use App\Models\Shop;
use App\Models\Voucher;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PurchaseReturnService
{
    protected VoucherService $voucherService;

    public function __construct(VoucherService $voucherService)
    {
        $this->voucherService = $voucherService;
    }

    /**
     * Get list of finalized purchases eligible for return.
     */
    public function getEligiblePurchaseInvoices(int $shopId, ?int $supplierId = null)
    {
        return ProductPurchase::whereHas('inwardInvoice', function ($q) use ($shopId, $supplierId) {
            $q->where('shop_id', $shopId);
            if ($supplierId) {
                $q->where('inward_party_code', $supplierId);
            }
        })
        ->with(['inwardInvoice.inwardProduct.products', 'inwardInvoice.partyCode'])
        ->orderByDesc('id')
        ->get();
    }

    /**
     * Get line items and returnable quantities for a specific inward/purchase invoice.
     */
    public function getInvoiceReturnableItems(int $inwardInvoiceId): array
    {
        $inwardInvoice = InwardInvoice::with(['inwardProduct.products', 'inwardProduct.vatTax', 'partyCode'])->findOrFail($inwardInvoiceId);

        $items = [];
        foreach ($inwardInvoice->inwardProduct as $ip) {
            $product = $ip->products;
            $purchasedQty = (int) ($ip->quantity ?? 0);
            $availableStock = $product ? (int) $product->available_stock : 0;
            $unitRate = (float) ($ip->buy_price ?? $ip->price ?? 0);
            if ($unitRate <= 0 && (float) $ip->net_purc_rate > 0 && $purchasedQty > 0) {
                $unitRate = round((float) $ip->net_purc_rate / $purchasedQty, 2);
            }

            $items[] = [
                'inward_product_id' => $ip->id,
                'product_id' => $ip->product_id,
                'product_name' => $product?->name ?? 'Unknown Product',
                'purchased_quantity' => $purchasedQty,
                'available_stock' => $availableStock,
                'max_returnable_qty' => min($purchasedQty, $availableStock),
                'unit_rate' => $unitRate,
                'vat_tax_percentage' => (float) ($ip->vattax?->percentage ?? 0),
            ];
        }

        return [
            'inward_invoice_id' => $inwardInvoice->id,
            'voucher_no' => $inwardInvoice->inward_voucher_no,
            'supplier_name' => $inwardInvoice->partyCode?->accountName ?? 'Supplier',
            'supplier_id' => $inwardInvoice->inward_party_code,
            'items' => $items,
        ];
    }

    /**
     * Process a Purchase Return, reduce physical inventory, and post a double-entry Debit Note voucher.
     * Idempotent & atomic.
     */
    public function processPurchaseReturn(array $data, Shop $shop, FinancialYear $financialYear): array
    {
        return DB::transaction(function () use ($data, $shop, $financialYear) {
            $inwardInvoiceId = (int) $data['inward_invoice_id'];
            $inwardInvoice = InwardInvoice::with(['partyCode', 'inwardProduct.vatTax'])->findOrFail($inwardInvoiceId);
            $productPurchase = ProductPurchase::where('inward_invoice_id', $inwardInvoiceId)->first();

            $supplier = $inwardInvoice->partyCode;
            $supplierAccountId = $supplier?->account_id ?? Account::where('code', 'SUPP_TRADE')->value('id') ?? 24;

            // Accounts resolution from Chart of Accounts
            $purchaseReturnAccountId = Account::where('code', 'PUR_RET')->value('id') ?? 15;
            $cgstInputId             = Account::where('code', 'CGST_IN')->value('id') ?? 4;
            $sgstInputId             = Account::where('code', 'SGST_IN')->value('id') ?? 5;
            $igstInputId             = Account::where('code', 'IGST_IN')->value('id') ?? 6;
            $roundAccountId          = Account::where('code', 'EXP_ROF')->value('id') ?? 31;

            $totalTaxableReturn = 0.00;
            $totalCgstReturn    = 0.00;
            $totalSgstReturn    = 0.00;
            $totalIgstReturn    = 0.00;
            $processedItems     = [];

            $isInterState = ($productPurchase && (float) $productPurchase->total_igst > 0);

            foreach ($data['items'] as $item) {
                $inwardProductId = (int) $item['inward_product_id'];
                $returnQty = (int) $item['qty'];

                if ($returnQty <= 0) {
                    continue;
                }

                $inwardProduct = InwardProduct::where('id', $inwardProductId)
                    ->where('inward_invoice_id', $inwardInvoiceId)
                    ->lockForUpdate()
                    ->firstOrFail();

                $product = Product::where('id', $inwardProduct->product_id)->lockForUpdate()->firstOrFail();

                // Validation: Available stock check
                if ($returnQty > $product->available_stock) {
                    throw new Exception("Return quantity ({$returnQty}) exceeds available physical stock ({$product->available_stock}) for {$product->name}.");
                }

                // Validation: Purchased quantity check
                if ($returnQty > (int) $inwardProduct->quantity) {
                    throw new Exception("Return quantity ({$returnQty}) exceeds purchased quantity ({$inwardProduct->quantity}) for {$product->name}.");
                }

                // 1. Physical Stock Reduction
                $product->decrement('quantity', $returnQty);
                $inwardProduct->decrement('quantity', $returnQty);

                // If barcodes exist, mark the returned barcodes
                if (!empty($item['barcode_number'])) {
                    ProductBarcode::where('barcode_number', $item['barcode_number'])
                        ->where('product_id', $product->id)
                        ->update(['is_sold' => 2]); // 2 = Returned to Supplier / Inactive
                } else {
                    ProductBarcode::where('product_id', $product->id)
                        ->where('inward_invoice_id', $inwardInvoiceId)
                        ->where('is_sold', 0)
                        ->limit($returnQty)
                        ->update(['is_sold' => 2]);
                }

                // Rate and Tax calculations
                $unitRate = (float) ($inwardProduct->buy_price ?? $inwardProduct->price ?? 0);
                if ($unitRate <= 0 && (float) $inwardProduct->net_purc_rate > 0 && (int) $inwardProduct->quantity > 0) {
                    $unitRate = round((float) $inwardProduct->net_purc_rate / (int) $inwardProduct->quantity, 2);
                }

                $lineTaxable = round($returnQty * $unitRate, 2);
                $taxPercent = (float) ($inwardProduct->vatTax?->percentage ?? 0);
                $lineTax = round($lineTaxable * ($taxPercent / 100), 2);

                $totalTaxableReturn += $lineTaxable;

                if ($isInterState) {
                    $totalIgstReturn += $lineTax;
                } else {
                    $halfTax = round($lineTax / 2, 2);
                    $totalCgstReturn += $halfTax;
                    $totalSgstReturn += ($lineTax - $halfTax);
                }

                $processedItems[] = [
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'qty' => $returnQty,
                    'unit_rate' => $unitRate,
                    'taxable_amount' => $lineTaxable,
                    'tax_amount' => $lineTax,
                ];
            }

            if (empty($processedItems)) {
                throw new Exception("No valid items selected for purchase return.");
            }

            $totalTaxReturn = $isInterState ? $totalIgstReturn : ($totalCgstReturn + $totalSgstReturn);
            $grandReturnTotal = round($totalTaxableReturn + $totalTaxReturn, 2);

            // Double-Entry Accounting Entries
            $entries = [];

            // 1. DEBIT: Supplier Payable A/c (Reducing liability to supplier)
            $entries[] = [
                'account_id' => $supplierAccountId,
                'type' => 'Dr',
                'amount' => $grandReturnTotal,
                'description' => "Debit Note against Purchase Inv #{$inwardInvoice->inward_voucher_no}",
            ];

            // 2. CREDIT: Purchase Return A/c (Reversing purchase expense)
            if ($totalTaxableReturn > 0) {
                $entries[] = [
                    'account_id' => $purchaseReturnAccountId,
                    'type' => 'Cr',
                    'amount' => $totalTaxableReturn,
                    'description' => "Purchase Return Taxable Reversal for Inv #{$inwardInvoice->inward_voucher_no}",
                ];
            }

            // 3. CREDIT: Input GST Reversal (Reversing Input Tax Credit)
            if ($isInterState && $totalIgstReturn > 0) {
                $entries[] = [
                    'account_id' => $igstInputId,
                    'type' => 'Cr',
                    'amount' => $totalIgstReturn,
                    'description' => "Input IGST (ITC) Reversal for Inv #{$inwardInvoice->inward_voucher_no}",
                ];
            } else {
                if ($totalCgstReturn > 0) {
                    $entries[] = [
                        'account_id' => $cgstInputId,
                        'type' => 'Cr',
                        'amount' => $totalCgstReturn,
                        'description' => "Input CGST (ITC) Reversal for Inv #{$inwardInvoice->inward_voucher_no}",
                    ];
                }
                if ($totalSgstReturn > 0) {
                    $entries[] = [
                        'account_id' => $sgstInputId,
                        'type' => 'Cr',
                        'amount' => $totalSgstReturn,
                        'description' => "Input SGST (ITC) Reversal for Inv #{$inwardInvoice->inward_voucher_no}",
                    ];
                }
            }

            // 4. Minor paise Round Off adjustment
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
                    'description' => 'Round off adjustment on Debit Note',
                ];
            }

            // Register Debit Note Voucher
            $voucher = $this->voucherService->create([
                'voucher_type' => 'Debit Note',
                'date' => $data['return_date'] ?? now()->toDateString(),
                'narration' => $data['narration'] ?? "Purchase Return Debit Note against Inv #{$inwardInvoice->inward_voucher_no} ({$supplier?->accountName})",
                'shop_id' => $shop->id,
                'financial_year_id' => $financialYear->id,
                'seq_prefix' => 'DN-' . $shop->id . '-',
                'entries' => $entries,
            ]);

            // Mark return flag on purchase records
            $inwardInvoice->update(['is_return' => 1]);
            if ($productPurchase) {
                $productPurchase->update(['is_return' => 1]);
            }

            return [
                'success' => true,
                'voucher_id' => $voucher->id,
                'voucher_no' => $voucher->voucher_no,
                'grand_total_reversed' => $grandReturnTotal,
                'taxable_reversed' => $totalTaxableReturn,
                'tax_reversed' => $totalTaxReturn,
                'items' => $processedItems,
            ];
        });
    }
}
