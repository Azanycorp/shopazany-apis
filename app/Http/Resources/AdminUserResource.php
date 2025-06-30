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
            'id' => (int) $this->id,
            'name' => $this->first_name.' '.$this->last_name,
            'email' => (string) $this->email,
            'phone_number' => (string) $this->phone_number,
            'type' => (string) $this->type,
            'role' => $this?->roles->first()?->name,
            'date' => (string) $this->created_at,
            'role' => $this->roles ? $this->roles->map(function ($role): array {
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
