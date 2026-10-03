<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RegisterRenterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nome' => 'required|string|max:120',
            'email' => 'required|email',
            'telefone' => 'required|string|max:20',
            'senha' => 'required|string|min:8',
        ];
    }

    /**
     * @return array{name: string, email: string, phone: string, password: string}
     */
    public function payload(): array
    {
        $data = $this->validated();

        return [
            'name' => $data['nome'],
            'email' => $data['email'],
            'phone' => $data['telefone'],
            'password' => $data['senha'],
        ];
    }
}
