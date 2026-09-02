<?php
namespace App\Repositories;

use Abedin\Maker\Repositories\Repository;
use App\Models\Transport;

class TransportRepository extends Repository
{
    public static function model()
    {
        return Transport::class;    
    }
}