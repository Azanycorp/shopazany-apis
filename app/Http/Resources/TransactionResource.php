<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TransactionResource extends JsonResource
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
            'transaction_id' => (string) $this->resource->reference,
            'type' => (string) $this->resource->type,
            'date' => (string) $this->resource->date,
            'amount' => (string) $this->resource->amount,
            'status' => (string) $this->resource->status,
        ];
    }
}
