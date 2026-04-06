<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProformaPart extends Model
{
    use HasFactory;

    protected $table = 'proforma_part';

    protected $fillable = [
        'proforma_id',
        'number',
        'grade',
        'country',
        'quantity',
        'condition',
        'component',
    ];

    protected $casts = [
        'quantity' => 'integer',
    ];

    public function proforma()
    {
        return $this->belongsTo(Proforma::class);
    }

    // New relationship: each part has many images
    public function images()
    {
        return $this->hasMany(PartsImage::class, 'proforma_part_id');
    }
}
