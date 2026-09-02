<?php
namespace App\Repositories;

use Abedin\Maker\Repositories\Repository;
use App\Models\VoucherType;

class VoucherTypeRepository extends Repository
{
    public static function model()
    {
        return VoucherType::class;    
    }

    public static function voucherTypeUpadetOrCreate($request)
    {
        return self::query()->updateOrCreate(
            ['id' => $request->id],
            [
                'name' => $request->name,
                'code' => $request->code,
                'is_active' => 1,
                'is_default' => 1,
            ]
        );
    }
}