<?php

namespace App\Http\Requests\Ujian;

use App\Models\Ujian\UjianPeriodeKelompok;
use App\Services\Ujian\UjianKalenderService;
use Carbon\Carbon;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;

class PreviewPemetaanHariRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'kecualikan_minggu' => $this->boolean('kecualikan_minggu', true),
        ]);

        if ($this->has('tanggal_libur') && is_string($this->input('tanggal_libur'))) {
            $raw = (string) $this->input('tanggal_libur');
            $lines = preg_split('/[\r\n,]+/', $raw, -1, PREG_SPLIT_NO_EMPTY);
            $parsed = [];
            foreach ($lines as $line) {
                $trimmed = trim($line);
                if ($trimmed !== '') {
                    try {
                        $parsed[] = Carbon::parse($trimmed)->format('Y-m-d');
                    } catch (\Throwable) {
                        $parsed[] = $trimmed;
                    }
                }
            }
            $this->merge([
                'tanggal_libur' => array_values(array_unique($parsed)),
            ]);
        }
    }

    public function rules(): array
    {
        return [
            'ujian_periode_id' => ['required', 'integer', 'exists:ujian_periode,id'],
            'kelompok' => ['required', 'in:bawah,atas'],
            'tanggal_mulai' => ['required', 'date'],
            'tanggal_selesai' => ['required', 'date', 'after_or_equal:tanggal_mulai'],
            'kecualikan_minggu' => ['boolean'],
            'tanggal_libur' => ['nullable', 'array'],
            'tanggal_libur.*' => ['date_format:Y-m-d'],
            'jumlah_sesi_per_hari' => ['required', 'integer', 'min:1', 'max:4'],
            'sesi_hari_terakhir' => ['required', 'integer', 'min:1', 'lte:jumlah_sesi_per_hari'],
            'jam_sesi' => ['nullable', 'array'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function ($validator) {
            if (! $validator->errors()->has('tanggal_mulai') && ! $validator->errors()->has('tanggal_selesai')) {
                $tempConfig = new UjianPeriodeKelompok([
                    'tanggal_mulai' => $this->input('tanggal_mulai'),
                    'tanggal_selesai' => $this->input('tanggal_selesai'),
                    'kecualikan_minggu' => $this->boolean('kecualikan_minggu', true),
                    'tanggal_libur' => $this->input('tanggal_libur') ?? [],
                ]);

                $service = app(UjianKalenderService::class);
                $effective = $service->getEffectiveDates($tempConfig);
                if ($effective->isEmpty()) {
                    $validator->errors()->add('tanggal_mulai', 'Tidak ada hari efektif setelah mengecualikan Minggu dan tanggal libur.');
                }
            }
        });
    }
}
