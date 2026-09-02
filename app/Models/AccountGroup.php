<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AccountGroup extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'remark',
        'account_type_id',
        'is_editable',
    ];

    public function accountType()
    {
        return $this->belongsTo(AccountType::class, 'account_type_id', 'id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_editable',1);
    }


}