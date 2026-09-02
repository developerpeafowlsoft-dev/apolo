<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SyncQueue extends Model
{
    use HasFactory;

    protected $table = 'sync_queue';

    protected $fillable = [
        'syncable_type',
        'syncable_id',
        'payload',
        'status',
        'attempts',
        'last_attempted_at',
        'branch_id',
    ];

    protected $casts = [
        'payload' => 'array',
        'last_attempted_at' => 'datetime',
    ];

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }
}
