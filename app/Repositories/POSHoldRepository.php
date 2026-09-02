<?php

namespace App\Repositories;

use Abedin\Maker\Repositories\Repository;
use App\Models\POSHold;

class POSHoldRepository extends Repository
{
    /**
     * Get model class name.
     */
    public static function model()
    {
        return POSHold::class;
    }

    /**
     * Store a suspended bill transaction.
     */
    public static function store(array $data): POSHold
    {
        return self::create([
            'shop_id' => $data['shop_id'],
            'counter_id' => $data['counter_id'] ?? null,
            'cashier_id' => $data['cashier_id'] ?? null,
            'bill_label' => $data['bill_label'] ?? ('Hold #' . time()),
            'items_json' => $data['items'],
            'customer_phone' => $data['customer_phone'] ?? null,
            'customer_name' => $data['customer_name'] ?? null,
            'customer_email' => $data['customer_email'] ?? null,
            'subtotal' => $data['subtotal'] ?? 0,
        ]);
    }
}
