<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'address' => $this->address, // JSON/array
            'role' => $this->role, // ✅ ADD THIS - includes admin role
            'is_admin' => $this->role === 'admin', // ✅ ADD THIS - boolean for easy frontend check
            'orders_count' => $this->orders()->count(), // total orders
            'created_at' => $this->created_at,
        ];
    }
}