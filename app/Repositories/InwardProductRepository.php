<?php
namespace App\Repositories;

use Abedin\Maker\Repositories\Repository;
use App\Models\InwardProduct;
use Google\Rpc\Context\AttributeContext\Request;

class InwardProductRepository extends Repository
{
    public static function model()
    {
        return InwardProduct::class;    
    }

    public static function storeByInwardProductRequest($row,$inwardId): InwardProduct
    {

        $shop = generaleSetting('shop');
        $generaleSetting = generaleSetting('setting');

        /**
         * @var \App\Models\User $user
         */
        $user = auth()->user();
        $isAdmin = false;
        if ($user->hasRole('root') || ($generaleSetting?->shop_type == 'single')) {
            $isAdmin = true;
        }


        $product = self::create([
            'shop_id' => $shop?->id,
            'inward_invoice_id' => $inwardId,
            'product_id' => $row['itemid'],
            'design_master_id' => $row['designid'],
            'quantity' => $row['qty'],
            'buy_price' => $row['purcRate'],
            'price' => $row['amount'],
            'discount_price' => $row['disc'] ?? 0,
            'net_purc_price' => $row['netPurcPrice'] ?? ($row['net_purc_price'] ?? 0),
            'mrp' => $row['mrp'],
            'mark_up' => $row['mark_up'],
            'mark_down' => $row['mark_down'],
            'net_purc_rate' => $row['netPurcRate'],
            'hsn_master_id' => $row['taxCodeId'] ?? null,
            'vat_tax_id' => $row['sgstId'] ?? null,
        ]);


        if (!empty($row['colorInwardIds'])){
            $colorIds = array_unique(array_filter((array)$row['colorInwardIds']));
            $syncColors = [];
            foreach ($colorIds as $cId) {
                $syncColors[$cId] = ['price' => $row['mrp']];
            }
            $product->colors()->sync($syncColors);
        }

        if (!empty($row['sizeInwardIds'])){
            $sizeIds = array_unique(array_filter((array)$row['sizeInwardIds']));
            $syncSizes = [];
            foreach ($sizeIds as $sId) {
                $syncSizes[$sId] = ['price' => $row['mrp']];
            }
            $product->sizes()->sync($syncSizes);
        }

        return $product;
    }

    public static function updateByInwardProductRequest(InwardProduct $product, array $row): InwardProduct
    {

        $product->update([
            'product_id' => $row['itemid'],
            'design_master_id' => $row['designid'],
            'quantity' => $row['qty'],
            'buy_price' => $row['purcRate'],
            'price' => $row['amount'],
            'discount_price' => $row['disc'] ?? 0,
            'net_purc_price' => $row['netPurcPrice'] ?? ($row['net_purc_price'] ?? 0),
            'mrp' => $row['mrp'],
            'mark_up' => $row['mark_up'],
            'mark_down' => $row['mark_down'],
            'net_purc_rate' => $row['netPurcRate'],
            'hsn_master_id' => $row['taxCodeId'] ?? null,
            'vat_tax_id' => $row['sgstId'] ?? null,
        ]);

        // Colors
        $colorIds = array_unique(array_filter((array)($row['colorInwardIds'] ?? [])));
        $syncColors = [];
        foreach ($colorIds as $cId) {
            $syncColors[$cId] = ['price' => $row['mrp']];
        }
        $product->colors()->sync($syncColors);

        // Sizes
        $sizeIds = array_unique(array_filter((array)($row['sizeInwardIds'] ?? [])));
        $syncSizes = [];
        foreach ($sizeIds as $sId) {
            $syncSizes[$sId] = ['price' => $row['mrp']];
        }
        $product->sizes()->sync($syncSizes);

        return $product;
    }

}