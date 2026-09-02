<?php
namespace App\Repositories;

use Abedin\Maker\Repositories\Repository;
use App\Models\TDSMaster;

class TDSMasterRepository extends Repository
{
    public static function model()
    {
        return TDSMaster::class;    
    }

    public static function tdsMasterCreate($request)
    {
        return self::create($request->all());
    }

    public static function tdsMasterUpdate($request,$tdsMaster)
    {
        $tdsMaster->update($request->all());
        return $tdsMaster;
    }
}