<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateRenterProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nome' => 'sometimes|required|string|max:120',
            'email' => 'sometimes|required|email',
            'telefone' => 'sometimes|required|string|max:20',
            'senha' => 'prohibited',
            'papel' => 'prohibited',
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            if (!$this->exists('nome') && !$this->exists('email') && !$this->exists('telefone')) {
                $validator->errors()->add('nome', 'Informe ao menos um campo para atualizar.');
            }
        });
    }

    /**
     * @return array{name?: string, email?: string, phone?: string}
     */
    public function payload(): array
    {
        $data = $this->validated();
        $payload = [];

        if (array_key_exists('nome', $data)) {
            $payload['name'] = $data['nome'];
        }

        if (array_key_exists('email', $data)) {
            $payload['email'] = $data['email'];
        }

        if (array_key_exists('telefone', $data)) {
            $payload['phone'] = $data['telefone'];
        }

        return $payload;
    }
}
