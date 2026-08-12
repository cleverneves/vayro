<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreModeloRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'marca_id' => 'required|exists:marcas,id',
            'nome' => 'required|string|min:3|unique:modelos,nome',
            'imagem' => 'required|file|mimes:png,jpeg,jpg|max:2048',
            'numero_portas' => 'required|integer|min:1|max:99999',
            'lugares' => 'required|integer|min:1|max:20',
            'air_bag' => 'required|boolean',
            'abs' => 'required|boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'marca_id.exists' => 'A marca informada não existe.',
            'nome.unique' => 'O nome do modelo já existe.',
            'imagem.mimes' => 'O arquivo deve ser uma imagem do tipo PNG ou JPEG.',
        ];
    }
}
