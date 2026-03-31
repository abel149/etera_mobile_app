<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Partner extends Model
{
    use HasFactory;

    protected $fillable = [
      'insurance_id',
      'partner_id',
    ];

    public function insurance()
    {
        return $this->belongsTo(User::class, 'insurance_id');
    }

    public function partner()
    {
        return $this->belongsTo(User::class, 'partner_id');
    }

}
