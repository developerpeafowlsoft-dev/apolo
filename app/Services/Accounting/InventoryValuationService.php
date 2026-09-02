<?php

namespace App\Services\Accounting;

use App\Models\Product;
use App\Models\InwardProduct;
use App\Models\OrderProduct;
use Illuminate\Support\Facades\DB;

class InventoryValuationService
{
    /**
     * Calculate Weighted Average Cost Rate for a product based on InwardProduct purchase entries.
     * Formula: Weighted Average Cost = Sum(Inward Quantity * Purchase Rate) / Sum(Inward Quantity)
     *
     * @param int $productId
     * @return float Weighted Average Cost Rate per unit
     */
    public function getWeightedAverageCost(int $productId): float
    {
        $inwards = InwardProduct::where('product_id', $productId)->get();

        if ($inwards->isEmpty()) {
            $product = Product::find($productId);
            return (float)($product->unit_price ?? $product->price ?? 0.00);
        }

        $totalCost = 0.00;
        $totalQty = 0;

        foreach ($inwards as $inward) {
            $qty = (int)($inward->quantity ?? $inward->stock ?? 1);
            $rate = (float)($inward->buy_price ?? $inward->purchase_rate ?? $inward->price ?? 0.00);
            if ($rate <= 0 && (float)$inward->net_purc_rate > 0 && $qty > 0) {
                $rate = round((float)$inward->net_purc_rate / $qty, 2);
            }
            $totalCost += ($qty * $rate);
            $totalQty += $qty;
        }

        return $totalQty > 0 ? round($totalCost / $totalQty, 2) : 0.00;
    }

    /**
     * Calculate total inventory asset valuation across all physical SKUs for a shop.
     * Aggregates by canonical physical SKU, eliminating duplicate catalog product counting.
     *
     * @param int $shopId
     * @return array Summary of total stock quantity, total weighted asset value, and product breakdown
     */
    public function calculateShopInventoryValuation(int $shopId): array
    {
        $canonicalService = app(CanonicalInventoryService::class);
        $summary = $canonicalService->getShopPhysicalSkuSummary($shopId);

        $breakdown = [];
        foreach ($summary['physical_skus'] as $sku) {
            $breakdown[] = [
                'product_id' => $sku['canonical_product_id'],
                'name' => $sku['name'],
                'alias_product_ids' => $sku['alias_product_ids'],
                'purchased_quantity' => $sku['purchased_qty'],
                'sold_quantity' => $sku['recognized_sold_qty'],
                'reserved_quantity' => $sku['reserved_qty'],
                'stock_quantity' => $sku['available_qty'],
                'accounting_owned_quantity' => $sku['accounting_owned_qty'],
                'weighted_avg_cost' => $sku['unit_cost'],
                'asset_value' => $sku['accounting_owned_value'],
                'available_asset_value' => $sku['available_value'],
                'reserved_asset_value' => $sku['reserved_value'],
            ];
        }

        return [
            'total_stock_quantity' => $summary['totals']['total_available_qty'],
            'total_accounting_owned_quantity' => $summary['totals']['total_accounting_owned_qty'],
            'total_reserved_quantity' => $summary['totals']['total_reserved_qty'],
            'total_inventory_asset_value' => $summary['totals']['total_accounting_owned_value'],
            'total_accounting_owned_value' => $summary['totals']['total_accounting_owned_value'],
            'total_available_value' => $summary['totals']['total_available_value'],
            'total_reserved_value' => $summary['totals']['total_reserved_value'],
            'valuation_method' => 'Canonical Physical SKU Weighted Average Costing (Inwards & GL Purchases)',
            'products' => $breakdown,
        ];
    }
}
