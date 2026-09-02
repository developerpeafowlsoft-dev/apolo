<?php

namespace App\Repositories;

use Abedin\Maker\Repositories\Repository;
use App\Models\POSShift;

class POSShiftRepository extends Repository
{
    /**
     * Get model class name.
     */
    public static function model()
    {
        return POSShift::class;
    }
}
