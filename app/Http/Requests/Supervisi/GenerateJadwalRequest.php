<?php

namespace App\Http\Requests\Supervisi;

use Illuminate\Foundation\Http\FormRequest;

class GenerateJadwalRequest extends FormRequest
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
            'academic_year_id' => ['required', 'exists:academic_years,id'],
            'tanggal_mulai' => ['required', 'date'],
            'tanggal_selesai' => ['required', 'date', 'after_or_equal:tanggal_mulai'],
            'supervisor_teacher_ids' => ['required', 'array', 'min:1'],
            'supervisor_teacher_ids.*' => ['integer', 'exists:teachers,id'],
            'teacher_ids' => ['required', 'array', 'min:1'],
            'teacher_ids.*' => ['integer', 'exists:teachers,id'],
            'hari_aktif' => ['required', 'array', 'min:1'],
            'hari_aktif.*' => ['integer', 'between:1,6'],
            'max_per_day' => ['nullable', 'integer', 'between:1,6'],
            'indikator_pmm_id' => ['nullable', 'exists:supervisi_jenis_penilaian,id'],
        ];
    }
}
