<?php

namespace App\Http\Resources;

use App\Models\Product;
use App\Models\B2BProduct;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class B2BBannerResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $productIds = json_decode($this->products, true);

        $products = B2BProduct::whereIn('id', $productIds)
            ->select(['id', 'name', 'unit_price', 'description'])
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
