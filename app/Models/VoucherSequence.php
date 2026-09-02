<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VoucherSequence extends Model
{
    use HasFactory;

    protected $fillable = [
        'shop_id','financial_year_id','voucher_type', 'prefix','padding','current_no','reset_policy'
    ];
}