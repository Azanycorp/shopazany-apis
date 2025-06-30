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
            'id' => (int) $this->id,
            'transaction_id' => (string) $this->reference,
            'type' => (string) $this->type,
            'date' => (string) $this->date,
            'amount' => (string) $this->amount,
            'status' => (string) $this->status,
        ];
    }
}
