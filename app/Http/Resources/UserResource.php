<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'nama' => $this->name,
            'email' => $this->email,
            'password' => $this->password,
            'is_admin' => $this->is_admin,
            'status' => $this->status,
            'images' => $this->images
                ? (filter_var($this->images, FILTER_VALIDATE_URL)
                    ? $this->images
                    : asset('storage/' . $this->images))
                : null,
        ];
    }
}
