<?php
namespace App\Repositories;

use Abedin\Maker\Repositories\Repository;
use App\Models\CounterMaster;

class CounterMasterRepository extends Repository
{
    public static function model()
    {
        return CounterMaster::class;    
    }

    public static function counterMasterCreate($request)
    {
        return self::create($request->all());
    }

    public static function counterMasterUpdate($request,$bankMaster)
    {
        $bankMaster->update($request->all());
        return $bankMaster;
    }
}