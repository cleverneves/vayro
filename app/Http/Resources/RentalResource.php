<?php

namespace App\Http\Resources;

use App\Support\RentalVocabulary;
use Illuminate\Http\Resources\Json\JsonResource;

class RentalResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'locatario' => [
                'id' => $this->renter->id,
                'nome' => $this->renter->nome,
                'email' => $this->renter->email,
                'telefone' => $this->renter->phone,
            ],
            'automovel' => [
                'id' => $this->vehicle->id,
                'placa' => $this->vehicle->placa,
                'modelo' => [
                    'id' => $this->vehicle->modelo->id,
                    'nome' => $this->vehicle->modelo->nome,
                    'marca' => [
                        'id' => $this->vehicle->modelo->marca->id,
                        'nome' => $this->vehicle->modelo->marca->nome,
                    ],
                ],
            ],
            'data_inicio' => $this->starts_on->toDateString(),
            'quantidade_dias' => $this->day_count,
            'periodo' => [
                'data_inicio' => $this->starts_on->toDateString(),
                'data_fim' => $this->lastReservedDate()->toDateString(),
            ],
            'motivo' => RentalVocabulary::reasonToPortuguese($this->reason),
            'comentario' => $this->blankToNull($this->comment),
            'status' => RentalVocabulary::statusToPortuguese($this->status),
            'observacao' => $this->blankToNull($this->admin_note),
            'data_solicitacao' => $this->requested_on->toDateString(),
        ];
    }

    private function blankToNull(?string $value): ?string
    {
        return $value === '' ? null : $value;
    }
}
