<?php

namespace App\Http\Requests\Supervisi;

use Illuminate\Foundation\Http\FormRequest;

class SaveTindakLanjutDRequest extends FormRequest
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
            'inspirasi_baru' => ['required', 'string'],
            'perubahan_praktik' => ['required', 'string'],
            'kendala_tindak_lanjut' => ['nullable', 'string'],
            'rencana_perbaikan_berkelanjutan' => ['nullable', 'string'],
        ];
    }
}
