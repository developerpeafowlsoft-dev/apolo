<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BankMaster extends Model
{
    use HasFactory;

    protected $fillable = ['shop_id','bank_name','short_name','account_group_id','bank_ac_no','bank_branch','bank_swift_code','bank_ifsc_code','bank_address','is_active'];

    public function scopeBasicFields($query)
    {
        return $query->select([
            'id',
            'shop_id',
            'bank_name',
            'short_name',
            'account_group_id',
            'bank_ac_no',
            'bank_branch',
            'is_active'
        ]);
    }

    public function scopeIsActive($query)
    {
        return $query->where('is_active',1);
    }

    public function accountGroup()
    {
        return $this->belongsTo(AccountGroup::class);
    }
}