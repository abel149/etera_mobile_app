<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AppError extends Model
{
    protected $fillable = [
        'user_id',
        'url',
        'method',
        'status_code',
        'message',
        'trace',
        'hash',
        'seen',
        'fixed'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

