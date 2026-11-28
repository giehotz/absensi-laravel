<x-print-preview-layout title="Lembar Coaching Alur TIRTA" paperSize="A4" orientation="portrait">
    <x-kop-surat :useBase64="true" />

    <div style="text-align: center; margin-bottom: 12px;">
        <h1 style="font-size: 13pt; font-weight: bold; text-transform: uppercase; margin: 0;">Lembar Catatan Coaching Alur TIRTA</h1>
    </div>

    <table style="width: 100%; font-size: 9.5pt; margin-bottom: 12px;">
        <tr><td style="width: 24%; font-weight: bold;">Nama Coachee (Guru)</td><td>: {{ $jadwal->teacher?->user?->name }}</td><td style="width: 22%; font-weight: bold;">Tanggal</td><td>: {{ $jadwal->tanggal?->format('d/m/Y') }}</td></tr>
        <tr><td style="font-weight: bold;">Coach (Supervisor)</td><td>: {{ $jadwal->supervisor_display_name }}</td><td style="font-weight: bold;">Kelas / Mapel</td><td>: {{ $jadwal->schoolClass?->name ?? '-' }} / {{ $jadwal->subject?->name ?? '-' }}</td></tr>
    </table>

    @php
        $tirtaTemplates = $tirtaTemplates ?? \App\Models\Supervisi\SupervisiCoachingTemplate::forAllStages();
    @endphp
    @forelse($jadwal->coachingTirtas as $log)
        @php
            $tplLog = $tirtaTemplates->get($log->tahap);
        @endphp
        <div style="margin-bottom: 12px;">
            <div style="font-weight: bold; font-size: 10pt; background: #e2e8f0; padding: 4px 8px; border: 1px solid #000;">
                Tahap: {{ strtoupper(str_replace('_', ' ', $log->tahap)) }}
            </div>
            <table style="width: 100%; border-collapse: collapse; font-size: 9pt;">
                <tr>
                    <td style="border: 1px solid #000; padding: 5px; width: 34%; vertical-align: top;">
                        <div style="font-weight: bold;">T — Tujuan</div>
                        @if($tplLog?->pertanyaan_tujuan)<div style="font-size: 7.5pt; color: #475569; font-style: italic; margin-top: 2px;">{{ $tplLog->pertanyaan_tujuan }}</div>@endif
                    </td>
                    <td style="border: 1px solid #000; padding: 5px; vertical-align: top;">{{ $log->tujuan_coaching ?: '-' }}</td>
                </tr>
                <tr>
                    <td style="border: 1px solid #000; padding: 5px; vertical-align: top;">
                        <div style="font-weight: bold;">I — Identifikasi</div>
                        @if($tplLog?->pertanyaan_identifikasi)<div style="font-size: 7.5pt; color: #475569; font-style: italic; margin-top: 2px;">{{ $tplLog->pertanyaan_identifikasi }}</div>@endif
                    </td>
                    <td style="border: 1px solid #000; padding: 5px; vertical-align: top;">{{ $log->identifikasi_kondisi ?: '-' }}</td>
                </tr>
                <tr>
                    <td style="border: 1px solid #000; padding: 5px; vertical-align: top;">
                        <div style="font-weight: bold;">R — Rencana Aksi</div>
                        @if($tplLog?->pertanyaan_rencana)<div style="font-size: 7.5pt; color: #475569; font-style: italic; margin-top: 2px;">{{ $tplLog->pertanyaan_rencana }}</div>@endif
                    </td>
                    <td style="border: 1px solid #000; padding: 5px; vertical-align: top;">{{ $log->rencana_aksi ?: '-' }}</td>
                </tr>
                <tr>
                    <td style="border: 1px solid #000; padding: 5px; vertical-align: top;">
                        <div style="font-weight: bold;">TA — Tanggung Jawab</div>
                        @if($tplLog?->pertanyaan_tanggung_jawab)<div style="font-size: 7.5pt; color: #475569; font-style: italic; margin-top: 2px;">{{ $tplLog->pertanyaan_tanggung_jawab }}</div>@endif
                    </td>
                    <td style="border: 1px solid #000; padding: 5px; vertical-align: top;">{{ $log->tanggung_jawab ?: '-' }}</td>
                </tr>
                <tr><td style="border: 1px solid #000; padding: 5px; font-weight: bold;">Catatan Penguatan Coach</td><td style="border: 1px solid #000; padding: 5px;">{{ $log->catatan_coach ?: '-' }}</td></tr>
                <tr><td style="border: 1px solid #000; padding: 5px; font-weight: bold;">Komitmen &amp; Refleksi Coachee (Guru)</td><td style="border: 1px solid #000; padding: 5px;">{{ $log->catatan_guru ?: '-' }}</td></tr>
                <tr><td style="border: 1px solid #000; padding: 5px; font-weight: bold;">Status Coaching</td><td style="border: 1px solid #000; padding: 5px;">{{ strtoupper((string) ($log->status ?? 'draft')) }}@if($log->guru_confirmed_at) (Dikonfirmasi Guru: {{ $log->guru_confirmed_at->format('d/m/Y H:i') }})@endif</td></tr>
            </table>
        </div>
    @empty
        <p style="font-size: 9.5pt;">Belum ada catatan coaching TIRTA pada jadwal ini.</p>
    @endforelse

    @include('supervisi.pdf.partials.signature_blocks')
</x-print-preview-layout>
