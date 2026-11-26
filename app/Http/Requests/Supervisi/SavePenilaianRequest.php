<?php

namespace App\Http\Requests\Supervisi;

use Illuminate\Foundation\Http\FormRequest;

class SavePenilaianRequest extends FormRequest
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
            'jenis_penilaian_id' => ['required', 'exists:supervisi_jenis_penilaian,id'],
            'rekomendasi' => ['nullable', 'string'],
            'skor' => ['nullable', 'array'],
            'skor.*' => ['nullable', 'integer', 'between:1,4'],
            'catatan' => ['nullable', 'array'],
            'catatan.*' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
