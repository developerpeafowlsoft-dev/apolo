<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VoucherAuditLog extends Model
{
    use HasFactory;

    protected $fillable = ['voucher_id','user_id','action','meta'];
    protected $casts = ['meta' => 'array'];
}