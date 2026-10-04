<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class ModeloResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'nome' => $this->nome,
            'imagem_url' => Storage::disk('public')->url($this->imagem),
            'numero_portas' => $this->numero_portas,
            'lugares' => $this->lugares,
            'air_bag' => $this->air_bag,
            'abs' => $this->abs,
            'marca' => new MarcaResource($this->whenLoaded('marca')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
