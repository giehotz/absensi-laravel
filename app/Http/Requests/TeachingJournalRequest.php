<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class TeachingJournalRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'school_class_id' => ['required', 'exists:school_classes,id'],
            'subject_id' => ['required', 'exists:subjects,id'],
            'schedule_id' => ['nullable', 'exists:schedules,id'],
            'date' => ['required', 'date'],
            'meeting_number' => ['required', 'integer', 'min:1'],
            'learning_objective' => ['required', 'string'],
            'teaching_activity' => ['required', 'string'],
            'teaching_problem' => ['nullable', 'string'],
            'is_shared_with_students' => ['nullable', 'boolean'],
            'notes' => ['nullable', 'string'],
        ];
    }

    /**
     * Custom attributes for validator errors.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'school_class_id' => 'Kelas',
            'subject_id' => 'Mata Pelajaran',
            'schedule_id' => 'Jadwal Mengajar',
            'date' => 'Hari / Tanggal',
            'meeting_number' => 'Pertemuan Ke-',
            'learning_objective' => 'Tujuan Pembelajaran',
            'teaching_activity' => 'Kegiatan Belajar Mengajar',
            'teaching_problem' => 'Permasalahan KBM',
            'is_shared_with_students' => 'Tampilkan ke Siswa',
        ];
    }
}
