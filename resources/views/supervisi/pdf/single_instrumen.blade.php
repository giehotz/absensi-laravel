<x-print-preview-layout :title="'Form Penilaian: ' . $jenis->nama" paperSize="A4" orientation="portrait">
    <x-kop-surat :useBase64="true" />

    <div style="text-align: center; margin-bottom: 12px;">
        <h1 style="font-size: 12.5pt; font-weight: bold; text-transform: uppercase; margin: 0;">Instrumen Penilaian Supervisi ({{ $jenis->kode }})</h1>
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
            <td style="font-weight: bold; padding: 2px 0;">Skor Komponen</td>
            <td style="padding: 2px 0;">: <strong>{{ $instrumentSkor }} / {{ $instrumentMaks }} ({{ number_format((float) $instrumentNilai, 2) }}% · {{ $instrumentPredikat }})</strong></td>
        </tr>
    </table>

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

    @include('supervisi.pdf.partials.signature_blocks')
</x-print-preview-layout>
