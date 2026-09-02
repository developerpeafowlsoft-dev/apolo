<?php
namespace App\Repositories;

use Abedin\Maker\Repositories\Repository;
use App\Models\ItemMastersColor;

class ItemMastersColorRepository extends Repository
{
    public static function model()
    {
        return ItemMastersColor::class;    
    }
}