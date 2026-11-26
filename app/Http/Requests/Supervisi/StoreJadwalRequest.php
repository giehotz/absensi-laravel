<?php

namespace App\Http\Requests\Supervisi;

use Illuminate\Foundation\Http\FormRequest;

class StoreJadwalRequest extends FormRequest
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
            'teacher_id' => ['required', 'exists:teachers,id', 'different:supervisor_teacher_id'],
            'supervisor_teacher_id' => ['nullable', 'exists:teachers,id'],
            'school_class_id' => ['nullable', 'exists:school_classes,id'],
            'subject_id' => ['nullable', 'exists:subjects,id'],
            'indikator_pmm_id' => ['nullable', 'exists:supervisi_jenis_penilaian,id'],
            'tanggal' => ['required', 'date'],
            'jam_ke' => ['nullable', 'string', 'max:10'],
            'jam_mulai' => ['nullable', 'date_format:H:i'],
            'jam_selesai' => ['nullable', 'date_format:H:i', 'after:jam_mulai'],
            'materi' => ['nullable', 'string', 'max:255'],
            'catatan' => ['nullable', 'string', 'max:1000'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'teacher_id.different' => 'Guru yang disupervisi tidak boleh sama dengan supervisor penilai.',
            'jam_selesai.after' => 'Jam selesai harus lebih lambat daripada jam mulai.',
        ];
    }
}
