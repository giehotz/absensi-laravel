<?php

namespace App\Http\Requests\Supervisi;

use App\Models\Supervisi\SupervisiAspekPenilaian;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class SavePraObservasiRequest extends FormRequest
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
        $isSubmit = $this->input('aksi') === 'ajukan';

        return [
            'aksi' => ['required', 'in:draft,ajukan'],
            'indikator_pmm_id' => [$isSubmit ? 'required' : 'nullable', 'exists:supervisi_jenis_penilaian,id'],
            'fokus_perilaku_dipilih' => [$isSubmit ? 'required' : 'nullable', 'array', $isSubmit ? 'min:1' : 'min:0', 'max:3'],
            'fokus_perilaku_dipilih.*' => ['integer', 'exists:supervisi_aspek_penilaian,id'],
            'upaya_mempelajari' => [$isSubmit ? 'required' : 'nullable', 'string'],
            'tujuan_pembelajaran_sesi' => [$isSubmit ? 'required' : 'nullable', 'string'],
            'area_pengembangan' => ['nullable', 'string'],
            'strategi_persiapan' => ['nullable', 'string'],
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator): void {
                $indikatorId = (int) $this->input('indikator_pmm_id');
                $fokusIds = array_map('intval', (array) $this->input('fokus_perilaku_dipilih', []));

                if ($indikatorId > 0 && ! empty($fokusIds)) {
                    $validCount = SupervisiAspekPenilaian::query()
                        ->where('jenis_penilaian_id', $indikatorId)
                        ->whereIn('id', $fokusIds)
                        ->count();

                    if ($validCount !== count(array_unique($fokusIds))) {
                        $validator->errors()->add('fokus_perilaku_dipilih', 'Fokus perilaku yang dipilih harus sesuai dengan indikator kinerja PMM yang dipilih.');
                    }
                }
            },
        ];
    }
}
