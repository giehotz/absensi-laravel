<x-print-preview-layout title="Telaah Perangkat Ajar" paperSize="A4" orientation="portrait">
    <x-kop-surat :useBase64="true" />

    <div style="text-align: center; margin-bottom: 12px;">
        <h1 style="font-size: 13pt; font-weight: bold; text-transform: uppercase; margin: 0;">Lembar Telaah Perangkat Ajar Guru</h1>
    </div>

    <table style="width: 100%; font-size: 9.5pt; margin-bottom: 12px;">
        <tr><td style="width: 24%; font-weight: bold;">Nama Guru</td><td>: {{ $jadwal->teacher?->user?->name }}</td><td style="width: 22%; font-weight: bold;">Tanggal</td><td>: {{ $jadwal->tanggal?->format('d/m/Y') }}</td></tr>
        <tr><td style="font-weight: bold;">Supervisor</td><td>: {{ $jadwal->supervisor_display_name }}</td><td style="font-weight: bold;">Kelas / Mapel</td><td>: {{ $jadwal->schoolClass?->name ?? '-' }} / {{ $jadwal->subject?->name ?? '-' }}</td></tr>
    </table>

    <table style="width: 100%; border-collapse: collapse; font-size: 9pt; margin-bottom: 12px;">
        <thead>
            <tr style="background: #e2e8f0;">
                <th style="border: 1px solid #000; padding: 4px; width: 30px;">No</th>
                <th style="border: 1px solid #000; padding: 4px; text-align: left;">Nama Dokumen</th>
                <th style="border: 1px solid #000; padding: 4px; text-align: left;">Kategori / Fase</th>
                <th style="border: 1px solid #000; padding: 4px; text-align: left;">Status</th>
                <th style="border: 1px solid #000; padding: 4px; text-align: left;">Catatan Verifikasi</th>
            </tr>
        </thead>
        <tbody>
            @forelse($jadwal->dokumenAjars as $idx => $dok)
                <tr>
                    <td style="border: 1px solid #000; padding: 4px; text-align: center;">{{ $idx + 1 }}</td>
                    <td style="border: 1px solid #000; padding: 4px;">{{ $dok->nama_dokumen }}</td>
                    <td style="border: 1px solid #000; padding: 4px;">{{ $dok->kategori_dokumen }} @if($dok->fase) (Fase {{ $dok->fase }}) @endif</td>
                    <td style="border: 1px solid #000; padding: 4px;">{{ strtoupper($dok->status) }}</td>
                    <td style="border: 1px solid #000; padding: 4px;">{{ $dok->feedback ?: '-' }}</td>
                </tr>
            @empty
                <tr><td colspan="5" style="border: 1px solid #000; padding: 6px; text-align: center;">Belum ada perangkat ajar diunggah.</td></tr>
            @endforelse
        </tbody>
    </table>

    @include('supervisi.pdf.partials.signature_blocks')
</x-print-preview-layout>
