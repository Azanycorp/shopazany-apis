<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class WalletResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'available_balance' => $this->resource->balance,
            'total_income' => 0,
            'total_withdrawal' => 0,
            'total_points' => $this->resource->reward_point,
            'points_cleared' => $this->resource->reward_point_cleared,
        ];
    }
}
