<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verifikasi Dokumen Supervisi</title>
    @vite(['resources/css/app.css'])
</head>
<body class="min-h-screen bg-[#F0F3F8] p-4 sm:p-8 flex items-center justify-center">
    <main class="w-full max-w-2xl bg-white border-4 border-black rounded-2xl p-6 sm:p-8 shadow-[8px_8px_0px_0px_#000] space-y-6">
        <div class="flex items-center justify-between gap-3 border-b-2 border-black pb-4">
            <div>
                <div class="text-[11px] font-black uppercase text-slate-500">{{ $setting?->school_name ?? config('app.name') }}</div>
                <h1 class="text-xl font-black">Verifikasi Dokumen Supervisi Guru</h1>
            </div>
            <span class="px-3 py-1 rounded-full border-2 border-black text-xs font-black {{ $isValid ? 'bg-emerald-200 text-emerald-950' : 'bg-rose-200 text-rose-950' }}">
                {{ $isValid ? 'DOKUMEN VALID' : 'TIDAK VALID' }}
            </span>
        </div>

        @if(!$isValid)
            <div class="p-4 rounded-xl border-2 border-black bg-rose-50 text-sm font-bold text-rose-950">
                Token verifikasi tidak ditemukan atau tanda tangan digital pada dokumen ini telah di-reset oleh Administrator.
            </div>
        @else
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs bg-slate-50 p-4 rounded-xl border-2 border-black">
                <div><span class="text-slate-500 block font-bold">Guru Disupervisi</span><span class="font-black">{{ $jadwal->teacher?->user?->name ?? '-' }}</span></div>
                <div><span class="text-slate-500 block font-bold">Supervisor Penilai</span><span class="font-black">{{ $jadwal->supervisor_display_name }}</span></div>
                <div><span class="text-slate-500 block font-bold">Kelas / Mapel</span><span class="font-black">{{ $jadwal->schoolClass?->name ?? '-' }} / {{ $jadwal->subject?->name ?? '-' }}</span></div>
                <div><span class="text-slate-500 block font-bold">Tanggal & Nilai Akhir</span><span class="font-black">{{ $jadwal->tanggal?->format('d/m/Y') }} · {{ $jadwal->nilai_akhir ?? '-' }} ({{ $jadwal->ketercapaian ?? '-' }})</span></div>
            </div>

            <div class="space-y-2">
                <h2 class="text-xs font-black uppercase text-slate-600">Riwayat Tanda Tangan Elektronik</h2>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                    @foreach(['guru' => 'Guru', 'supervisor' => 'Supervisor', 'kepala' => 'Kepala Madrasah'] as $rKey => $rLabel)
                        @php $sig = $allSignatures[$rKey] ?? null; @endphp
                        <div class="p-3 rounded-xl border-2 border-black {{ $sig ? 'bg-emerald-50' : 'bg-slate-50' }} text-xs">
                            <div class="font-black uppercase text-[10px] text-slate-500">{{ $rLabel }}</div>
                            @if($sig)
                                <div class="font-black mt-1">{{ $sig->signer_nama }}</div>
                                <div class="text-[11px] text-slate-600">NIP: {{ $sig->signer_nip ?: '-' }}</div>
                                <div class="text-[11px] font-bold text-emerald-800 mt-1">{{ $sig->signed_at->format('d/m/Y H:i') }}</div>
                            @else
                                <div class="italic text-slate-400 mt-1">Belum TTD</div>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    </main>
</body>
</html>
