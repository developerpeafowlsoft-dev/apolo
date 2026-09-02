<?php
namespace App\Repositories;

use Abedin\Maker\Repositories\Repository;
use App\Models\ProductBarcode;

class ProductBarcodeRepository extends Repository
{
    public static function model()
    {
        return ProductBarcode::class;    
    }
}