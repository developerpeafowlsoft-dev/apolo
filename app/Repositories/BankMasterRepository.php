<?php
namespace App\Repositories;

use Abedin\Maker\Repositories\Repository;
use App\Models\BankMaster;

class BankMasterRepository extends Repository
{
    public static function model()
    {
        return BankMaster::class;    
    }

    public static function bankMasterCreate($request)
    {
        return self::create($request->all());
    }

    public static function bankMasterUpdate($request,$bankMaster)
    {
        $bankMaster->update($request->all());
        return $bankMaster;
    }

}