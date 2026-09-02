<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Branch extends Model
{
    use HasFactory;

    protected $fillable = [
        'branch_code',
        'name',
        'voucher_range_start',
        'voucher_range_end',
        'bill_prefix',
        'is_active',
        'last_synced_at',
        'api_key',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'last_synced_at' => 'datetime',
    ];

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function vouchers()
    {
        return $this->hasMany(Voucher::class);
    }

    public function voucherEntries()
    {
        return $this->hasMany(VoucherEntry::class);
    }

    public function productPurchases()
    {
        return $this->hasMany(ProductPurchase::class);
    }
}
