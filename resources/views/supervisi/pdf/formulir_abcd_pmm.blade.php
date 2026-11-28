<x-print-preview-layout title="Formulir A-B-C-D Supervisi PMM" paperSize="A4" orientation="portrait">
    <x-kop-surat :useBase64="true" />

    <div style="text-align: center; margin-bottom: 12px;">
        <h1 style="font-size: 12.5pt; font-weight: bold; text-transform: uppercase; margin: 0;">Instrumen Siklus Supervisi Kurikulum Merdeka (Form A–D)</h1>
        <p style="font-size: 9.5pt; margin: 2px 0 0 0;">Indikator PMM: {{ $jadwal->indikatorPmm?->nama ?? '-' }}</p>
    </div>

    <table style="width: 100%; font-size: 9.5pt; margin-bottom: 10px;">
        <tr><td style="width: 24%; font-weight: bold;">Nama Guru</td><td>: {{ $jadwal->teacher?->user?->name }}</td><td style="width: 22%; font-weight: bold;">Tanggal</td><td>: {{ $jadwal->tanggal?->format('d/m/Y') }}</td></tr>
        <tr><td style="font-weight: bold;">Supervisor</td><td>: {{ $jadwal->supervisor_display_name }}</td><td style="font-weight: bold;">Kelas / Mapel</td><td>: {{ $jadwal->schoolClass?->name ?? '-' }} / {{ $jadwal->subject?->name ?? '-' }}</td></tr>
    </table>

    <h2 style="font-size: 10.5pt; font-weight: bold; background: #e2e8f0; padding: 4px 8px; border: 1px solid #000; margin: 8px 0 4px;">A. Formulir A — Pra-Observasi</h2>
    <table style="width: 100%; border-collapse: collapse; font-size: 9pt; margin-bottom: 10px;">
        <tr><td style="border: 1px solid #000; padding: 4px; width: 32%; font-weight: bold;">Fokus Perilaku Dipilih</td><td style="border: 1px solid #000; padding: 4px;">{{ $fokusDipilihList->pluck('nama_aspek')->implode(', ') ?: '-' }}</td></tr>
        <tr><td style="border: 1px solid #000; padding: 4px; font-weight: bold;">Tujuan Pembelajaran Sesi</td><td style="border: 1px solid #000; padding: 4px;">{{ $jadwal->praObservasi?->tujuan_pembelajaran_sesi ?: '-' }}</td></tr>
        <tr><td style="border: 1px solid #000; padding: 4px; font-weight: bold;">Upaya Mempelajari</td><td style="border: 1px solid #000; padding: 4px;">{{ $jadwal->praObservasi?->upaya_mempelajari ?: '-' }}</td></tr>
        <tr><td style="border: 1px solid #000; padding: 4px; font-weight: bold;">Catatan Supervisor</td><td style="border: 1px solid #000; padding: 4px;">{{ $jadwal->praObservasi?->catatan_supervisor_pra ?: '-' }}</td></tr>
    </table>

    <h2 style="font-size: 10.5pt; font-weight: bold; background: #e2e8f0; padding: 4px 8px; border: 1px solid #000; margin: 8px 0 4px;">B. Formulir B — Hasil Observasi ({{ number_format((float) $summary['nilai_akhir'], 2) }}% · {{ $summary['ketercapaian'] }})</h2>
    @foreach($jadwal->hasils as $hasil)
        <table style="width: 100%; border-collapse: collapse; font-size: 9pt; margin-bottom: 10px;">
            @foreach($hasil->detailHasils as $idx => $d)
                <tr>
                    <td style="border: 1px solid #000; padding: 4px; width: 28px;">{{ $idx + 1 }}</td>
                    <td style="border: 1px solid #000; padding: 4px;">{{ $d->aspek?->nama_aspek }}</td>
                    <td style="border: 1px solid #000; padding: 4px; width: 55px; text-align: center; font-weight: bold;">{{ $d->skor }}</td>
                    <td style="border: 1px solid #000; padding: 4px;">{{ $d->catatan ?: '-' }}</td>
                </tr>
            @endforeach
        </table>
    @endforeach

    <h2 style="font-size: 10.5pt; font-weight: bold; background: #e2e8f0; padding: 4px 8px; border: 1px solid #000; margin: 8px 0 4px;">C &amp; D. Tindak Lanjut &amp; Refleksi</h2>
    <table style="width: 100%; border-collapse: collapse; font-size: 9pt;">
        <tr><td style="border: 1px solid #000; padding: 4px; width: 32%; font-weight: bold;">Refleksi Guru</td><td style="border: 1px solid #000; padding: 4px;">{{ $jadwal->tindakLanjut?->refleksi_guru ?: '-' }}</td></tr>
        <tr><td style="border: 1px solid #000; padding: 4px; font-weight: bold;">Tujuan &amp; Upaya Tindak Lanjut</td><td style="border: 1px solid #000; padding: 4px;">{{ $jadwal->tindakLanjut?->tujuan_tindak_lanjut ?: '-' }} — {{ $jadwal->tindakLanjut?->upaya_tindak_lanjut ?: '-' }}</td></tr>
        <tr><td style="border: 1px solid #000; padding: 4px; font-weight: bold;">Kebutuhan Dukungan</td><td style="border: 1px solid #000; padding: 4px;">{{ $jadwal->tindakLanjut?->kebutuhan_dukungan ?: '-' }}</td></tr>
        <tr><td style="border: 1px solid #000; padding: 4px; font-weight: bold;">Inspirasi &amp; Perubahan Praktik (Form D)</td><td style="border: 1px solid #000; padding: 4px;">{{ $jadwal->tindakLanjut?->inspirasi_baru ?: '-' }} / {{ $jadwal->tindakLanjut?->perubahan_praktik ?: '-' }}</td></tr>
        <tr><td style="border: 1px solid #000; padding: 4px; font-weight: bold;">Evaluasi Supervisor</td><td style="border: 1px solid #000; padding: 4px;">{{ $jadwal->tindakLanjut?->penilaian_supervisor_d ?: '-' }} ({{ $jadwal->tindakLanjut?->catatan_supervisor_d ?: '-' }})</td></tr>
    </table>

    @include('supervisi.pdf.partials.signature_blocks')
    @include('supervisi.pdf.partials.foto_bukti')
</x-print-preview-layout>
