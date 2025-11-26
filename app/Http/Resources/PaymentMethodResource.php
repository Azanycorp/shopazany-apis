<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PaymentMethodResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => (int) $this->resource->id,
            'type' => (string) $this->resource->type,
            'bank_name' => (string) $this->resource->bank_name,
            'account_number' => (string) $this->resource->account_number,
            'account_name' => (string) $this->resource->account_name,
            'platform' => (string) $this->resource->platform,
            'routing_number' => (string) $this->resource->routing_number,
            'is_default' => (bool) $this->resource->is_default,
        ];
    }
}
