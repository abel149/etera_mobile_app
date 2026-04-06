<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PartsImage extends Model
{
    use HasFactory;

    protected $fillable = ['proforma_part_id', 'image_path'];

    public function part()
    {
        return $this->belongsTo(ProformaPart::class, 'proforma_part_id');
    }
}
