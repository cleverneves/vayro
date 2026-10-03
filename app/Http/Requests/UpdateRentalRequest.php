<?php

namespace App\Http\Requests;

use App\Support\RentalVocabulary;
use Illuminate\Foundation\Http\FormRequest;

class UpdateRentalRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status' => 'sometimes|required|in:solicitada,confirmada,em_andamento,concluida,cancelada',
            'observacao' => 'sometimes|nullable|string|max:500',
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            if (!$this->exists('status') && !$this->exists('observacao')) {
                $validator->errors()->add('status', 'Informe o status ou a observação.');
            }
        });
    }

    /**
     * @return array{status?: string, admin_note?: ?string}
     */
    public function payload(): array
    {
        $data = $this->validated();
        $payload = [];

        if (array_key_exists('status', $data)) {
            $payload['status'] = RentalVocabulary::statusToEnglish($data['status']);
        }

        if (array_key_exists('observacao', $data)) {
            $payload['admin_note'] = $data['observacao'];
        }

        return $payload;
    }
}
