<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateModeloRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'marca_id' => 'sometimes|required|exists:marcas,id',
            'nome' => [
                'sometimes', 'required', 'string', 'min:3',
                Rule::unique('modelos', 'nome')->ignore($this->route('modelo')),
            ],
            'imagem' => 'sometimes|file|mimes:png,jpeg,jpg|max:2048',
            'numero_portas' => 'sometimes|required|integer|min:1|max:99999',
            'lugares' => 'sometimes|required|integer|min:1|max:20',
            'air_bag' => 'sometimes|required|boolean',
            'abs' => 'sometimes|required|boolean',
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
