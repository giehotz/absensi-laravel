<?php

namespace App\Http\Requests\Supervisi;

use Illuminate\Foundation\Http\FormRequest;

class ResetSignatureRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'alasan' => ['required', 'string', 'min:5', 'max:500'],
            'dokumen_type' => ['nullable', 'string', 'max:50'],
        ];
    }
}
