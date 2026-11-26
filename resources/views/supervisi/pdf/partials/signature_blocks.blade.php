@if(!empty($isDraft) && $isDraft)
    <div class="pointer-events-none fixed inset-0 flex items-center justify-center opacity-10 select-none z-0">
        <div class="border-4 border-dashed border-rose-700 text-rose-700 font-black text-3xl px-6 py-3 -rotate-12 uppercase tracking-widest">
            DRAFT / BELUM DISAHKAN
        </div>
    </div>
@endif

<table style="width: 100%; border-collapse: collapse; margin-top: 24px; font-size: 10pt; position: relative; z-index: 1;">
    <tr>
        <td style="width: 33.3%; text-align: center; vertical-align: top; padding: 6px;">
            <div>Guru yang Disupervisi,</div>
            @include('supervisi.pdf.partials.single_signature', [
                'sig' => $signatures['guru'] ?? null,
                'qr' => $qrDataUris['guru'] ?? null,
                'nama' => $jadwal->teacher?->user?->name ?? 'Guru',
                'nip' => $jadwal->teacher?->nip ?? '-',
                'isPolos' => $isPolos ?? false,
            ])
        </td>
        <td style="width: 33.3%; text-align: center; vertical-align: top; padding: 6px;">
            <div>Supervisor Penilai,</div>
            @include('supervisi.pdf.partials.single_signature', [
                'sig' => $signatures['supervisor'] ?? null,
                'qr' => $qrDataUris['supervisor'] ?? null,
                'nama' => $jadwal->supervisor_display_name,
                'nip' => $jadwal->supervisor_nip ?? '-',
                'isPolos' => $isPolos ?? false,
            ])
        </td>
        <td style="width: 33.3%; text-align: center; vertical-align: top; padding: 6px;">
            <div>{{ $kotaMadrasah ?? 'Madrasah' }}, {{ ($signatures['kepala']?->tanggal_ttd ?? $jadwal->tanggal ?? now())->format('d/m/Y') }}</div>
            <div>Kepala Madrasah,</div>
            @include('supervisi.pdf.partials.single_signature', [
                'sig' => $signatures['kepala'] ?? null,
                'qr' => $qrDataUris['kepala'] ?? null,
                'nama' => $namaKepala ?? 'Kepala Madrasah',
                'nip' => $nipKepala ?? '-',
                'isPolos' => $isPolos ?? false,
            ])
        </td>
    </tr>
</table>
