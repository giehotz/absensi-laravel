@php $redirectTo = $redirectTo ?? 'siklus'; @endphp

@if($jadwal->status !== \App\Enums\SupervisiStatus::Selesai->value)
    <div class="bg-rose-50 border-2 border-black rounded-xl p-5 shadow-[4px_4px_0px_0px_#000] space-y-2">
        <h3 class="font-heading font-black text-sm text-rose-950">✕ Hasil &amp; Tanda Tangan Digital Belum Tersedia</h3>
        <p class="text-xs font-semibold text-rose-900">
            Ringkasan nilai akhir dan tanda tangan digital baru dibuka setelah siklus supervisi berstatus <strong>Selesai</strong>.
        </p>
    </div>
@else
    <div class="bg-white border-2 border-black rounded-xl p-5 shadow-[4px_4px_0px_0px_#000] flex flex-wrap items-center justify-between gap-4">
        <div>
            <div class="text-xs font-bold text-slate-500">{{ $jadwal->academicYear?->name }} ({{ ucfirst($jadwal->academicYear?->semester ?? '') }}) · {{ $jadwal->tanggal?->format('d/m/Y') }}</div>
            <h3 class="font-heading font-black text-lg">Nilai Akhir: {{ $jadwal->nilai_akhir !== null ? number_format((float) $jadwal->nilai_akhir, 2) . '%' : '-' }} ({{ $jadwal->ketercapaian ?? '-' }})</h3>
            <p class="text-xs font-semibold text-slate-600">Supervisor Penilai: {{ $jadwal->supervisor_display_name }}</p>
        </div>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('supervisi.guru.hasil.show', $jadwal) }}" class="px-3 py-2 bg-white text-black text-xs font-black rounded-lg border-2 border-black shadow-[2px_2px_0px_0px_#000]">📄 Halaman Detail Lengkap</a>
            <a href="{{ route('supervisi.cetak', ['jadwal' => $jadwal, 'type' => 'detail']) }}" target="_blank" class="px-3 py-2 bg-[#20C997] text-black text-xs font-black rounded-lg border-2 border-black shadow-[2px_2px_0px_0px_#000]">🖨️ Cetak Hasil</a>
        </div>
    </div>

    <div class="bg-white border-2 border-black rounded-xl p-5 shadow-[4px_4px_0px_0px_#000] space-y-4">
        <div class="flex flex-wrap items-center justify-between gap-2">
            <div>
                <h3 class="font-heading font-black text-sm">✍️ Tanda Tangan Digital Sekuensial (QR Code)</h3>
                <p class="text-xs text-slate-600">Urutan wajib: 1. Supervisor → 2. Guru → 3. Kepala Madrasah</p>
            </div>
            <span class="px-3 py-1 rounded-full border-2 border-black text-xs font-black {{ $isFullySigned ? 'bg-emerald-200' : 'bg-amber-100' }}">
                {{ $isFullySigned ? 'Disahkan Lengkap' : 'Menunggu Pengesahan' }}
            </span>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
            @foreach(['supervisor' => '1. Supervisor', 'guru' => '2. Guru', 'kepala' => '3. Kepala Madrasah'] as $rKey => $rLabel)
                @php $sig = $signatures[$rKey] ?? null; @endphp
                <div class="p-3.5 rounded-lg border-2 border-black {{ $sig ? 'bg-emerald-50' : 'bg-slate-50' }}">
                    <div class="text-[11px] font-black uppercase text-slate-600">{{ $rLabel }}</div>
                    @if($sig)
                        <div class="font-black text-sm mt-1">{{ $sig->signer_nama }}</div>
                        <div class="text-xs text-slate-600">NIP: {{ $sig->signer_nip ?: '-' }}</div>
                        <div class="text-[11px] font-bold text-emerald-800 mt-1">TTD: {{ $sig->tanggal_ttd->format('d/m/Y') }}</div>
                    @else
                        <div class="text-xs font-bold text-slate-400 italic mt-2">Belum ditandatangani</div>
                    @endif
                </div>
            @endforeach
        </div>

        <div class="flex flex-wrap items-center justify-between gap-3 pt-2">
            <p class="text-xs font-semibold text-slate-600">
                @if(!empty($signatures['guru']))
                    Anda sudah menandatangani dokumen supervisi ini.
                @elseif($canSignCheck['allowed'])
                    Giliran Anda menandatangani dokumen supervisi ini.
                @else
                    {{ $canSignCheck['message'] }}
                @endif
            </p>

            @if($canSignCheck['allowed'])
                <button type="button" data-open-sign-dialog class="px-4 py-2 bg-[#20C997] hover:bg-emerald-400 text-black font-black text-xs rounded-lg border-2 border-black shadow-[2px_2px_0px_0px_#000] cursor-pointer">
                    ✍️ Tanda Tangani Digital Sekarang
                </button>
            @endif
        </div>
    </div>

    <div class="bg-white border-2 border-black rounded-xl p-5 shadow-[4px_4px_0px_0px_#000] space-y-2">
        <h4 class="font-heading font-black text-sm">Ringkasan Komponen Nilai</h4>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            @foreach($jadwal->hasils as $hasil)
                <div class="p-3 rounded-lg border-2 border-black bg-slate-50 flex items-center justify-between gap-2">
                    <span class="text-xs font-black">{{ $hasil->jenisPenilaian?->nama }}</span>
                    <span class="px-2 py-0.5 rounded border border-black bg-[#FFD43B] text-xs font-black tabular-nums">
                        {{ $hasil->total_skor }}/{{ $hasil->skor_maksimal }} ({{ number_format((float) $hasil->nilai_akhir, 2) }}%)
                    </span>
                </div>
            @endforeach
        </div>
    </div>

    <dialog id="dialogGuruSiklusSign" aria-labelledby="dialogGuruSiklusSignTitle" class="p-0 rounded-xl border-4 border-black shadow-[6px_6px_0px_0px_#000] w-full max-w-md m-auto">
        <form action="{{ route('supervisi.signature.sign', $jadwal) }}" method="POST" class="bg-white p-5 space-y-4">
            @csrf
            <input type="hidden" name="signer_role" value="guru">
            <input type="hidden" name="redirect_to" value="{{ $redirectTo }}">
            <h4 id="dialogGuruSiklusSignTitle" class="font-heading font-black text-base">Konfirmasi Tanda Tangan Digital Guru</h4>
            <p class="text-xs text-slate-600">Anda akan menandatangani persetujuan hasil supervisi ini sebagai <strong>GURU</strong>.</p>
            <div>
                <label for="tanggal_ttd_siklus" class="block text-xs font-black mb-1">Tanggal Tanda Tangan</label>
                <input id="tanggal_ttd_siklus" type="date" name="tanggal_ttd" value="{{ $jadwal->tanggal?->toDateString() ?? now()->toDateString() }}" required class="w-full px-3 py-2 text-xs border-2 border-black rounded-lg">
            </div>
            <div class="flex justify-end gap-2">
                <button type="button" data-close-sign-dialog class="px-3 py-2 bg-slate-100 text-xs font-bold rounded-lg border-2 border-black cursor-pointer">Batal</button>
                <button type="submit" class="px-4 py-2 bg-[#20C997] text-black text-xs font-black rounded-lg border-2 border-black cursor-pointer">Ya, Tandatangani</button>
            </div>
        </form>
    </dialog>
@endif
