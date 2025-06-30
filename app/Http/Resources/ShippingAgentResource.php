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
        $countryIds = $this->country_ids;
        $locations = Country::whereIn('id', $countryIds)
            ->pluck('name')
            ->toArray();

        return [
            'id' => (int) $this->id,
            'name' => (string) $this->name,
            'type' => (string) $this->type,
            'logo' => (string) $this->logo,
            'locations' => $locations,
            'account_email' => (string) $this->account_email,
            'account_password' => (string) $this->account_password,
            'api_live_key' => (string) $this->api_live_key,
            'api_test_key' => (string) $this->api_test_key,
            'status' => (string) $this->status,
        ];
    }
}
