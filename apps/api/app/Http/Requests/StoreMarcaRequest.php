<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreMarcaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nome' => 'required|string|min:3|unique:marcas,nome',
            'imagem' => 'required|file|mimes:png|max:2048',
        ];
    }

    public function messages(): array
    {
        return [
            'nome.unique' => 'O nome da marca já existe.',
            'imagem.mimes' => 'O arquivo deve ser uma imagem do tipo PNG.',
        ];
    }
}
