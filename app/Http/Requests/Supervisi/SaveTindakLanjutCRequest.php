<?php

namespace App\Http\Requests\Supervisi;

use Illuminate\Foundation\Http\FormRequest;

class SaveTindakLanjutCRequest extends FormRequest
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
            'refleksi_guru' => ['required', 'string'],
            'pilihan_belajar_pmm' => ['nullable', 'string', 'max:255'],
            'pilihan_belajar_lainnya' => ['nullable', 'string'],
            'tujuan_tindak_lanjut' => ['required', 'string'],
            'upaya_tindak_lanjut' => ['required', 'string'],
            'periode_tindak_lanjut' => ['nullable', 'string', 'max:100'],
            'kebutuhan_dukungan' => ['nullable', 'string', 'max:100'],
            'detail_dukungan' => ['nullable', 'string'],
        ];
    }
}
