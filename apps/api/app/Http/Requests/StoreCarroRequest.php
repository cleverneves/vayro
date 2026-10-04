<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCarroRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'modelo_id' => 'required|exists:modelos,id',
            'placa' => 'required|string|max:10|unique:carros,placa',
            'disponivel' => 'required|boolean',
            'km' => 'required|integer|min:0',
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
