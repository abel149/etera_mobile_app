<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PartResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'        => $this->id,
            'name'      => $this->name,
            'number'    => $this->number,
            'component' => $this->component,
            'condition' => $this->condition,
            'grade'     => $this->grade,
            'country'   => $this->country,
            'quantity'  => (int) $this->quantity,
        ];
    }
}
