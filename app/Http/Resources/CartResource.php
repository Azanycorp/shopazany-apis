<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CartResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $pricePerItem = $this->calculatePrice();
        $totalPrice = $pricePerItem * $this->quantity;

        return [
            'id' => (int)$this->id,
            'quantity' => (int)$this->quantity,
            'product' => (object) [
                'name' => optional($this->product)->name,
                'slug' => optional($this->product)->slug,
                'description' => optional($this->product)->description,
                'category' => (object) [
                    'category_id' => optional($this->product)->category_id,
                    'category_name' => optional($this->product)->category?->name,
                    'sub_category_id' => optional($this->product)->sub_category_id,
                    'sub_category_name' => optional($this->product)->subCategory?->name,
                ],
                'sub_category' => optional($this->product)->subCategory?->name,
                'product_price' => (int)optional($this->product)->product_price,
                'discount_price' => (int)optional($this->product)->discount_price,
                'price' => (int)optional($this->product)->price,
            ],
            'seller' => (object) [
                'first_name' => optional($this->product)->user?->first_name,
                'last_name' => optional($this->product)->user?->last_name,
            ],
            'total_price' => $totalPrice,
        ];
    }

    private function calculatePrice()
    {
        return optional($this->product)->price;
    }

}
