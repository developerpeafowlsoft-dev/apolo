<?php

namespace App\Repositories;

use Abedin\Maker\Repositories\Repository;
use App\Models\POSReturn;

class POSReturnRepository extends Repository
{
    /**
     * Get model class name.
     */
    public static function model()
    {
        return POSReturn::class;
    }
}
