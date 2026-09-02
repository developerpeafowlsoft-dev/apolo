<?php
namespace App\Repositories;

use Abedin\Maker\Repositories\Repository;
use App\Models\AccountType;

class AccountTypeRepository extends Repository
{
    public static function model()
    {
        return AccountType::class;    
    }

    public static function accountTypeUpadetOrCreate($request)
    {
        return self::query()->updateOrCreate(
            ['id' => $request->id],
            [
                'name' => $request->name,
                'is_active' => 1,
            ]
        );
    }
}