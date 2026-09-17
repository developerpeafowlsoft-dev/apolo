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
        return $this->getWeightedAverageCostMap([$productId])[$productId] ?? 0.00;
    }

    /**
     * Weighted average cost for many products in two queries.
     *
     * Calling getWeightedAverageCost() inside a loop issued one query per product
     * (two when the product had no inwards). On a 4,000-SKU shop that was tens of
     * thousands of round trips; this collapses it to a pair.
     *
     * @param  int[]  $productIds
     * @return array<int,float>  product id => weighted average unit cost
     */
    public function getWeightedAverageCostMap(array $productIds): array
    {
        $productIds = array_values(array_unique(array_filter(array_map('intval', $productIds))));
        if (empty($productIds)) {
            return [];
        }

        $totals = [];   // product_id => ['cost' => float, 'qty' => int]

        InwardProduct::whereIn('product_id', $productIds)
            ->select('product_id', 'quantity', 'buy_price', 'price', 'net_purc_rate')
            ->chunk(5000, function ($chunk) use (&$totals) {
                foreach ($chunk as $inward) {
                    $pid = (int)$inward->product_id;
                    $qty = (int)($inward->quantity ?? 1);
                    $rate = (float)($inward->buy_price ?? $inward->price ?? 0.00);

                    if ($rate <= 0 && (float)$inward->net_purc_rate > 0 && $qty > 0) {
                        $rate = round((float)$inward->net_purc_rate / $qty, 2);
                    }

                    $totals[$pid]['cost'] = ($totals[$pid]['cost'] ?? 0.00) + ($qty * $rate);
                    $totals[$pid]['qty'] = ($totals[$pid]['qty'] ?? 0) + $qty;
                }
            });

        $map = [];
        foreach ($totals as $pid => $t) {
            $map[$pid] = $t['qty'] > 0 ? round($t['cost'] / $t['qty'], 2) : 0.00;
        }

        // Products with no inward history fall back to their catalogue price.
        $missing = array_diff($productIds, array_keys($totals));
        if (!empty($missing)) {
            foreach (Product::whereIn('id', $missing)->select('id', 'price')->get() as $prod) {
                $map[(int)$prod->id] = (float)($prod->price ?? 0.00);
            }
            foreach ($missing as $pid) {
                $map[$pid] = $map[$pid] ?? 0.00;
            }
        }

        return $map;
    }

    /**
     * Calculate total inventory asset valuation across all physical SKUs for a shop.
     * Aggregates by canonical physical SKU, eliminating duplicate catalog product counting.
     *
     * @param int $shopId
     * @return array Summary of total stock quantity, total weighted asset value, and product breakdown
     */
    /** Per-request memo: the dashboard and each statement ask for this more than once. */
    protected static array $valuationMemo = [];

    public function calculateShopInventoryValuation(int $shopId): array
    {
        if (isset(self::$valuationMemo[$shopId])) {
            return self::$valuationMemo[$shopId];
        }

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

        return self::$valuationMemo[$shopId] = [
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
