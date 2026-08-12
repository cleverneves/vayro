<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCarroRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'modelo_id' => 'sometimes|required|exists:modelos,id',
            'placa' => [
                'sometimes', 'required', 'string', 'max:10',
                Rule::unique('carros', 'placa')->ignore($this->route('carro')),
            ],
            'disponivel' => 'sometimes|required|boolean',
            'km' => 'sometimes|required|integer|min:0',
        ];
    }

    public function messages(): array
    {
        return [
            'modelo_id.exists' => 'O modelo informado não existe.',
            'placa.unique' => 'Já existe um carro cadastrado com essa placa.',
        ];
    }
}
