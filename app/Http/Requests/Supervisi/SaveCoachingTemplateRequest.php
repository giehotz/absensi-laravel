<?php

namespace App\Http\Requests\Supervisi;

use App\Models\Supervisi\SupervisiCoachingTirta;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SaveCoachingTemplateRequest extends FormRequest
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
            'pertanyaan_tujuan' => ['required', 'string', 'max:1000'],
            'pertanyaan_identifikasi' => ['required', 'string', 'max:1000'],
            'pertanyaan_rencana' => ['required', 'string', 'max:1000'],
            'pertanyaan_tanggung_jawab' => ['required', 'string', 'max:1000'],
            'template_tujuan' => ['nullable', 'string', 'max:2000'],
            'template_identifikasi' => ['nullable', 'string', 'max:2000'],
            'template_rencana' => ['nullable', 'string', 'max:2000'],
            'template_tanggung_jawab' => ['nullable', 'string', 'max:2000'],
            'template_catatan_coach' => ['nullable', 'string', 'max:2000'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'pertanyaan_tujuan.required' => 'Pertanyaan global T — Tujuan wajib diisi.',
            'pertanyaan_identifikasi.required' => 'Pertanyaan global I — Identifikasi wajib diisi.',
            'pertanyaan_rencana.required' => 'Pertanyaan global R — Rencana Aksi wajib diisi.',
            'pertanyaan_tanggung_jawab.required' => 'Pertanyaan global TA — Tanggung Jawab wajib diisi.',
        ];
    }
}
