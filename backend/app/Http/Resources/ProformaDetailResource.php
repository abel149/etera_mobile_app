<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProformaDetailResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'proforma' => new ProformaResource($this->resource),
            'parts'    => PartResource::collection($this->whenLoaded('parts')),
            'invoice'  => $this->when(
                $this->relationLoaded('proformaInvoice') && $this->proformaInvoice,
                fn() => [
                    'sku' => $this->proformaInvoice->sku,
                    'url' => url('/transaction/' . $this->proformaInvoice->sku),
                ]
            ),
        ];
    }
}
