<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AdminUserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => (int) $this->resource->id,
            'name' => "{$this->resource->first_name} {$this->resource->last_name}",
            'email' => (string) $this->resource->email,
            'phone_number' => (string) $this->resource->phone_number,
            'type' => (string) $this->resource->type,
            'date' => (string) $this->resource->created_at,
            'two_factor_enabled' => (bool) $this->resource->two_factor_enabled,
            'role' => $this->resource->roles ? $this->resource->roles->map(function ($role): array {
                return [
                    'id' => $role?->id,
                    'name' => $role?->name,
                    'permissions' => $role?->permissions->flatMap(function ($permission): array {
                        return [$permission->name];
                    })->toArray(),
                ];
            })->toArray() : [],
        ];
    }
}
