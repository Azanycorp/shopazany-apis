<?php

namespace App\Http\Resources;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BannerResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $products = Product::whereIn('id', $this->products)
            ->select(['id', 'name', 'product_price', 'description', 'discount_price','slug'])
            ->get()
            ->toArray();

        return [
            'id' => (int)$this->id,
            'title' => (string)$this->title,
            'image' => (string)$this->image,
            'start_date' => (string)$this->start_date,
            'end_date' => (string)$this->end_date,
            'products' => $products,
            'status' => (string)$this->status,
        ];
    }
}
