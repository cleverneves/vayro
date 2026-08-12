<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateLocacaoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Mescla os valores atuais da locação para que comparações entre campos
     * (after, gte) funcionem corretamente em atualizações parciais.
     */
    public function validationData()
    {
        $atual = $this->route('locacao')?->only(['data_inicio_periodo', 'km_inicial']) ?? [];

        return array_merge($atual, $this->all());
    }

    public function rules(): array
    {
        return [
            'cliente_id' => 'sometimes|required|exists:clientes,id',
            'carro_id' => 'sometimes|required|exists:carros,id',
            'data_inicio_periodo' => 'sometimes|required|date',
            'data_final_previsto_periodo' => 'sometimes|required|date|after:data_inicio_periodo',
            'data_final_realizado_periodo' => 'nullable|date|after:data_inicio_periodo',
            'valor_diaria' => 'sometimes|required|numeric|min:0',
            'km_inicial' => 'sometimes|required|integer|min:0',
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
