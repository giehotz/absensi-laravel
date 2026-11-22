<?php

namespace App\Http\Requests\Ujian;

use Illuminate\Foundation\Http\FormRequest;

class ImportJadwalExcelRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'ujian_periode_id' => ['required', 'integer', 'exists:ujian_periode,id'],
            'file' => ['required', 'file', 'mimes:xlsx,xls,csv', 'max:5120'],
        ];
    }

    public function messages(): array
    {
        return [
            'ujian_periode_id.required' => 'Periode ujian wajib dipilih.',
            'ujian_periode_id.exists' => 'Periode ujian yang dipilih tidak valid.',
            'file.required' => 'File Excel wajib diunggah.',
            'file.file' => 'Berkas yang diunggah harus berupa file.',
            'file.mimes' => 'Format file harus berupa Excel (.xlsx, .xls) atau .csv.',
            'file.max' => 'Ukuran file maksimal adalah 5MB.',
        ];
    }
}
