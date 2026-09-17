<?php

namespace App\Services\Accounting;

use App\Models\Product;
use App\Models\InwardProduct;
use App\Models\OrderProduct;
use App\Models\ProductPurchase;
use App\Models\POSReturnProduct;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CanonicalInventoryService
{
    /**
     * Central mapping of alias product IDs to canonical physical product IDs.
     * Preserves original Product IDs in historical records while consolidating inventory pools.
     *
     * @var array<int, int>
     */
    protected static array $staticCanonicalMap = [
        4 => 1, // Product #4 (GIRLS FANCY WEAR Web) -> Canonical Product #1
        3 => 2, // Product #3 (BABY WALKER Web) -> Canonical Product #2
    ];

    /**
     * Resolve the Canonical Physical Product ID for a given Product ID.
     *
     * @param int $productId
     * @return int
     */
    public function resolveCanonicalProductId(int $productId): int
    {
        // 1. Check if database column exists (for future schema expansion).
        //    Cached: this is a schema-introspection round trip, and it used to run
        //    once per product - thousands of them on a single report load.
        if (self::hasCanonicalColumn()) {
            $canonicalId = Product::where('id', $productId)->value('canonical_product_id');
            if ($canonicalId) {
                return (int)$canonicalId;
            }
        }

        // 2. Fallback to centralized static mapping
        if (isset(self::$staticCanonicalMap[$productId])) {
            return self::$staticCanonicalMap[$productId];
        }

        return $productId;
    }

    /**
     * Get all Product IDs that belong to the same physical SKU as the given Product ID.
     *
     * @param int $productId
     * @return array<int>
     */
    public function getAliasProductIds(int $productId): array
    {
        $canonicalId = $this->resolveCanonicalProductId($productId);
        $aliases = [$canonicalId];

        foreach (self::$staticCanonicalMap as $aliasId => $targetId) {
            if ($targetId === $canonicalId) {
                $aliases[] = $aliasId;
            }
        }

        return array_unique($aliases);
    }

    protected static ?bool $hasCanonicalColumn = null;

    protected static function hasCanonicalColumn(): bool
    {
        return self::$hasCanonicalColumn ??= Schema::hasColumn('products', 'canonical_product_id');
    }

    /**
     * Physical-SKU stock and valuation for a shop.
     *
     * Every figure is pre-aggregated in a handful of grouped queries. The previous
     * version issued roughly a dozen queries per product inside the loop, which on
     * this shop meant ~64,700 queries and 87 seconds for one page load.
     */
    public function getShopPhysicalSkuSummary(int $shopId): array
    {
        $valuationService = app(InventoryValuationService::class);

        $products = Product::where('shop_id', $shopId)
            ->select('id', 'name', 'hsn_master_id', 'vat_tax_id')
            ->get();

        // Group product records by canonical physical SKU
        $skuGroups = [];
        foreach ($products as $p) {
            $canonicalId = $this->resolveCanonicalProductId($p->id);
            if (!isset($skuGroups[$canonicalId])) {
                $skuGroups[$canonicalId] = [
                    'canonical_id' => $canonicalId,
                    'name' => null,
                    'hsn_master_id' => null,
                    'vat_tax_id' => null,
                    'aliases' => [],
                ];
            }
            $skuGroups[$canonicalId]['aliases'][] = $p->id;
        }

        // Canonical product details, resolved in one pass rather than per group
        $byId = $products->keyBy('id');
        $missingCanonical = array_diff(array_keys($skuGroups), $byId->keys()->all());
        if (!empty($missingCanonical)) {
            foreach (Product::whereIn('id', $missingCanonical)->select('id', 'name', 'hsn_master_id', 'vat_tax_id')->get() as $extra) {
                $byId[$extra->id] = $extra;
            }
        }
        foreach ($skuGroups as $cid => $g) {
            $canonical = $byId[$cid] ?? $byId[$g['aliases'][0]] ?? null;
            $skuGroups[$cid]['name'] = $canonical?->name;
            $skuGroups[$cid]['hsn_master_id'] = $canonical?->hsn_master_id;
            $skuGroups[$cid]['vat_tax_id'] = $canonical?->vat_tax_id;
        }

        $allAliasIds = [];
        foreach ($skuGroups as $g) {
            foreach ($g['aliases'] as $aid) {
                $allAliasIds[] = $aid;
            }
        }
        $allAliasIds = array_values(array_unique($allAliasIds));

        // --- pre-aggregated lookups -------------------------------------------
        $costMap = $valuationService->getWeightedAverageCostMap(array_keys($skuGroups));

        $purchasedMap = DB::table('inward_products')
            ->whereIn('product_id', $allAliasIds)
            ->groupBy('product_id')
            ->selectRaw('product_id, SUM(quantity) as qty')
            ->pluck('qty', 'product_id')
            ->toArray();

        // Raw joins: the Order model carries a global scope that whereHas() had to
        // strip anyway, and this keeps both figures to one query each.
        $soldMap = DB::table('order_products')
            ->join('orders', 'order_products.order_id', '=', 'orders.id')
            ->where('orders.shop_id', $shopId)
            ->whereNotNull('orders.voucher_id')
            ->whereIn('order_products.product_id', $allAliasIds)
            ->groupBy('order_products.product_id')
            ->selectRaw('order_products.product_id as pid, SUM(order_products.quantity) as qty')
            ->pluck('qty', 'pid')
            ->toArray();

        $reservedMap = DB::table('order_products')
            ->join('orders', 'order_products.order_id', '=', 'orders.id')
            ->where('orders.shop_id', $shopId)
            ->whereNull('orders.voucher_id')
            ->where('orders.order_status', '!=', 'Cancelled')
            ->where('orders.order_status', '!=', \App\Enums\OrderStatus::CANCELLED->value)
            ->whereIn('order_products.product_id', $allAliasIds)
            ->groupBy('order_products.product_id')
            ->selectRaw('order_products.product_id as pid, SUM(order_products.quantity) as qty')
            ->pluck('qty', 'pid')
            ->toArray();

        $sumFor = function (array $ids, array $map): int {
            $total = 0;
            foreach ($ids as $id) {
                $total += (int)($map[$id] ?? 0);
            }
            return $total;
        };

        $summary = [];
        $totalPurchasedQty = 0;
        $totalRecognizedSoldQty = 0;
        $totalReservedQty = 0;
        $totalAccountingOwnedQty = 0;
        $totalAvailableQty = 0;
        $totalAccountingOwnedValue = 0.00;
        $totalAvailableValue = 0.00;
        $totalReservedValue = 0.00;

        foreach ($skuGroups as $canonicalId => $group) {
            $aliasIds = array_values(array_unique($group['aliases']));
            $unitCost = (float)($costMap[$canonicalId] ?? 0.00);

            $purchasedQty = $sumFor($aliasIds, $purchasedMap);
            $recognizedSoldQty = $sumFor($aliasIds, $soldMap);
            $reservedQty = $sumFor($aliasIds, $reservedMap);
            $purchaseReturnQty = 0;

            $accountingOwnedQty = max(0, $purchasedQty - $recognizedSoldQty - $purchaseReturnQty);
            $availableQty = max(0, $accountingOwnedQty - $reservedQty);

            $accountingOwnedVal = round($accountingOwnedQty * $unitCost, 2);
            $availableVal = round($availableQty * $unitCost, 2);
            $reservedVal = round($reservedQty * $unitCost, 2);

            $totalPurchasedQty += $purchasedQty;
            $totalRecognizedSoldQty += $recognizedSoldQty;
            $totalReservedQty += $reservedQty;
            $totalAccountingOwnedQty += $accountingOwnedQty;
            $totalAvailableQty += $availableQty;
            $totalAccountingOwnedValue += $accountingOwnedVal;
            $totalAvailableValue += $availableVal;
            $totalReservedValue += $reservedVal;

            $summary[] = [
                'canonical_product_id' => $canonicalId,
                'name' => $group['name'],
                'alias_product_ids' => $aliasIds,
                'unit_cost' => $unitCost,
                'purchased_qty' => $purchasedQty,
                'recognized_sold_qty' => $recognizedSoldQty,
                'reserved_qty' => $reservedQty,
                'purchase_return_qty' => $purchaseReturnQty,
                'accounting_owned_qty' => $accountingOwnedQty,
                'available_qty' => $availableQty,
                'accounting_owned_value' => $accountingOwnedVal,
                'available_value' => $availableVal,
                'reserved_value' => $reservedVal,
            ];
        }

        return [
            'shop_id' => $shopId,
            'physical_skus' => $summary,
            'totals' => [
                'total_purchased_qty' => $totalPurchasedQty,
                'total_recognized_sold_qty' => $totalRecognizedSoldQty,
                'total_reserved_qty' => $totalReservedQty,
                'total_accounting_owned_qty' => $totalAccountingOwnedQty,
                'total_available_qty' => $totalAvailableQty,
                'total_accounting_owned_value' => round($totalAccountingOwnedValue, 2),
                'total_available_value' => round($totalAvailableValue, 2),
                'total_reserved_value' => round($totalReservedValue, 2),
            ],
        ];
    }

}
