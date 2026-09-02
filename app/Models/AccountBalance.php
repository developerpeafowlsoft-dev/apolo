<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AccountBalance extends Model
{
    use HasFactory;

    protected $fillable = ['account_id','financial_year_id','shop_id','opening_balance','closing_balance'];

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class,'account_id','id');
    }

    public function financialYear(): BelongsTo
    {
        return $this->belongsTo(FinancialYear::class,'financial_year_id','id');
    }
}