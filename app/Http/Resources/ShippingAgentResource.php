<?php

namespace App\Http\Resources;

use App\Models\Country;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ShippingAgentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $countryIds = $this->resource->country_ids;
        $locations = Country::whereIn('id', $countryIds)
            ->pluck('name')
            ->toArray();

        return [
            'id' => (int) $this->resource->id,
            'name' => (string) $this->resource->name,
            'type' => (string) $this->resource->type,
            'logo' => (string) $this->resource->logo,
            'locations' => $locations,
            'account_email' => (string) $this->resource->account_email,
            'account_password' => (string) $this->resource->account_password,
            'api_live_key' => (string) $this->resource->api_live_key,
            'api_test_key' => (string) $this->resource->api_test_key,
            'status' => (string) $this->resource->status,
        ];
    }
}
