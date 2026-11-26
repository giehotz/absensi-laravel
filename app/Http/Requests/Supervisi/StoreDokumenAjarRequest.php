<?php

namespace App\Http\Requests\Supervisi;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StoreDokumenAjarRequest extends FormRequest
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
            'nama_dokumen' => ['required', 'string', 'max:255'],
            'kategori_dokumen' => ['required', 'in:modul_ajar,atp,rpp,lkpd,lainnya'],
            'fase' => ['nullable', 'in:A,B,C,D,E,F'],
            'tujuan_pembelajaran' => ['nullable', 'string'],
            'strategi_diferensiasi' => ['nullable', 'array'],
            'strategi_diferensiasi.*' => ['string', 'max:50'],
            'link_drive' => ['nullable', 'url', 'max:500'],
            'link_eksternal' => ['nullable', 'url', 'max:500'],
            'file' => ['nullable', 'file', 'mimes:pdf,doc,docx', 'max:10240'],
            'keterangan' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator): void {
                if (empty($this->input('link_drive')) && ! $this->hasFile('file')) {
                    $validator->errors()->add('link_drive', 'Isi tautan Google Drive atau unggah file PDF/DOCX minimal salah satu.');
                }
            },
        ];
    }
}
