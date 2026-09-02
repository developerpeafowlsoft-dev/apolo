<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VoucherEntry extends Model
{
    use HasFactory;

    protected $fillable = [
        'voucher_id','account_id','type','amount','description','branch_id'
    ];

    public function branch() {
        return $this->belongsTo(Branch::class);
    }

    public function voucher() {
        return $this->belongsTo(Voucher::class);
    }
    public function account() {
        return $this->belongsTo(Account::class);
    }
}