<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ApplicationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $subtotal    = $this->prices->sum('part_total');
        $discount    = (float) ($this->discount ?? 0);
        $discountAmt = $subtotal > 0 ? ($subtotal * $discount / 100) : 0;
        $netTotal    = $subtotal > 0 ? ($subtotal - $discountAmt) : (float) ($this->amount ?? 0);

        return [
            'id'       => $this->id,
            'from'     => $this->from,
            'applicant' => [
                'name'            => $this->applicationBy->name ?? null,
                'phone'           => $this->applicationBy->phone_number ?? null,
                'store_id'        => $this->applicationBy->store_id ?? null,
                'tin_number'      => $this->applicationBy->tin_number ?? null,
                'location'        => $this->applicationBy->location ?? null,
                'stamp_image_url' => $this->applicationBy->stamp_image
                    ? asset('storage/' . $this->applicationBy->stamp_image)
                    : null,
            ],
            'parts_pricing' => $this->prices->map(fn($p) => [
                'car_part_id' => $p->car_part_id,
                'unit_price'  => (float) $p->unit_price,
                'part_total'  => (float) $p->part_total,
            ]),
            'subtotal'        => round($subtotal, 2),
            'discount_pct'    => $discount,
            'discount_amount' => round($discountAmt, 2),
            'net_total'       => round($netTotal, 2),
        ];
    }
}
