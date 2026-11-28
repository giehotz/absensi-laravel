@php
    $isKma6334 = $jenis->kategori_supervisi === \App\Models\Supervisi\SupervisiJenisPenilaian::KATEGORI_KMA_6334;
@endphp
<x-print-preview-layout :title="'Form Penilaian: ' . $jenis->nama" paperSize="A4" orientation="portrait">
    <x-kop-surat :useBase64="true" />

    <div style="text-align: center; margin-bottom: 12px;">
        <h1 style="font-size: 12.5pt; font-weight: bold; text-transform: uppercase; margin: 0;">
            {{ $isKma6334 ? 'Instrumen Supervisi Pembelajaran MI (SK Dirjen Pendis No. 6334/2021)' : 'Instrumen Penilaian Supervisi (' . $jenis->kode . ')' }}
        </h1>
        <h2 style="font-size: 10.5pt; font-weight: bold; margin: 3px 0 0 0; color: #1e293b;">{{ $jenis->nama }}</h2>
        <p style="font-size: 9pt; margin: 2px 0 0 0; color: #475569;">{{ $jadwal->academicYear?->name }} — Semester {{ ucfirst($jadwal->academicYear?->semester ?? '') }}</p>
    </div>

    <table style="width: 100%; font-size: 9.2pt; margin-bottom: 12px; border-collapse: collapse;">
        <tr>
            <td style="width: 22%; font-weight: bold; padding: 2px 0;">Nama Guru</td>
            <td style="width: 30%; padding: 2px 0;">: {{ $jadwal->teacher?->user?->name }}</td>
            <td style="width: 22%; font-weight: bold; padding: 2px 0;">Kelas / Mapel</td>
            <td style="padding: 2px 0;">: {{ $jadwal->schoolClass?->name ?? '-' }} / {{ $jadwal->subject?->name ?? '-' }}</td>
        </tr>
        <tr>
            <td style="font-weight: bold; padding: 2px 0;">NIP</td>
            <td style="padding: 2px 0;">: {{ $jadwal->teacher?->nip ?: '-' }}</td>
            <td style="font-weight: bold; padding: 2px 0;">Tanggal Observasi</td>
            <td style="padding: 2px 0;">: {{ $jadwal->tanggal?->format('d/m/Y') }}</td>
        </tr>
        <tr>
            <td style="font-weight: bold; padding: 2px 0;">Supervisor Penilai</td>
            <td style="padding: 2px 0;">: {{ $jadwal->supervisor_display_name }}</td>
            <td style="font-weight: bold; padding: 2px 0;">Skor / Predikat</td>
            <td style="padding: 2px 0;">: <strong>{{ $instrumentSkor }} / {{ $instrumentMaks }} ({{ number_format((float) $instrumentNilai, 2) }}% · {{ $instrumentPredikat }})</strong></td>
        </tr>
        @if($isKma6334)
            <tr>
                <td style="font-weight: bold; padding: 2px 0;">Pendekatan Supervisi</td>
                <td style="padding: 2px 0;">: {{ \App\Models\Supervisi\SupervisiJadwal::PENDEKATAN_OPTIONS[$jadwal->pendekatan ?? 'kolaboratif'] ?? ucfirst((string) ($jadwal->pendekatan ?? 'Kolaboratif')) }}</td>
                <td style="font-weight: bold; padding: 2px 0;">Teknik Supervisi</td>
                <td style="padding: 2px 0;">: {{ \App\Models\Supervisi\SupervisiJadwal::TEKNIK_OPTIONS[$jadwal->teknik_supervisi ?? 'kunjungan_kelas'] ?? ucfirst(str_replace('_', ' ', (string) ($jadwal->teknik_supervisi ?? 'Kunjungan Kelas'))) }}</td>
            </tr>
        @endif
    </table>

    @if($isKma6334)
        <table style="width: 100%; border-collapse: collapse; font-size: 8.8pt; margin-bottom: 10px;">
            <thead>
                <tr style="background: #EAEAEA;">
                    <th style="border: 1px solid #000; padding: 5px; width: 28px; text-align: center;">No</th>
                    <th style="border: 1px solid #000; padding: 5px; text-align: left; width: 34%;">Aspek yang Diamati</th>
                    <th style="border: 1px solid #000; padding: 5px; text-align: left; width: 33%;">Hasil Pengamatan (Skor)</th>
                    <th style="border: 1px solid #000; padding: 5px; text-align: left;">Rekomendasi</th>
                </tr>
            </thead>
            <tbody>
                @foreach($jenis->aspekPenilaians as $aspek)
                    @php
                        $detail = $detailsByAspek->get($aspek->id);
                        $max = $aspek->maxSkor();
                    @endphp
                    <tr>
                        <td style="border: 1px solid #000; padding: 5px; text-align: center; vertical-align: top;">{{ $aspek->urutan }}</td>
                        <td style="border: 1px solid #000; padding: 5px; vertical-align: top;">
                            <div style="font-weight: bold;">{{ $aspek->nama_aspek }}</div>
                            @if($aspek->fokus_perilaku)
                                <div style="font-size: 7.8pt; color: #334155; margin-top: 2px;">{{ $aspek->fokus_perilaku }}</div>
                            @endif
                        </td>
                        <td style="border: 1px solid #000; padding: 5px; vertical-align: top;">
                            <div><strong>Skor:</strong> {{ $detail ? $detail->skor . ' / ' . $max : '-' }}</div>
                            <div style="margin-top: 2px;">{{ $detail?->catatan ?: '-' }}</div>
                        </td>
                        <td style="border: 1px solid #000; padding: 5px; vertical-align: top;">{{ $detail?->rekomendasi ?: '-' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div style="border: 1px solid #000; background: #f8fafc; padding: 6px 8px; font-size: 8.8pt; margin-bottom: 10px;">
            <strong>Tindak Lanjut &amp; Kesimpulan Supervisi:</strong><br>
            {{ $hasil?->rekomendasi ?: 'Teruskan praktik baik yang sudah berjalan dan laksanakan rekomendasi perbaikan pada pertemuan berikutnya.' }}
        </div>
    @else
        <table style="width: 100%; border-collapse: collapse; font-size: 8.8pt; margin-bottom: 10px;">
            <thead>
                <tr style="background: #EAEAEA;">
                    <th style="border: 1px solid #000; padding: 5px; width: 28px; text-align: center;">No</th>
                    <th style="border: 1px solid #000; padding: 5px; text-align: left; width: 42%;">Aspek &amp; Keterangan Indikator</th>
                    <th style="border: 1px solid #000; padding: 5px; width: 48px; text-align: center;">Skor</th>
                    <th style="border: 1px solid #000; padding: 5px; width: 45px; text-align: center;">Maks</th>
                    <th style="border: 1px solid #000; padding: 5px; text-align: left;">Catatan Penilai</th>
                </tr>
            </thead>
            <tbody>
                @foreach($jenis->aspekPenilaians as $aspek)
                    @php
                        $detail = $detailsByAspek->get($aspek->id);
                        $max = $aspek->maxSkor();
                    @endphp
                    <tr>
                        <td style="border: 1px solid #000; padding: 5px; text-align: center; vertical-align: top;">{{ $aspek->urutan }}</td>
                        <td style="border: 1px solid #000; padding: 5px; vertical-align: top;">
                            <div style="font-weight: bold;">{{ $aspek->nama_aspek }}</div>
                            @if($aspek->fokus_perilaku)
                                <div style="font-size: 8pt; color: #334155; margin-top: 2px;">{{ $aspek->fokus_perilaku }}</div>
                            @endif
                        </td>
                        <td style="border: 1px solid #000; padding: 5px; text-align: center; font-weight: bold; vertical-align: top;">
                            {{ $detail ? $detail->skor : '-' }}
                        </td>
                        <td style="border: 1px solid #000; padding: 5px; text-align: center; vertical-align: top;">{{ $max }}</td>
                        <td style="border: 1px solid #000; padding: 5px; vertical-align: top;">{{ $detail?->catatan ?: '-' }}</td>
                    </tr>
                @endforeach
                <tr style="background: #f1f5f9; font-weight: bold;">
                    <td colspan="2" style="border: 1px solid #000; padding: 5px; text-align: right;">TOTAL KOMPONEN ({{ number_format((float) $instrumentNilai, 2) }}% — {{ $instrumentPredikat }})</td>
                    <td style="border: 1px solid #000; padding: 5px; text-align: center;">{{ $instrumentSkor }}</td>
                    <td style="border: 1px solid #000; padding: 5px; text-align: center;">{{ $instrumentMaks }}</td>
                    <td style="border: 1px solid #000; padding: 5px;"></td>
                </tr>
            </tbody>
        </table>

        @if($hasil?->rekomendasi)
            <div style="border: 1px solid #000; background: #f8fafc; padding: 6px 8px; font-size: 8.8pt; margin-bottom: 10px;">
                <strong>Rekomendasi Supervisor:</strong> {{ $hasil->rekomendasi }}
            </div>
        @endif
    @endif

    @include('supervisi.pdf.partials.signature_blocks')
</x-print-preview-layout>
