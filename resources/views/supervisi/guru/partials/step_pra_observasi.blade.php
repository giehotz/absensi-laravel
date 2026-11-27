@php
    $redirectTo = $redirectTo ?? null;
    $isDisepakati = $pra?->isAgreed() ?? false;
    $isDiajukan = $pra?->status_kesepakatan === 'diajukan';
    $selectedIndikatorId = old('indikator_pmm_id', $jadwal->indikator_pmm_id ?? ($indikatorPmm->first()?->id));
    $selectedFokus = array_map('intval', (array) old('fokus_perilaku_dipilih', $fokusDipilih));
@endphp

<div class="bg-white border-2 border-black rounded-xl p-5 shadow-[4px_4px_0px_0px_#000] flex flex-wrap justify-between items-center gap-3">
    <div>
        <h2 class="font-heading font-black text-base">Jadwal: {{ $jadwal->schoolClass?->name ?? '-' }} — {{ $jadwal->subject?->name ?? '-' }} ({{ $jadwal->tanggal?->format('d/m/Y') }})</h2>
        <p class="text-xs text-slate-600">Status Formulir A: <strong>{{ $pra?->status_kesepakatan ?? 'draft_guru' }}</strong></p>
    </div>
    @if($isDiajukan && !$isDisepakati)
        <form action="{{ route('supervisi.guru.pra-observasi.tarik', $jadwal) }}" method="POST">
            @csrf
            @if($redirectTo)
                <input type="hidden" name="redirect_to" value="{{ $redirectTo }}">
            @endif
            <button type="submit" class="px-3.5 py-2 bg-amber-100 text-black text-xs font-black rounded-lg border-2 border-black shadow-[2px_2px_0px_0px_#000] cursor-pointer">
                ↺ Tarik Pengajuan ke Draf
            </button>
        </form>
    @endif
</div>

<form action="{{ route('supervisi.guru.pra-observasi.save', $jadwal) }}" method="POST" class="bg-white border-2 border-black rounded-xl p-6 shadow-[4px_4px_0px_0px_#000] space-y-5">
    @csrf
    @if($redirectTo)
        <input type="hidden" name="redirect_to" value="{{ $redirectTo }}">
    @endif
    <div>
        <label for="indikator_pmm_id" class="block text-xs font-black mb-1">1. Target Indikator Kinerja PMM</label>
        <select id="indikator_pmm_id" name="indikator_pmm_id" data-fokus-selector {{ $isDisepakati ? 'disabled' : '' }} class="w-full px-3 py-2 text-xs border-2 border-black rounded-lg bg-white font-bold">
            <option value="">-- Pilih Indikator PMM --</option>
            @foreach($indikatorPmm as $ind)
                <option value="{{ $ind->id }}" {{ (int) $selectedIndikatorId === (int) $ind->id ? 'selected' : '' }}>{{ $ind->nama }}</option>
            @endforeach
        </select>
    </div>

    <div class="space-y-2">
        <div class="text-xs font-black">2. Pilih 1 s/d 3 Fokus Perilaku yang Akan Diobservasi</div>
        @foreach($indikatorPmm as $ind)
            <div class="fokus-group space-y-2 {{ (int) $selectedIndikatorId === (int) $ind->id ? '' : 'hidden' }}" data-indikator="{{ $ind->id }}">
                @foreach($ind->aspekPenilaians as $aspek)
                    <label class="block p-3 rounded-lg border-2 border-black bg-slate-50 cursor-pointer">
                        <div class="flex items-start gap-2">
                            <input type="checkbox" name="fokus_perilaku_dipilih[]" value="{{ $aspek->id }}" {{ in_array((int) $aspek->id, $selectedFokus, true) ? 'checked' : '' }} {{ $isDisepakati ? 'disabled' : '' }}>
                            <div class="text-xs">
                                <div class="font-black">{{ $aspek->nama_aspek }}</div>
                                <div class="text-slate-700">{{ $aspek->fokus_perilaku }}</div>
                            </div>
                        </div>
                    </label>
                @endforeach
            </div>
        @endforeach
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
            <label for="tujuan_pembelajaran_sesi" class="block text-xs font-black mb-1">3. Tujuan Pembelajaran Sesi Observasi</label>
            <textarea id="tujuan_pembelajaran_sesi" name="tujuan_pembelajaran_sesi" rows="3" {{ $isDisepakati ? 'readonly' : '' }} class="w-full px-3 py-2 text-xs border-2 border-black rounded-lg bg-white">{{ old('tujuan_pembelajaran_sesi', $pra?->tujuan_pembelajaran_sesi) }}</textarea>
        </div>
        <div>
            <label for="upaya_mempelajari" class="block text-xs font-black mb-1">4. Upaya Mempelajari Target Perilaku</label>
            <textarea id="upaya_mempelajari" name="upaya_mempelajari" rows="3" {{ $isDisepakati ? 'readonly' : '' }} class="w-full px-3 py-2 text-xs border-2 border-black rounded-lg bg-white">{{ old('upaya_mempelajari', $pra?->upaya_mempelajari) }}</textarea>
        </div>
        <div>
            <label for="area_pengembangan" class="block text-xs font-black mb-1">5. Area Pengembangan yang Hendak Dicapai</label>
            <textarea id="area_pengembangan" name="area_pengembangan" rows="2" {{ $isDisepakati ? 'readonly' : '' }} class="w-full px-3 py-2 text-xs border-2 border-black rounded-lg bg-white">{{ old('area_pengembangan', $pra?->area_pengembangan) }}</textarea>
        </div>
        <div>
            <label for="strategi_persiapan" class="block text-xs font-black mb-1">6. Strategi Persiapan Pembelajaran</label>
            <textarea id="strategi_persiapan" name="strategi_persiapan" rows="2" {{ $isDisepakati ? 'readonly' : '' }} class="w-full px-3 py-2 text-xs border-2 border-black rounded-lg bg-white">{{ old('strategi_persiapan', $pra?->strategi_persiapan) }}</textarea>
        </div>
    </div>

    @if(!$isDisepakati)
        <div class="flex justify-end gap-2">
            <button type="submit" name="aksi" value="draft" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-black text-xs font-black rounded-lg border-2 border-black cursor-pointer">Simpan Draf</button>
            <button type="submit" name="aksi" value="ajukan" class="px-5 py-2 bg-[#20C997] hover:bg-emerald-400 text-black text-xs font-black rounded-lg border-2 border-black shadow-[2px_2px_0px_0px_#000] cursor-pointer">🚀 Ajukan ke Supervisor</button>
        </div>
    @else
        <div class="p-3 rounded-lg border-2 border-black bg-emerald-50 text-xs font-bold text-emerald-950">
            ✓ Formulir A telah disepakati bersama supervisor pada {{ $pra?->disepakati_pada?->format('d/m/Y H:i') ?? '-' }}.
        </div>
    @endif
</form>

@push('scripts')
<script>
(() => {
    const selector = document.querySelector('[data-fokus-selector]');
    selector?.addEventListener('change', () => {
        const selected = selector.value;
        document.querySelectorAll('.fokus-group').forEach((el) => {
            el.classList.toggle('hidden', el.dataset.indikator !== selected);
        });
    });
})();
</script>
@endpush
