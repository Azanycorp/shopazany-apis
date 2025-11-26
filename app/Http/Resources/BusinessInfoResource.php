<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BusinessInfoResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'business_location' => $this->resource->business_location,
            'business_type' => $this->resource->business_type,
            'identity_type' => $this->resource->identity_type,
            'file' => $this->resource->file,
            'status' => $this->resource->status,
        ];
    }
}
