<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class WithdrawalResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'             => $this->id,
            'amount'         => (float) $this->amount,
            'bank_name'      => $this->bank_name,
            'account_number' => $this->account_number,
            'status'         => $this->status,
            'created_at'     => $this->created_at?->toIso8601String(),
        ];
    }
}
