<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CarPart extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function proformas()
    {
        return $this->belongsToMany(Proforma::class, 'proforma_part')
                    ->withPivot('number', 'grade', 'photo', 'country', 'quantity','condition')
                    ->withTimestamps();
    }
}
