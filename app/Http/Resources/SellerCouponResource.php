<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\JsonApi\JsonApiResource;

class SellerCouponResource extends JsonApiResource
{
    /**
     * Get the resource's attributes.
     *
     * @return array<string, mixed>
     */
    public function toAttributes(Request $request): array
    {
        return [
            'name' => $this->resource->coupon_code,
            'discount_value' => $this->resource->discount,
            'discount_type' => $this->resource->discount_type,
            'start_date' => $this->resource->start_date->toDateString(),
            'end_date' => $this->resource->end_date->toDateString(),
            'min_order_value' => $this->resource->min_order_value,
            'max_order_value' => $this->resource->max_order_value,
            'status' => $this->resource->status,
        ];
    }

    /**
     * The resource's relationships.
     */
    public $relationships = [
        'coupons',
    ];
}
