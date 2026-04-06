<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmployeeManager extends Model
{
    protected $guarded = [];

    protected $table = 'employee_managers';

    protected $with = ['employee', 'manager'];

    public function employee()
    {
        return $this->belongsTo(User::class, 'employee_id');
    }

    public function manager()
    {
        return $this->belongsTo(User::class, 'manager_id');
    }
}
