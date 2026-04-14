<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProformaResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'              => $this->id,
            'file_number'     => $this->file_number,
            'brand'           => $this->whenLoaded('brand', fn() => $this->brand?->name),
            'car_type'        => $this->car_type,
            'model'           => $this->model,
            'year'            => $this->year,
            'customer_name'   => $this->customer_name,
            'customer_phone'  => $this->customer_phone_number,
            'license_plate'   => $this->license_plate_number,
            'chassis_number'  => $this->chassis_number,
            'status'          => $this->status,
            'close_request'   => (bool) $this->close_request,
            'voice_note_url'  => $this->voice_note_path
                ? asset('storage/' . $this->voice_note_path)
                : null,
            'required_shops'  => $this->required_number_of_shops == 0
                ? '∞'
                : (int) $this->required_number_of_shops,
            'timer_duration'    => $this->timer_duration,
            'timer_expires_at'  => $this->timer_expires_at,
            'applications_count' => $this->whenLoaded('applications', fn() => $this->applications->count()),
            'can_request_close' => $this->whenLoaded('applications', fn() =>
                $this->status === 'published' &&
                !$this->close_request &&
                $this->applications->count() > 0
            ),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
