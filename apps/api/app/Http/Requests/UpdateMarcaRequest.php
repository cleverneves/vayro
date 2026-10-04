<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateMarcaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nome' => [
                'sometimes', 'required', 'string', 'min:3',
                Rule::unique('marcas', 'nome')->ignore($this->route('marca')),
            ],
            'imagem' => 'sometimes|file|mimes:png|max:2048',
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
