<?php
namespace App\Repositories;

use Abedin\Maker\Repositories\Repository;
use App\Models\Account;

class AccountRepository extends Repository
{
    public static function model()
    {
        return Account::class;    
    }

    public static function accountsAddorUpdate($request)
    {
        return self::query()->updateOrCreate(
            ['id' => $request->id],
            [
                'name' => $request->name,
                'code' => $request->code,
                'account_group_id' => $request->account_group_id,
                'remark' => $request->remark,
                'is_active' => 1,
                'is_default' => 1,
            ]
        );
    }
}