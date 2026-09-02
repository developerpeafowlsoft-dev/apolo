<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CounterMaster extends Model
{
    use HasFactory;

    protected $fillable = ['shop_id','code','counter_name','counter_short_name','floor','voucher_prefix','is_active'];

}