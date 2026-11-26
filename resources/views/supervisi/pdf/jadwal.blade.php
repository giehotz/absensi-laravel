<x-print-preview-layout title="Jadwal Supervisi Guru" paperSize="A4" orientation="landscape" :backUrl="$backUrl">
    <x-kop-surat :useBase64="true" />

    <div style="text-align: center; margin-bottom: 10px;">
        <h1 style="font-size: 13pt; font-weight: bold; text-transform: uppercase; margin: 0;">Jadwal Supervisi Akademik Guru</h1>
    </div>

    <table style="width: 100%; border-collapse: collapse; font-size: 9pt;">
        <thead>
            <tr style="background: #e2e8f0;">
                <th style="border: 1px solid #000; padding: 5px; width: 32px;">No</th>
                <th style="border: 1px solid #000; padding: 5px; text-align: left;">Hari / Tanggal</th>
                <th style="border: 1px solid #000; padding: 5px; text-align: left;">Jam</th>
                <th style="border: 1px solid #000; padding: 5px; text-align: left;">Nama Guru</th>
                <th style="border: 1px solid #000; padding: 5px; text-align: left;">Kelas / Mapel</th>
                <th style="border: 1px solid #000; padding: 5px; text-align: left;">Supervisor</th>
                <th style="border: 1px solid #000; padding: 5px; text-align: left;">Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach($jadwals as $idx => $j)
                <tr>
                    <td style="border: 1px solid #000; padding: 5px; text-align: center;">{{ $idx + 1 }}</td>
                    <td style="border: 1px solid #000; padding: 5px;">{{ $j->hari }}, {{ $j->tanggal?->format('d/m/Y') }}</td>
                    <td style="border: 1px solid #000; padding: 5px;">Ke-{{ $j->jam_ke ?? '-' }} ({{ substr((string) $j->jam_mulai, 0, 5) }}–{{ substr((string) $j->jam_selesai, 0, 5) }})</td>
                    <td style="border: 1px solid #000; padding: 5px; font-weight: bold;">{{ $j->teacher?->user?->name }}</td>
                    <td style="border: 1px solid #000; padding: 5px;">{{ $j->schoolClass?->name ?? '-' }} / {{ $j->subject?->name ?? '-' }}</td>
                    <td style="border: 1px solid #000; padding: 5px;">{{ $j->supervisor_display_name }}</td>
                    <td style="border: 1px solid #000; padding: 5px;">{{ $j->statusEnum()->label() }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</x-print-preview-layout>
