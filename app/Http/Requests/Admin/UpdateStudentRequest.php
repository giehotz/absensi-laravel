<?php

namespace App\Http\Requests\Admin;

use App\Models\Student;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateStudentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $cleanNullable = static function (mixed $value): ?string {
            $trimmed = trim((string) $value);

            return ($trimmed === '' || $trimmed === '-') ? null : $trimmed;
        };

        $nullableFields = [
            'nisn',
            'nik',
            'phone',
            'birth_place',
            'address',
            'religion',
            'family_status',
            'child_number',
            'previous_school',
            'entry_grade',
            'father_name',
            'mother_name',
            'father_job',
            'mother_job',
            'parent_address',
            'guardian_name',
            'guardian_job',
            'guardian_address',
            'audit_note',
        ];

        $merged = [
            'name' => trim((string) $this->input('name')),
            'nis' => trim((string) $this->input('nis')),
            'email' => ($email = $cleanNullable($this->input('email'))) ? strtolower($email) : null,
        ];

        foreach ($nullableFields as $field) {
            $merged[$field] = $cleanNullable($this->input($field));
        }

        $this->merge($merged);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        /** @var Student|null $student */
        $student = $this->route('student');

        return [
            'name' => ['required', 'string', 'max:255'],
            'nis' => ['required', 'string', 'max:50', Rule::unique('students', 'nis')->ignore($student?->id)],
            'nisn' => ['nullable', 'string', 'max:50', Rule::unique('students', 'nisn')->ignore($student?->id)],
            'nik' => ['nullable', 'string', 'max:30', Rule::unique('students', 'nik')->ignore($student?->id)],
            'school_class_id' => ['required', 'exists:school_classes,id'],
            'gender' => ['required', 'in:L,P'],
            'birth_place' => ['nullable', 'string', 'max:100'],
            'birth_date' => ['nullable', 'date', 'before_or_equal:today'],
            'phone' => ['nullable', 'string', 'max:20'],
            'email' => ['nullable', 'email', 'max:255', Rule::unique('users', 'email')->ignore($student?->user_id)],
            'password' => ['nullable', 'string', 'min:6'],
            'is_active' => ['required', 'boolean'],
            'photo' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:2048'],
            'remove_photo' => ['nullable', 'boolean'],
            'address' => ['nullable', 'string'],
            'religion' => ['nullable', 'string', 'max:50'],
            'family_status' => ['nullable', 'string', 'max:50'],
            'child_number' => ['nullable', 'string', 'max:10'],
            'previous_school' => ['nullable', 'string', 'max:150'],
            'admission_date' => ['nullable', 'date'],
            'entry_grade' => ['nullable', 'string', 'max:20'],
            'father_name' => ['nullable', 'string', 'max:150'],
            'mother_name' => ['nullable', 'string', 'max:150'],
            'father_job' => ['nullable', 'string', 'max:100'],
            'mother_job' => ['nullable', 'string', 'max:100'],
            'parent_address' => ['nullable', 'string'],
            'guardian_name' => ['nullable', 'string', 'max:150'],
            'guardian_job' => ['nullable', 'string', 'max:100'],
            'guardian_address' => ['nullable', 'string'],
            'audit_status' => ['nullable', 'string', 'in:pending,verified,flagged'],
            'audit_note' => ['nullable', 'string', 'max:255'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Nama siswa wajib diisi.',
            'nis.required' => 'NIS wajib diisi.',
            'nis.unique' => 'NIS sudah digunakan oleh siswa lain.',
            'nisn.unique' => 'NISN sudah digunakan oleh siswa lain.',
            'nik.unique' => 'NIK sudah digunakan oleh siswa lain.',
            'school_class_id.required' => 'Pilih kelas siswa.',
            'school_class_id.exists' => 'Kelas yang dipilih tidak valid.',
            'gender.required' => 'Pilih jenis kelamin.',
            'gender.in' => 'Pilihan jenis kelamin tidak valid.',
            'birth_date.before_or_equal' => 'Tanggal lahir tidak boleh di masa depan.',
            'email.unique' => 'Alamat email login sudah digunakan.',
            'photo.image' => 'File foto harus berupa gambar.',
            'photo.mimes' => 'Format foto harus jpeg, png, jpg, atau webp.',
            'photo.max' => 'Ukuran file foto maksimal 2MB.',
        ];
    }
}
