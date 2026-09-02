<?php
namespace App\Repositories;

use Abedin\Maker\Repositories\Repository;
use App\Models\AccountMaster;

class AccountMasterRepository extends Repository
{
    public static function model()
    {
        return AccountMaster::class;    
    }

    public static function accountMasterCreate($request)
    {
        return self::create($request->all());
    }

    public static function accountMasterUpdate($request,$accountMaster)
    {
        $accountMaster->update($request->all());
        return $accountMaster;
    }
}