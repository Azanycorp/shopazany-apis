<?php

namespace App\Http\Resources;

use App\Models\Product;
use App\Models\B2BProduct;
use App\Services\B2B\SellerService;
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
        return [
            'id' => (int)$this->id,
            'title' => (string)$this->title,
            'slug' => (string)$this->slug,
            'image' => (string)$this->image,
            'start_date' => (string)$this->start_date,
            'end_date' => (string)$this->end_date,
            'products' => $this->products,
            'status' => (string)$this->status,
        ];
    }
}
