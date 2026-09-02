<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Account extends Model
{
    use HasFactory;

    protected $fillable = ['name','code','account_group_id','remark','is_active','is_default'];

    public function accountGroup()
    {
        return $this->belongsTo(AccountGroup::class,'account_group_id','id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active',1);
    }
}