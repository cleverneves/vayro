<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class AvailableVehicleResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'placa' => $this->placa,
            'modelo' => [
                'id' => $this->modelo->id,
                'nome' => $this->modelo->nome,
                'marca' => [
                    'id' => $this->modelo->marca->id,
                    'nome' => $this->modelo->marca->nome,
                ],
            ],
        ];
    }
}
