<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Inbox extends Model
{
    protected $guarded = [];

    protected $casts = [
      'active' => 'boolean',
    ];

    protected $with = ['proforma', 'user'];

    protected $hidden = ['id', 'user_id', 'proforma_id', 'created_at', 'updated_at'];

    public function proforma()
    {
        return $this->belongsTo(Proforma::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
