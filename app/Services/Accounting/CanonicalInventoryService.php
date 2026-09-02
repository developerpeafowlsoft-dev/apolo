<?php

namespace App\Services\Accounting;

use App\Models\Product;
use App\Models\InwardProduct;
use App\Models\OrderProduct;
use App\Models\ProductPurchase;
use App\Models\POSReturnProduct;
use Illuminate\Support\Collection;
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
        // 1. Check if database column exists (for future schema expansion)
        if (Schema::hasColumn('products', 'canonical_product_id')) {
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

    /**
     * Calculate authoritative physical SKU summary for a shop.
     * Eliminates duplicate catalog stock counting.
     *
     * @param int $shopId
     * @return array
     */
    public function getShopPhysicalSkuSummary(int $shopId): array
    {
        $valuationService = app(InventoryValuationService::class);
        $products = Product::where('shop_id', $shopId)->get();

        // Group product records by canonical physical SKU
        $skuGroups = [];
        foreach ($products as $p) {
            $canonicalId = $this->resolveCanonicalProductId($p->id);
            if (!isset($skuGroups[$canonicalId])) {
                $canonicalProduct = Product::find($canonicalId) ?? $p;
                $skuGroups[$canonicalId] = [
                    'canonical_id' => $canonicalId,
                    'name' => $canonicalProduct->name,
                    'hsn_master_id' => $canonicalProduct->hsn_master_id,
                    'vat_tax_id' => $canonicalProduct->vat_tax_id,
                    'aliases' => [],
                ];
            }
            $skuGroups[$canonicalId]['aliases'][] = $p->id;
        }

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
            $aliasIds = array_unique($group['aliases']);
            $unitCost = $valuationService->getWeightedAverageCost($canonicalId);

            // 1. Purchased / Received Quantity from Inwards
            $purchasedQty = (int)InwardProduct::whereIn('product_id', $aliasIds)->sum('quantity');
            if ($purchasedQty <= 0) {
                // If not purchased via inward, take the base stock of the canonical product
                $canonicalProd = Product::find($canonicalId);
                $purchasedQty = (int)($canonicalProd?->available_stock ?? 0);
            }

            // 2. Recognized Sold Quantity (Delivered / Vouchered Orders)
            $recognizedSoldQty = (int)OrderProduct::whereHas('order', function ($q) use ($shopId) {
                $q->withoutGlobalScopes()
                  ->where('shop_id', $shopId)
                  ->whereNotNull('voucher_id');
            })->whereIn('product_id', $aliasIds)->sum('quantity');

            // 3. Purchase Returns
            $purchaseReturnQty = 0;

            // 4. Active Reserved Quantity (Pending / Unfulfilled Web Orders)
            $reservedQty = (int)OrderProduct::whereHas('order', function ($q) use ($shopId) {
                $q->withoutGlobalScopes()
                  ->where('shop_id', $shopId)
                  ->whereNull('voucher_id')
                  ->where('order_status', '!=', 'Cancelled')
                  ->where('order_status', '!=', \App\Enums\OrderStatus::CANCELLED->value);
            })->whereIn('product_id', $aliasIds)->sum('quantity');

            // 5. Accounting-Owned Quantity = Purchased - Sold - Purchase Returns
            $accountingOwnedQty = max(0, $purchasedQty - $recognizedSoldQty - $purchaseReturnQty);

            // 6. Available Quantity = Accounting-Owned - Active Reserved
            $availableQty = max(0, $accountingOwnedQty - $reservedQty);

            // Valuations
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
