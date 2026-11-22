<?php

namespace App\Http\Requests\Ujian;

use App\Models\Ujian\UjianPeriodeKelompok;
use App\Services\Ujian\UjianKalenderService;
use Carbon\Carbon;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;

class StorePeriodeKelompokRequest extends FormRequest
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
            'jam_sesi' => ['required', 'array', 'min:1'],
            'jam_sesi.*.sesi' => ['required', 'integer', 'min:1', 'max:4', 'distinct'],
            'jam_sesi.*.mulai' => ['required', 'date_format:H:i'],
            'jam_sesi.*.selesai' => ['required', 'date_format:H:i'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function ($validator) {
            $jamSesi = $this->input('jam_sesi');
            if (is_array($jamSesi)) {
                foreach ($jamSesi as $idx => $item) {
                    if (! empty($item['mulai']) && ! empty($item['selesai'])) {
                        try {
                            $tMulai = Carbon::createFromFormat('H:i', $item['mulai']);
                            $tSelesai = Carbon::createFromFormat('H:i', $item['selesai']);
                            if ($tSelesai->lte($tMulai)) {
                                $sesiLabel = $item['sesi'] ?? ($idx + 1);
                                $validator->errors()->add("jam_sesi.{$idx}.selesai", "Jam selesai sesi {$sesiLabel} harus setelah jam mulai.");
                            }
                        } catch (\Throwable) {
                            // Format error ditangani oleh date_format
                        }
                    }
                }

                $jumlahSesi = (int) $this->input('jumlah_sesi_per_hari');
                if (count($jamSesi) < $jumlahSesi) {
                    $validator->errors()->add('jam_sesi', "Harap lengkapi jam untuk minimal {$jumlahSesi} sesi.");
                }
            }

            // Validasi hari efektif tersisa minimal 1 hari
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

    public function messages(): array
    {
        return [
            'ujian_periode_id.required' => 'Periode ujian wajib dipilih.',
            'kelompok.required' => 'Kelompok kelas wajib ditentukan.',
            'kelompok.in' => 'Kelompok kelas harus bernilai bawah atau atas.',
            'tanggal_mulai.required' => 'Tanggal mulai pelaksanaan ujian wajib diisi.',
            'tanggal_selesai.required' => 'Tanggal selesai pelaksanaan ujian wajib diisi.',
            'tanggal_selesai.after_or_equal' => 'Tanggal selesai tidak boleh sebelum tanggal mulai.',
            'jumlah_sesi_per_hari.required' => 'Jumlah sesi per hari wajib diisi.',
            'sesi_hari_terakhir.lte' => 'Sesi hari terakhir tidak boleh melebihi jumlah sesi per hari.',
            'jam_sesi.required' => 'Jam sesi wajib diatur minimal 1 sesi.',
        ];
    }

    protected function failedValidation(Validator $validator): void
    {
        $bag = $this->input('kelompok') === 'atas' ? 'pemetaan_atas' : 'pemetaan_bawah';

        throw (new ValidationException($validator))
            ->errorBag($bag)
            ->redirectTo($this->getRedirectUrl());
    }
}
