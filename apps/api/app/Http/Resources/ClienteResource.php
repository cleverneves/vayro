<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ClienteResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'nome' => $this->nome,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
