<?php

namespace App\Http\Requests\Supervisi;

use App\Models\Supervisi\SupervisiCoachingTirta;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SaveCoachingTirtaRequest extends FormRequest
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
            'tahap' => ['required', Rule::in([SupervisiCoachingTirta::TAHAP_PRA, SupervisiCoachingTirta::TAHAP_PASCA])],
            'tujuan_coaching' => ['required', 'string'],
            'identifikasi_kondisi' => ['nullable', 'string'],
            'rencana_aksi' => ['nullable', 'string'],
            'tanggung_jawab' => ['nullable', 'string'],
            'catatan_coach' => ['nullable', 'string'],
            'aksi' => ['nullable', Rule::in([SupervisiCoachingTirta::STATUS_DRAFT, SupervisiCoachingTirta::STATUS_SUBMITTED])],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'tahap.required' => 'Tahap Coaching TIRTA wajib dipilih.',
            'tahap.in' => 'Tahap Coaching TIRTA tidak valid.',
            'tujuan_coaching.required' => 'Kolom T — Tujuan Coaching wajib diisi.',
        ];
    }
}
