<?php
namespace App\Repositories;

use Abedin\Maker\Repositories\Repository;
use App\Models\Season;

class SeasonRepository extends Repository
{
    public static function model()
    {
        return Season::class;    
    }
}