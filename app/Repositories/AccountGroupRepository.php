<?php
namespace App\Repositories;

use Abedin\Maker\Repositories\Repository;
use App\Models\AccountGroup;

class AccountGroupRepository extends Repository
{
    public static function model()
    {
        return AccountGroup::class;    
    }

    public static function accountAddorUpdate($request)
    {

        return self::query()->updateOrCreate(
            ['id' => $request->id],
            [
                'name' => $request->accountname,
                'code' => $request->accountcode,
                'remark' => $request->remark,
                'account_type_id' => $request->account_type,
                'is_editable' => 1,
            ]
        );

    }
}