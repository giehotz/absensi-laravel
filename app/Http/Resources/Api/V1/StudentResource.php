<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StudentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $schoolClass = $this->schoolClass;
        $academicYear = $schoolClass?->academicYear;

        return [
            'id' => $this->id,
            'nis' => $this->nis,
            'nisn' => $this->nisn,
            'nama' => $this->user?->name ?? 'Tanpa Nama',
            'jenis_kelamin' => $this->gender,
            'tempat_lahir' => $this->birth_place,
            'tanggal_lahir' => $this->birth_date?->format('Y-m-d'),
            'agama' => $this->religion,
            'telepon' => $this->phone,
            'alamat' => $this->address,
            'foto_url' => $this->photo_url,
            'qr_code_identifier' => $this->qr_code_identifier,
            'kelas' => [
                'id' => $schoolClass?->id,
                'nama' => $schoolClass?->name,
                'jenjang' => $schoolClass?->level,
                'tahun_ajaran' => $academicYear ? $academicYear->name.' ('.ucfirst($academicYear->semester).')' : null,
                'academic_year_id' => $academicYear?->id,
                'wali_kelas' => $schoolClass?->homeroomTeacher?->user?->name,
            ],
            'data_orang_tua' => [
                'nama_ayah' => $this->father_name,
                'pekerjaan_ayah' => $this->father_job,
                'nama_ibu' => $this->mother_name,
                'pekerjaan_ibu' => $this->mother_job,
                'alamat_orang_tua' => $this->parent_address,
                'nama_wali' => $this->guardian_name,
                'pekerjaan_wali' => $this->guardian_job,
                'alamat_wali' => $this->guardian_address,
            ],
            'data_akademik_awal' => [
                'status_keluarga' => $this->family_status,
                'anak_ke' => $this->child_number,
                'sekolah_asal' => $this->previous_school,
                'tanggal_diterima' => $this->admission_date?->format('Y-m-d'),
                'tingkat_awal' => $this->entry_grade,
            ],
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
