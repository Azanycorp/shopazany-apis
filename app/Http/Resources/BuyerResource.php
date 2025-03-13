<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BuyerResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => (int)$this->id,
            'uuid' => (string)$this->uuid,
            "first_name" => (string)$this->first_name,
            "last_name" => (string)$this->last_name,
            "middlename" => (string)$this->middlename,
            "phone" => (string)$this->phone,
            "email" => (string)$this->email,
            "default_currency" => (string)$this->default_currency,
            "date_of_birth" => (string)$this->date_of_birth,
            "image" => (string)$this->image,
            "address" => (object)[
                "address" => (string)$this->address,
                "city" => (string)$this->city,
                "country" => (string)optional($this->userCountry)->name,
                "state" => (string)optional($this->state)->name,
            ],
            "is_approved" => $this->is_admin_approve,
            "status" => (string)$this->status,
            'company' => $this->b2bCompany
        ];
    }
}
