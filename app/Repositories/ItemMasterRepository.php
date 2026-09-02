<?php
namespace App\Repositories;

use Abedin\Maker\Repositories\Repository;
use App\Http\Requests\ItemMasterRequest;
use App\Models\ItemMaster;
use App\Models\Product;
use Illuminate\Support\Str;
use Mews\Purifier\Facades\Purifier;

class ItemMasterRepository extends Repository
{
    public static function model()
    {
        return ItemMaster::class;    
    }

    public static function storeByRequest(ItemMasterRequest $request): Product
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
            'name' => $request->name,
            'brand_id' => $request->brand_id,
            'code' => $request->code,
            'vat_tax_id' => $request->vat_tax_id,
            'unit_id' => $request->unit_id,
            'buy_price' => $request->buy_price ?? 0,
            'price' => $request->price,
            'discount_price' => $request->discount_percentage,
            'mrp' => $request->mrp,
            'mark_up' => $request->mark_up,
            'mark_down' => $request->mark_down,
            'quantity' => $request->quantity ?? 0,
            'salesman_id' => $request->salesman_id ?? 0,
            'commission_type' => $request->commission_type ?? null,
            'salesman_comm' => $request->salesman_comm ?? 0,
            'salesman_comm_amt' => $request->salesman_comm_amt ?? 0,
        ]);

//        foreach ($request->names ?? [] as $key => $value) {
//            if (! $key || ! $value) {
//                continue;
//            }
//
//            $description = array_key_exists($key, $request->descriptions ?? []) ? $request->descriptions[$key] : null;
//            $shortDescription = array_key_exists($key, $request->short_descriptions ?? []) ? $request->short_descriptions[$key] : null;
//
//            ProductTranslation::create([
//                'product_id' => $product->id,
//                'lang' => $key,
//                'name' => $value,
//                'description' => $description,
//                'short_description' => $shortDescription,
//            ]);
//        }

        if ($request->is('api/*')) {
            if ($request->color && is_array($request->color)) {
                $colors = array_column($request->color, 'id');
                $product->colors()->sync($colors);
            }
        } else {
            foreach ($request->color ?? [] as $color) {
                $product->colors()->attach($color['id'], ['price' => $color['price']]);
            }
        }

        $product->categories()->sync($request->category ?? []);
        $product->subcategories()->sync($request->sub_category ?? []);

        if ($request->is('api/*')) {
            if ($request->size && is_array($request->size)) {
                foreach ($request->size ?? [] as $size) {
                    $price = 0;
                    $product->sizes()->attach($size, ['price' => $price]);
                }
            }
        } else {
            foreach ($request->size ?? [] as $size) {
                $product->sizes()->attach($size['id'], ['price' => $size['price']]);
            }
        }

//        foreach ($request->additionThumbnail ?? [] as $additionThumbnail) {
//            $thumbnail = MediaRepository::storeByRequest($additionThumbnail, 'products', 'thumbnail', 'image');
//            $product->medias()->attach($thumbnail->id);
//        }

        return $product;
    }
}