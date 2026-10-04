<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreLocacaoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'cliente_id' => 'required|exists:clientes,id',
            'carro_id' => 'required|exists:carros,id',
            'data_inicio_periodo' => 'required|date',
            'data_final_previsto_periodo' => 'required|date|after:data_inicio_periodo',
            'data_final_realizado_periodo' => 'nullable|date|after:data_inicio_periodo',
            'valor_diaria' => 'required|numeric|min:0',
            'km_inicial' => 'required|integer|min:0',
            'km_final' => 'nullable|integer|min:0|gte:km_inicial',
        ];
    }

    public function messages(): array
    {
        return [
            'cliente_id.exists' => 'O cliente informado não existe.',
            'carro_id.exists' => 'O carro informado não existe.',
            'km_final.gte' => 'A quilometragem final não pode ser menor que a inicial.',
        ];
    }
}
