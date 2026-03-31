<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WithdrawalRequest extends Model
{
    use HasFactory;

    protected $guarded = [];


    public function from()
    {
        return $this->belongsTo(User::class, 'from');
    }

    public function owner()
    {
        return $this->belongsTo(User::class, 'from');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function approve()
    {
        $this->status = 'approved';
        if ($this->owner->balance < $this->amount) {
            return redirect()->back()->with('error', 'Insufficient balance to approve this withdrawal request');
        }
        $this->owner->balance -= $this->amount;
        $this->owner->save();
        $this->save();
    }

    public function reject()
    {
        $this->status = 'rejected';
        $this->save();
    }
}
