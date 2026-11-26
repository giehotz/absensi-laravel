<?php

namespace App\Http\Requests\Supervisi;

use Illuminate\Foundation\Http\FormRequest;

class SignDokumenRequest extends FormRequest
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
            'signer_role' => ['required', 'in:guru,supervisor,kepala'],
            'tanggal_ttd' => ['nullable', 'date_format:Y-m-d'],
            'dokumen_type' => ['nullable', 'string', 'max:50'],
        ];
    }
}
