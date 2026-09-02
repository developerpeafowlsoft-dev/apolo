<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Salesman extends Model
{
    use HasFactory;
    protected $table = 'salesmans';

    protected $fillable = ['shop_id','name','last_name','phone','email','src','gender','date_of_birth','is_active'];

    public function thumbnail(): Attribute
    {
        $thumbnail = asset('default/profile.jpg');

        if ($this->src && Storage::disk('public')->exists($this->src)) {
            $thumbnail = Storage::url($this->src); // gives /storage/salesman/profile/filename.png
        }

        return Attribute::make(
            get: fn() => $thumbnail
        );
    }

    public function scopeActive()
    {
        return $this->where('is_active',1);
    }

}