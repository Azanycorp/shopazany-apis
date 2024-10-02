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
            'id' => (int)$this->id,
            'name' => (string)$this->first_name . ' ' . $this->last_name,
            'email' => (string)$this->email,
            'permissions' => $this?->permissions->flatMap(function ($permission) {
                return [$permission->name];
            })->toArray(),
            'date' => (string)$this->created_at,
        ];
    }
}
