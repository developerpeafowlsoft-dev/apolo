<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Country extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public function state()
    {
        return $this->belongsTo(State::class,'country_id','id');
    }

    public function cities()
    {
        return $this->hasManyThrough(City::class, State::class);
    }
}
