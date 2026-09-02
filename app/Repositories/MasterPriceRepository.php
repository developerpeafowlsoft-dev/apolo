<?php
namespace App\Repositories;

use Abedin\Maker\Repositories\Repository;
use App\Models\MasterPrice;

class MasterPriceRepository extends Repository
{
    public static function model()
    {
        return MasterPrice::class;    
    }
}