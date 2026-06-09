<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $parentRole = null;
        if ($this->role === 'employee' && $this->registered_by) {
            $parent = \App\Models\User::select('role')->find($this->registered_by);
            $parentRole = $parent?->role;
        }

        return [
            'id'           => $this->id,
            'name'         => $this->name,
            'email'        => $this->email,
            'phone_number' => $this->phone_number,
            'role'         => $this->role,
            'parent_role'  => $parentRole,
            'store_id'     => $this->store_id,
            'tin_number'   => $this->tin_number,
            'location'     => $this->location,
            'approved'     => (bool) $this->approved,
            'balance'      => (float) $this->balance,
            'created_at'   => $this->created_at?->toIso8601String(),
        ];
    }
}
