<?php

namespace App\Repositories;

use Abedin\Maker\Repositories\Repository;
use App\Models\HoldBill;

class HoldBillRepository extends Repository
{
    /**
     * Get model class name.
     */
    public static function model()
    {
        return HoldBill::class;
    }
}
