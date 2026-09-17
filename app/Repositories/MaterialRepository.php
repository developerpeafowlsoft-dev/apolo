<?php
namespace App\Repositories;

use Abedin\Maker\Repositories\Repository;
use App\Models\Material;

class MaterialRepository extends Repository
{
    public static function model()
    {
        return Material::class;    
    }

    public static function materialrCreate($request)
    {
        $data = $request->all();

        $shopId = $data['shop_id'] ?? null;
        if (! $shopId) {
            $user = auth()->user();
            if ($user && ! $user->hasRole('root')) {
                $shopId = $user->shop?->id ?? $user->myShop?->id;
            }
        }

        if (! $shopId) {
            $shopId = Material::whereNotNull('shop_id')->where('shop_id', '!=', 1)->value('shop_id')
                ?? Material::whereNotNull('shop_id')->value('shop_id')
                ?? generaleSetting('rootShop')?->id;
        }

        $data['shop_id'] = $shopId;
        $data['created_by'] = auth()->id() ?? 1;

        return self::create($data);
    }

    public static function materialUpdate($request,$material)
    {
        $material->update($request->all());
        return $material;
    }
}