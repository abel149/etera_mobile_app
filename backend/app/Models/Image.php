<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class Image extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function getUrlAttribute()
    {
        $path = (string) ($this->path ?? '');
        if (Str::startsWith($path, 'public/')) {
            return Storage::url($path);
        }
        return asset($path);
    }
}
