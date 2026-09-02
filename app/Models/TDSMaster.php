<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TDSMaster extends Model
{
    use HasFactory;

    protected $table = 'tds_masters';

    protected $fillable = ['shop_id','tds_code','tds_description','tds_payable_id','tds_receivable_id','from_date','to_date','tds_percentage','tds_limit','tds_single_trans_limit','is_active'];

    protected $casts = [
        'from_date' => 'date',
        'to_date'   => 'date',
    ];

    public function tdsPayable()
    {
        return $this->belongsTo(AccountMaster::class, 'tds_payable_id', 'id');
    }

    public function tdsReceivable()
    {
        return $this->belongsTo(AccountMaster::class, 'tds_receivable_id', 'id');
    }
}