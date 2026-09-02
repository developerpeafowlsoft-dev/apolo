<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Voucher extends Model
{
    use HasFactory;

    protected $fillable = [
        'voucher_no', 'voucher_type', 'status', 'date',
        'narration', 'shop_id', 'financial_year_id', 'sequence_id', 'branch_id', 'original_id'
    ];

    public function branch() {
        return $this->belongsTo(Branch::class);
    }

    public function entries()   {
        return $this->hasMany(VoucherEntry::class);
    }
    public function shop()    {
        return $this->belongsTo(Shop::class);
    }
    public function financialYear() {
        return $this->belongsTo(FinancialYear::class);
    }
}