<?php
namespace App\Repositories;

use Abedin\Maker\Repositories\Repository;
use App\Models\DeliveryBy;

class DeliveryByRepository extends Repository
{
    public static function model()
    {
        return DeliveryBy::class;    
    }
}