@if(!empty($isDraft) && $isDraft)
    <div class="pointer-events-none fixed inset-0 flex items-center justify-center opacity-10 select-none z-0">
        <div class="border-4 border-dashed border-rose-700 text-rose-700 font-black text-3xl px-6 py-3 -rotate-12 uppercase tracking-widest">
            DRAFT / BELUM DISAHKAN
        </div>
    </div>
@endif

@php
    $tanggalDokumen = ($signatures['supervisor']?->tanggal_ttd
        ?? $signatures['kepala']?->tanggal_ttd
        ?? $jadwal->tanggal
        ?? now())->locale('id')->isoFormat('D MMMM Y');
@endphp

<table style="width: 100%; border-collapse: collapse; margin-top: 24px; font-size: 10pt; position: relative; z-index: 1; page-break-inside: avoid;">
    <tr>
        <td style="width: 50%; text-align: center; vertical-align: bottom; padding: 6px 6px 0;">
            <div>Guru yang Disupervisi,</div>
        </td>
        <td style="width: 50%; text-align: center; vertical-align: bottom; padding: 6px 6px 0;">
            <div>{{ $kotaMadrasah ?? 'Madrasah' }}, {{ $tanggalDokumen }}</div>
            <div>Supervisor Penilai,</div>
        </td>
    </tr>
    <tr>
        <td style="width: 50%; text-align: center; vertical-align: top; padding: 0 6px 14px;">
            @include('supervisi.pdf.partials.single_signature', [
                'sig' => $signatures['guru'] ?? null,
                'qr' => $qrDataUris['guru'] ?? null,
                'nama' => $jadwal->teacher?->user?->name ?? 'Guru',
                'nip' => $jadwal->teacher?->nip ?? '-',
                'isPolos' => $isPolos ?? false,
            ])
        </td>
        <td style="width: 50%; text-align: center; vertical-align: top; padding: 0 6px 14px;">
            @include('supervisi.pdf.partials.single_signature', [
                'sig' => $signatures['supervisor'] ?? null,
                'qr' => $qrDataUris['supervisor'] ?? null,
                'nama' => $jadwal->supervisor_display_name,
                'nip' => $jadwal->supervisor_nip ?? '-',
                'isPolos' => $isPolos ?? false,
            ])
        </td>
    </tr>
    <tr>
        <td colspan="2" style="text-align: center; vertical-align: top; padding: 8px 6px 6px;">
            <div>Kepala Madrasah,</div>
            <div style="max-width: 280px; margin: 0 auto;">
                @include('supervisi.pdf.partials.single_signature', [
                    'sig' => $signatures['kepala'] ?? null,
                    'qr' => $qrDataUris['kepala'] ?? null,
                    'nama' => $namaKepala ?? 'Kepala Madrasah',
                    'nip' => $nipKepala ?? '-',
                    'isPolos' => $isPolos ?? false,
                ])
            </div>
        </td>
    </tr>
</table>
