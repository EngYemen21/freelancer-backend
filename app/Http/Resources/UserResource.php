<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource {
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array {
        return [
            'id' => $this->id,
            'firstName' => $this->firstName,
            'username'  => $this->username,
            'email' => $this->email,
            'image' => $this->image,
            'field' =>  $this->field,
            'Specialization' =>$this->Specialization,
            'role' => $this->role,
            'user_type' => $this->user_type,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
