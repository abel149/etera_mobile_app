<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Commission extends Model
{
    use HasFactory;

    protected $table = 'commission';

    protected $fillable = [
        'shopPay',
        'garagePay',
        'insurancePay',
        'operatorPay',
        'othersPay',
    ];
}
