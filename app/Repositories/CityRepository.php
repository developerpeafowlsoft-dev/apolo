<?php
namespace App\Repositories;

use Abedin\Maker\Repositories\Repository;
use App\Models\City;

class CityRepository extends Repository
{
    public static function model()
    {
        return City::class;    
    }
}