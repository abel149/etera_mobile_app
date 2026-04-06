<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cost extends Model
{
    use HasFactory;

    protected $fillable = [
        'cost_id',
        'etera_chereta_cost',
        '1_proforma_cost',
        '2_proforma_cost',
        '3_proforma_cost',
        '4_proforma_cost',
        'insurance_proforma',
        'insured_cost',
    ];

    // Optional: relationship to previous cost record
    public function previousCost()
    {
        return $this->belongsTo(Cost::class, 'cost_id');
    }
}
