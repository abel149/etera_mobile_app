<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'           => $this->id,
            'name'         => $this->name,
            'email'        => $this->email,
            'phone_number' => $this->phone_number,
            'role'         => $this->role,
            'store_id'     => $this->store_id,
            'tin_number'   => $this->tin_number,
            'location'     => $this->location,
            'approved'     => (bool) $this->approved,
            'balance'      => (float) $this->balance,
            'created_at'   => $this->created_at?->toIso8601String(),
        ];
    }
}
