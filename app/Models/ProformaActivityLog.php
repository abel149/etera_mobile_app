<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProformaActivityLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'proforma_id',
        'user_id',
        'action',
        'details',
    ];

    public function proforma()
    {
        return $this->belongsTo(Proforma::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
