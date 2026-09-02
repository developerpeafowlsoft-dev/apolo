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
            'discount_price' => $row['disc'],
            'mrp' => $row['mrp'],
            'mark_up' => $row['mark_up'],
            'mark_down' => $row['mark_down'],
            'net_purc_rate' => $row['netPurcRate'],
            'hsn_master_id' => $row['taxCodeId'],
            'vat_tax_id' => $row['sgstId'],
        ]);


        if (!empty($row['colorInwardIds'])){
            foreach ($row['colorInwardIds'] as $colorId) {
                $product->colors()->attach($colorId, ['price' => $row['mrp']]);
            }
        }

        if (!empty($row['sizeInwardIds'])){
            foreach ($row['sizeInwardIds'] as $sizeId) {
                $product->sizes()->attach($sizeId, ['price' => $row['mrp']]);
            }
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
            'discount_price' => $row['disc'],
            'mrp' => $row['mrp'],
            'mark_up' => $row['mark_up'],
            'mark_down' => $row['mark_down'],
            'net_purc_rate' => $row['netPurcRate'],
            'hsn_master_id' => $row['taxCodeId'],
            'vat_tax_id' => $row['sgstId'],
        ]);

        // Colors
        $product->colors()->sync(
            collect($row['colorInwardIds'] ?? [])
                ->mapWithKeys(fn ($id) => [$id => ['price' => $row['mrp']]])
                ->toArray()
        );

        // Sizes
        $product->sizes()->sync(
            collect($row['sizeInwardIds'] ?? [])
                ->mapWithKeys(fn ($id) => [$id => ['price' => $row['mrp']]])
                ->toArray()
        );
        return $product;
    }

}