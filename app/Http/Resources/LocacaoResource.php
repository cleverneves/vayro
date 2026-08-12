<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class LocacaoResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'data_inicio_periodo' => $this->data_inicio_periodo,
            'data_final_previsto_periodo' => $this->data_final_previsto_periodo,
            'data_final_realizado_periodo' => $this->data_final_realizado_periodo,
            'valor_diaria' => $this->valor_diaria,
            'km_inicial' => $this->km_inicial,
            'km_final' => $this->km_final,
            'cliente' => new ClienteResource($this->whenLoaded('cliente')),
            'carro' => new CarroResource($this->whenLoaded('carro')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
