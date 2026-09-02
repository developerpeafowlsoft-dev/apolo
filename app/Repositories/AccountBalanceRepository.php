<?php
namespace App\Repositories;

use Abedin\Maker\Repositories\Repository;
use App\Models\AccountBalance;

class AccountBalanceRepository extends Repository
{
    public static function model()
    {
        return AccountBalance::class;    
    }

    public static function accountBalanaceCreate($request)
    {
        return self::query()->create(
            [
                'account_id' => $request->account_id,
                'financial_year_id' => $request->financial_year_id,
                'shop_id' => $request->shop_id,
                'opening_balance' => $request->acOpenBalance,
                'closing_balance' => $request->acCloseBalance,
            ]
        );
    }
}