<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PaymentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'order_no' => $this?->order->order_no,
            'user' => (object)[
                'first_name' => optional($this->user)->first_name,
                'last_name' => optional($this->user)->last_name,
                'middlename' => optional($this->user)->middlename,
                'email' => optional($this->user)->email,
                'phone' => optional($this->user)->phone,
            ],
            'amount' => $this->amount,
            'payment_method' => $this?->order->payment_method,
            'status' => $this->status,
            'reference' => $this->reference,
            'created_at' => $this->created_at,
        ];
    }
}
