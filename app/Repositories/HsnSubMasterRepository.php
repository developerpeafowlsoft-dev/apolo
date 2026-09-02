<?php
namespace App\Repositories;

use Abedin\Maker\Repositories\Repository;
use App\Models\HsnSubMaster;

class HsnSubMasterRepository extends Repository
{
    public static function model()
    {
        return HsnSubMaster::class;    
    }
}