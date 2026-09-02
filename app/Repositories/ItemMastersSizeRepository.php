<?php
namespace App\Repositories;

use Abedin\Maker\Repositories\Repository;
use App\Models\ItemMastersSize;

class ItemMastersSizeRepository extends Repository
{
    public static function model()
    {
        return ItemMastersSize::class;    
    }
}