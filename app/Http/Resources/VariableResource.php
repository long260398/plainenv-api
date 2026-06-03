<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class VariableResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'             => $this->id,
            'key'            => $this->key,
            'value'          => $this->value,
            'environment_id' => $this->environment_id,
            'updated_at'     => $this->updated_at->toISOString(),
        ];
    }
}
