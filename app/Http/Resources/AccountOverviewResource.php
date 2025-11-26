<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Date;

class AccountOverviewResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->resource->id,
            'uuid' => $this->resource->uuid,
            'first_name' => $this->resource->first_name,
            'last_name' => $this->resource->last_name,
            'middlename' => $this->resource->middlename,
            'email' => $this->resource->email,
            'phone' => $this->resource->phone,
            'date_created' => Date::parse($this->resource->created_at)->format('d M Y'),
        ];
    }
}
