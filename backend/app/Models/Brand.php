<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Brand extends Model
{
    use HasFactory;

    protected $fillable = ['name'];


    public function users()
    {
        return $this->belongsToMany(User::class, 'brand_users', 'brand_id', 'user_id');
    }

    public function proformas()
    {
        return $this->hasMany(Proforma::class, 'car_brand_id');
    }
}
