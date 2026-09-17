<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Voucher extends Model
{
    use HasFactory;

    protected $fillable = [
        'voucher_no', 'voucher_type', 'status', 'date',
        'narration', 'shop_id', 'financial_year_id', 'sequence_id', 'branch_id', 'original_id', 'reverses_voucher_id',
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

    /** The voucher this one reverses, if it is a contra. */
    public function reverses() {
        return $this->belongsTo(Voucher::class, 'reverses_voucher_id');
    }

    /** The contra voucher that reverses this one, if it has been reversed. */
    public function reversal() {
        return $this->hasOne(Voucher::class, 'reverses_voucher_id');
    }
}