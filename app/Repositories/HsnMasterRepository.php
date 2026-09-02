<?php
namespace App\Repositories;

use Abedin\Maker\Repositories\Repository;
use App\Models\HsnMaster;

class HsnMasterRepository extends Repository
{
    public static function model()
    {
        return HsnMaster::class;    
    }

    public static function hsnMasterCreate($request)
    {
        return self::create($request->all());
    }

    public static function hsnMasterUpdate($request,$tdsMaster)
    {
        $tdsMaster->update($request->all());
        return $tdsMaster;
    }
}