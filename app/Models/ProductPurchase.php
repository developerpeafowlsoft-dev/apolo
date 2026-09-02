<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductPurchase extends Model
{
    use HasFactory;

    protected $fillable = [
        'inward_invoice_id',
        'purchase_date',
        'purchase_day_name',
        'purchase_time',
        'bill_date',
        'voucher_id',
        'total_taxable',
        'total_cgst',
        'total_sgst',
        'total_igst',
        'round_off',
        'grand_total',
        'is_purchase',
        'is_return',
        'is_online_product',
        'branch_id',
        'original_id',
    ];

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function inwardInvoice()
    {
        return $this->belongsTo(InwardInvoice::class);
    }
}