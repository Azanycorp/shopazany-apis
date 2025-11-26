<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BankAccountResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'account_name' => $this->resource->account_name,
            'bank_name' => $this->resource->bank_name,
            'account_number' => $this->resource->account_number,
        ];
    }
}
