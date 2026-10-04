<?php

namespace App\Http\Requests;

use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;

class ListAvailableVehiclesRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'data_inicio' => ['required', 'date', $this->notInThePast()],
            'quantidade_dias' => 'required|integer|min:1|max:90',
        ];
    }

    /**
     * @return array{starts_on: string, day_count: int}
     */
    public function payload(): array
    {
        $data = $this->validated();

        return [
            'starts_on' => $data['data_inicio'],
            'day_count' => (int) $data['quantidade_dias'],
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
