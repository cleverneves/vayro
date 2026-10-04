<?php

namespace App\Http\Requests;

use App\Support\RentalVocabulary;
use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;

class StoreRentalRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'automovel_id' => 'required|integer',
            'data_inicio' => ['required', 'date', $this->notInThePast()],
            'quantidade_dias' => 'required|integer|min:1|max:90',
            'motivo' => 'required|in:viagem,passeio,dia-a-dia',
            'comentario' => 'nullable|string|max:500',
        ];
    }

    /**
     * @return array{vehicle_id: int, starts_on: string, day_count: int, reason: string, comment: ?string}
     */
    public function payload(): array
    {
        $data = $this->validated();

        return [
            'vehicle_id' => (int) $data['automovel_id'],
            'starts_on' => $data['data_inicio'],
            'day_count' => (int) $data['quantidade_dias'],
            'reason' => RentalVocabulary::reasonToEnglish($data['motivo']),
            'comment' => $data['comentario'] ?? null,
        ];
    }

    private function notInThePast(): \Closure
    {
        return function (string $attribute, mixed $value, \Closure $fail): void {
            $timezone = config('rental.timezone');
            $today = now($timezone)->startOfDay();
            $date = Carbon::parse($value, $timezone)->startOfDay();

            if ($date->lt($today)) {
                $fail('A data de início deve ser o dia corrente ou futura.');
            }
        };
    }
}
