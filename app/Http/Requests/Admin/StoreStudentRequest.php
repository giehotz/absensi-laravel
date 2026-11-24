<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreStudentRequest extends FormRequest
{
    /**
     * Named error bag agar tidak bertabrakan dengan modal lain di halaman yang sama.
     */
    protected $errorBag = 'create_student';

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

        $this->merge([
            'name' => trim((string) $this->input('name')),
            'nis' => trim((string) $this->input('nis')),
            'nisn' => $cleanNullable($this->input('nisn')),
            'nik' => $cleanNullable($this->input('nik')),
            'email' => ($email = $cleanNullable($this->input('email'))) ? strtolower($email) : null,
            'phone' => $cleanNullable($this->input('phone')),
            'birth_place' => $cleanNullable($this->input('birth_place')),
            'audit_note' => $cleanNullable($this->input('audit_note')),
        ]);
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'nis' => ['required', 'string', 'max:50', 'unique:students,nis'],
            'nisn' => ['nullable', 'string', 'max:50', 'unique:students,nisn'],
            'nik' => ['nullable', 'string', 'max:30', 'unique:students,nik'],
            'school_class_id' => ['required', 'exists:school_classes,id'],
            'gender' => ['required', 'in:L,P'],
            'birth_place' => ['nullable', 'string', 'max:100'],
            'birth_date' => ['nullable', 'date', 'before_or_equal:today'],
            'phone' => ['nullable', 'string', 'max:20'],
            'email' => ['nullable', 'email', 'max:255', 'unique:users,email'],
            'photo' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:2048'],
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
            'audit_note' => ['nullable', 'string', 'max:255'],
            'redirect_to' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Nama siswa wajib diisi.',
            'nis.required' => 'NIS wajib diisi.',
            'nis.unique' => 'NIS sudah digunakan oleh siswa (aktif atau di tab Tidak Aktif). Silakan pulihkan siswa atau hapus permanen terlebih dahulu.',
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
