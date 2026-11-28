@php
    use App\Models\Supervisi\SupervisiCoachingTirta;

    $redirectTo = $redirectTo ?? null;
    $canAccessPasca = $canAccessPasca ?? true;
    $tl = $jadwal->tindakLanjut;
    $coachingPasca = $jadwal->coachingTirtas->firstWhere('tahap', SupervisiCoachingTirta::TAHAP_PASCA);
@endphp

@if(!$canAccessPasca)
    <div class="bg-amber-50 border-2 border-black rounded-xl p-5 shadow-[4px_4px_0px_0px_#000] space-y-2">
        <h3 class="font-heading font-black text-sm text-amber-950">⏳ Formulir C &amp; D Belum Dibuka</h3>
        <p class="text-xs font-semibold text-amber-900">
            Rencana Tindak Lanjut (Formulir C) dan Refleksi (Formulir D) baru dapat diisi setelah Supervisor selesai melaksanakan dan menyimpan hasil observasi kelas.
        </p>
    </div>
@else
    <div class="bg-white border-2 border-black rounded-xl p-5 shadow-[4px_4px_0px_0px_#000] space-y-4">
        <h2 class="font-heading font-black text-base">Formulir C: Rencana Tindak Lanjut Pasca Observasi</h2>
        <form action="{{ route('supervisi.guru.tindak-lanjut.save-c', $jadwal) }}" method="POST" class="space-y-3">
            @csrf
            @if($redirectTo)
                <input type="hidden" name="redirect_to" value="{{ $redirectTo }}">
            @endif
            <div>
                <label for="refleksi_guru" class="block text-xs font-black mb-1">Refleksi Guru atas Hasil Observasi</label>
                <textarea id="refleksi_guru" name="refleksi_guru" rows="3" required class="w-full px-3 py-2 text-xs border-2 border-black rounded-lg bg-white">{{ old('refleksi_guru', $tl?->refleksi_guru) }}</textarea>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                <div>
                    <label for="tujuan_tindak_lanjut" class="block text-xs font-black mb-1">Tujuan Tindak Lanjut</label>
                    <textarea id="tujuan_tindak_lanjut" name="tujuan_tindak_lanjut" rows="2" required class="w-full px-3 py-2 text-xs border-2 border-black rounded-lg bg-white">{{ old('tujuan_tindak_lanjut', $tl?->tujuan_tindak_lanjut) }}</textarea>
                </div>
                <div>
                    <label for="upaya_tindak_lanjut" class="block text-xs font-black mb-1">Upaya Tindak Lanjut</label>
                    <textarea id="upaya_tindak_lanjut" name="upaya_tindak_lanjut" rows="2" required class="w-full px-3 py-2 text-xs border-2 border-black rounded-lg bg-white">{{ old('upaya_tindak_lanjut', $tl?->upaya_tindak_lanjut) }}</textarea>
                </div>
                <div>
                    <label for="pilihan_belajar_pmm" class="block text-xs font-black mb-1">Pilihan Belajar PMM</label>
                    <input id="pilihan_belajar_pmm" type="text" name="pilihan_belajar_pmm" value="{{ old('pilihan_belajar_pmm', $tl?->pilihan_belajar_pmm) }}" class="w-full px-3 py-2 text-xs border-2 border-black rounded-lg bg-white">
                </div>
                <div>
                    <label for="periode_tindak_lanjut" class="block text-xs font-black mb-1">Periode Pelaksanaan</label>
                    <input id="periode_tindak_lanjut" type="text" name="periode_tindak_lanjut" value="{{ old('periode_tindak_lanjut', $tl?->periode_tindak_lanjut) }}" placeholder="Contoh: 2 minggu ke depan" class="w-full px-3 py-2 text-xs border-2 border-black rounded-lg bg-white">
                </div>
                <div>
                    <label for="kebutuhan_dukungan" class="block text-xs font-black mb-1">Kebutuhan Dukungan (Kombel/Sekolah)</label>
                    <input id="kebutuhan_dukungan" type="text" name="kebutuhan_dukungan" value="{{ old('kebutuhan_dukungan', $tl?->kebutuhan_dukungan) }}" placeholder="Contoh: Sumber daya pembelajaran / Coaching rekan" class="w-full px-3 py-2 text-xs border-2 border-black rounded-lg bg-white">
                </div>
                <div>
                    <label for="detail_dukungan" class="block text-xs font-black mb-1">Detail Dukungan</label>
                    <input id="detail_dukungan" type="text" name="detail_dukungan" value="{{ old('detail_dukungan', $tl?->detail_dukungan) }}" class="w-full px-3 py-2 text-xs border-2 border-black rounded-lg bg-white">
                </div>
            </div>
            <div class="flex justify-end">
                <button type="submit" class="px-4 py-2 bg-[#FFD43B] hover:bg-amber-300 text-black text-xs font-black rounded-lg border-2 border-black cursor-pointer">Simpan Formulir C</button>
            </div>
        </form>
    </div>

    @if($tl)
        <div class="bg-white border-2 border-black rounded-xl p-5 shadow-[4px_4px_0px_0px_#000] space-y-4">
            <h2 class="font-heading font-black text-base">Formulir D: Refleksi Tindak Lanjut</h2>
            <form action="{{ route('supervisi.guru.tindak-lanjut.save-d', $jadwal) }}" method="POST" class="grid grid-cols-1 md:grid-cols-2 gap-3">
                @csrf
                @if($redirectTo)
                    <input type="hidden" name="redirect_to" value="{{ $redirectTo }}">
                @endif
                <div>
                    <label for="inspirasi_baru" class="block text-xs font-black mb-1">Inspirasi Baru yang Didapat</label>
                    <textarea id="inspirasi_baru" name="inspirasi_baru" rows="2" required class="w-full px-3 py-2 text-xs border-2 border-black rounded-lg bg-white">{{ old('inspirasi_baru', $tl->inspirasi_baru) }}</textarea>
                </div>
                <div>
                    <label for="perubahan_praktik" class="block text-xs font-black mb-1">Perubahan Praktik di Kelas</label>
                    <textarea id="perubahan_praktik" name="perubahan_praktik" rows="2" required class="w-full px-3 py-2 text-xs border-2 border-black rounded-lg bg-white">{{ old('perubahan_praktik', $tl->perubahan_praktik) }}</textarea>
                </div>
                <div>
                    <label for="kendala_tindak_lanjut" class="block text-xs font-black mb-1">Kendala Pelaksanaan</label>
                    <textarea id="kendala_tindak_lanjut" name="kendala_tindak_lanjut" rows="2" class="w-full px-3 py-2 text-xs border-2 border-black rounded-lg bg-white">{{ old('kendala_tindak_lanjut', $tl->kendala_tindak_lanjut) }}</textarea>
                </div>
                <div>
                    <label for="rencana_perbaikan_berkelanjutan" class="block text-xs font-black mb-1">Rencana Perbaikan Berkelanjutan</label>
                    <textarea id="rencana_perbaikan_berkelanjutan" name="rencana_perbaikan_berkelanjutan" rows="2" class="w-full px-3 py-2 text-xs border-2 border-black rounded-lg bg-white">{{ old('rencana_perbaikan_berkelanjutan', $tl->rencana_perbaikan_berkelanjutan) }}</textarea>
                </div>
                <div class="md:col-span-2 flex justify-end">
                    <button type="submit" class="px-5 py-2 bg-[#20C997] hover:bg-emerald-400 text-black text-xs font-black rounded-lg border-2 border-black cursor-pointer">Simpan Formulir D</button>
                </div>
            </form>
        </div>
    @else
        <div class="p-4 rounded-xl border-2 border-black bg-slate-50 text-xs font-bold text-slate-700">
            Isi dan simpan Formulir C di atas terlebih dahulu untuk membuka Formulir D (Refleksi Tindak Lanjut).
        </div>
    @endif

    @if($coachingPasca && $coachingPasca->status !== SupervisiCoachingTirta::STATUS_DRAFT)
        <div class="bg-white border-2 border-black rounded-xl p-5 shadow-[4px_4px_0px_0px_#000] space-y-4">
            <div class="flex flex-wrap items-center justify-between gap-2">
                <h2 class="font-heading font-black text-base">💬 Coaching TIRTA Pasca-Observasi (Konfirmasi Guru)</h2>
                <span class="px-2.5 py-1 rounded border border-black text-[11px] font-black {{ $coachingPasca->status === SupervisiCoachingTirta::STATUS_CONFIRMED ? 'bg-emerald-200 text-emerald-950' : 'bg-[#FFD43B] text-black' }}">
                    {{ $coachingPasca->status === SupervisiCoachingTirta::STATUS_CONFIRMED ? '✓ Dikonfirmasi Guru' : '⏳ Menunggu Konfirmasi Anda' }}
                </span>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-3 text-xs">
                <div class="p-3 bg-slate-50 border border-black rounded-lg"><strong>T — Tujuan:</strong> {{ $coachingPasca->tujuan_coaching ?: '-' }}</div>
                <div class="p-3 bg-slate-50 border border-black rounded-lg"><strong>I — Identifikasi:</strong> {{ $coachingPasca->identifikasi_kondisi ?: '-' }}</div>
                <div class="p-3 bg-slate-50 border border-black rounded-lg"><strong>R — Rencana Aksi:</strong> {{ $coachingPasca->rencana_aksi ?: '-' }}</div>
                <div class="p-3 bg-slate-50 border border-black rounded-lg"><strong>TA — Tanggung Jawab:</strong> {{ $coachingPasca->tanggung_jawab ?: '-' }}</div>
            </div>

            @if($coachingPasca->catatan_coach)
                <div class="p-3 bg-sky-50 border border-black rounded-lg text-xs">
                    <strong>Catatan Penguatan Supervisor:</strong> {{ $coachingPasca->catatan_coach }}
                </div>
            @endif

            <form action="{{ route('supervisi.guru.tindak-lanjut.coaching-confirm', $jadwal) }}" method="POST" class="space-y-3 border-t-2 border-slate-200 pt-3">
                @csrf
                @if($redirectTo)
                    <input type="hidden" name="redirect_to" value="{{ $redirectTo }}">
                @endif
                <div>
                    <label for="catatan_guru_tirta" class="block text-xs font-black mb-1">Komitmen &amp; Refleksi Pribadi Guru</label>
                    <textarea id="catatan_guru_tirta" name="catatan_guru" rows="2" required placeholder="Tuliskan komitmen pelaksanaan tindak lanjut hasil coaching TIRTA..." class="w-full px-3 py-2 text-xs border-2 border-black rounded-lg bg-white">{{ old('catatan_guru', $coachingPasca->catatan_guru) }}</textarea>
                </div>
                <div class="flex flex-wrap items-center justify-between gap-2">
                    <a href="{{ route('supervisi.cetak', ['jadwal' => $jadwal, 'type' => 'coaching']) }}" target="_blank" class="px-3 py-1.5 bg-white border-2 border-black rounded-lg text-xs font-black">
                        🖨️ Cetak Lembar TIRTA
                    </a>
                    <button type="submit" class="px-4 py-2 bg-[#20C997] hover:bg-emerald-400 text-black text-xs font-black rounded-lg border-2 border-black cursor-pointer">
                        ✅ Setujui Hasil Coaching TIRTA
                    </button>
                </div>
            </form>
        </div>
    @endif
@endif
