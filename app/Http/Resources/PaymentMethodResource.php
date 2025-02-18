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
            "id" => (int)$this->id,
            "type" => (string)$this->type,
            "bank_name" => (string)$this->bank_name,
            "account_number" => (string)$this->account_number,
            "account_name" => (string)$this->account_name,
        ];
    }
}
