<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ApplicationPdf extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function application()
    {
        return $this->belongsTo(ProformaApplication::class, 'application_id');
    }

    public function isEncrypted(): bool
    {
        return $this->storage_type === 'encrypted';
    }
}
