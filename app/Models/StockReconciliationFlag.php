<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockReconciliationFlag extends Model
{
    use HasFactory;

    protected $table = 'stock_reconciliation_flags';

    protected $fillable = [
        'branch_id',
        'product_id',
        'reported_sale_qty',
        'previous_stock',
        'new_stock',
        'reason',
    ];

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
