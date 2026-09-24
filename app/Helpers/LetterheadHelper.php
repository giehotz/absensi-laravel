<?php

use App\Services\LetterheadService;

if (! function_exists('kop_surat')) {
    /**
     * Helper cepat untuk mengakses data, service, atau render HTML Kop Surat.
     *
     * Contoh penggunaan:
     * - kop_surat()              => mengembalikan array seluruh data kop surat
     * - kop_surat('html')        => me-render HTML kop surat
     * - kop_surat('pdf_html')    => me-render HTML kop surat dengan gambar base64 untuk PDF
     * - kop_surat('school_name') => mengambil nama sekolah/madrasah di kop surat
     * - kop_surat('service')     => instance LetterheadService
     */
    function kop_surat(?string $key = null, array $overrides = []): mixed
    {
        /** @var LetterheadService $service */
        $service = app(LetterheadService::class);

        if ($key === 'service') {
            return $service;
        }

        if ($key === 'html') {
            return $service->renderHtml($overrides, false);
        }

        if ($key === 'pdf_html') {
            return $service->renderHtml($overrides, true);
        }

        $data = $service->getData($overrides);

        if ($key === null) {
            return $data;
        }

        return $data[$key] ?? null;
    }
}
