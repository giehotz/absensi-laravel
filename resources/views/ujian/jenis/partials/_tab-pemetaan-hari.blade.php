{{-- 3. Tab Pemetaan Hari & Sesi Ujian --}}
<div class="space-y-6">
    {{-- Toolbar Pemilih Periode --}}
    <div class="p-4 bg-white border-4 border-black rounded-xl shadow-[4px_4px_0px_0px_#000] flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <form method="GET" action="{{ route('ujian.jenis.index') }}" class="flex items-center gap-3">
            <input type="hidden" name="tab" value="pemetaan-hari">
            <div class="flex items-center gap-2 shrink-0">
                <span class="text-base">🗓️</span>
                <label for="pemetaan_periode_id" class="text-xs font-black uppercase tracking-wider text-black">
                    Pilih Periode:
                </label>
            </div>
            <select id="pemetaan_periode_id" name="periode_id" onchange="this.form.submit()"
                    class="cursor-pointer rounded-lg border-2 border-black bg-white px-3.5 py-1.5 text-xs font-black text-slate-900 shadow-[2px_2px_0px_0px_#000] focus:outline-none">
                @foreach($periodes as $p)
                    <option value="{{ $p->id }}" {{ $p->id == $selectedPeriodeId ? 'selected' : '' }}>
                        {{ $p->nama_periode }} {{ $p->is_active ? '★ (Aktif)' : '' }}
                    </option>
                @endforeach
            </select>
        </form>

        @if($selectedPeriode)
            <div class="flex items-center gap-2 text-xs font-bold text-slate-600">
                <span>Rentang Induk:</span>
                <span class="font-mono bg-slate-100 border border-black px-2 py-0.5 rounded text-black">
                    {{ $selectedPeriode->tanggal_mulai ? $selectedPeriode->tanggal_mulai->format('d/m/Y') : '-' }} s.d. {{ $selectedPeriode->tanggal_selesai ? $selectedPeriode->tanggal_selesai->format('d/m/Y') : '-' }}
                </span>
            </div>
        @endif
    </div>

    @if(! $selectedPeriode)
        <div class="p-8 bg-white border-4 border-black rounded-xl text-center shadow-[4px_4px_0px_0px_#000]">
            <p class="text-sm font-bold text-slate-600">
                Silakan buat periode ujian terlebih dahulu pada tab "Periode Pelaksanaan" sebelum mengatur pemetaan hari.
            </p>
        </div>
    @else
        @php
            $configBawah = $configs->get('bawah');
            $configAtas = $configs->get('atas');

            $isBawahSubmitted = old('kelompok') === 'bawah';
            $isAtasSubmitted = old('kelompok') === 'atas';

            $bagBawah = $errors->getBag('pemetaan_bawah');
            $bagAtas = $errors->getBag('pemetaan_atas');

            // Default jam sesi jika konfigurasi belum ada
            $defaultJamArray = [
                1 => ['mulai' => '07:30', 'selesai' => '09:00'],
                2 => ['mulai' => '09:30', 'selesai' => '11:00'],
                3 => ['mulai' => '13:00', 'selesai' => '14:30'],
                4 => ['mulai' => '15:00', 'selesai' => '16:30'],
            ];

            $defaultJamAtasArray = [
                1 => ['mulai' => '07:30', 'selesai' => '09:30'],
                2 => ['mulai' => '10:00', 'selesai' => '12:00'],
                3 => ['mulai' => '13:00', 'selesai' => '14:30'],
                4 => ['mulai' => '15:00', 'selesai' => '16:30'],
            ];
        @endphp

        {{-- Grid Dua Kartu: Kelas Bawah & Kelas Atas --}}
        <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">

            {{-- ============================================================== --}}
            {{-- KARTU 1: KELAS BAWAH (1-2)                                     --}}
            {{-- ============================================================== --}}
            <div class="bg-white border-4 border-black rounded-xl p-5 shadow-[5px_5px_0px_0px_#000] flex flex-col justify-between">
                <div>
                    {{-- Header Kartu --}}
                    <div class="pb-3 border-b-2 border-black flex flex-wrap items-center justify-between gap-2">
                        <div>
                            <div class="flex items-center gap-2 mb-1">
                                <span class="px-2.5 py-0.5 bg-[#FFD43B] text-black border-2 border-black rounded-md text-xs font-black shadow-[1.5px_1.5px_0px_0px_#000]">
                                    Kelas Bawah
                                </span>
                                <span class="px-2 py-0.5 bg-slate-100 text-slate-800 border border-black rounded text-[11px] font-bold">
                                    Tingkat 1–2
                                </span>
                                <span class="px-2 py-0.5 bg-blue-100 text-blue-900 border border-black rounded text-[11px] font-bold">
                                    Mode Per Rombel
                                </span>
                            </div>
                            <h3 class="text-base font-black font-heading text-black">
                                Kalender Ujian Kelas Bawah
                            </h3>
                            <p class="text-xs text-slate-600 mt-0.5">
                                Mengatur slot jadwal terpisah untuk rombel kelas 1 dan 2 (total {{ $kelasBawah->count() }} rombel).
                            </p>
                        </div>

                        @if($configBawah)
                            <span class="px-2 py-0.5 rounded text-[10px] font-black bg-emerald-100 text-emerald-950 border border-emerald-600">
                                Terkonfigurasi
                            </span>
                        @else
                            <span class="px-2 py-0.5 rounded text-[10px] font-black bg-amber-100 text-amber-950 border border-amber-600">
                                Belum Dikonfigurasi
                            </span>
                        @endif
                    </div>

                    {{-- Error Bag Bawah --}}
                    @if($bagBawah->any())
                        <div class="my-3 p-3 bg-rose-50 border-2 border-rose-600 rounded-lg text-rose-900 text-xs font-bold space-y-1">
                            <div class="flex items-center gap-1.5 text-rose-950 font-black">
                                <span>⚠️</span>
                                <span>Terdapat kesalahan pada input Kelas Bawah:</span>
                            </div>
                            <ul class="list-disc list-inside pl-1 text-[11px]">
                                @foreach($bagBawah->all() as $err)
                                    <li>{{ $err }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form id="form_pemetaan_bawah" action="{{ route('ujian.jenis.pemetaan-hari.store') }}" method="POST" class="mt-4 space-y-4 text-xs font-semibold">
                        @csrf
                        <input type="hidden" name="ujian_periode_id" value="{{ $selectedPeriode->id }}">
                        <input type="hidden" name="kelompok" value="bawah">

                        {{-- Rentang Tanggal --}}
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div>
                                <label for="bawah_tanggal_mulai" class="block font-bold text-black mb-1">Tanggal Mulai</label>
                                <input type="date" id="bawah_tanggal_mulai" name="tanggal_mulai" required
                                       value="{{ $isBawahSubmitted ? old('tanggal_mulai') : ($configBawah?->tanggal_mulai?->format('Y-m-d') ?? $selectedPeriode->tanggal_mulai?->format('Y-m-d') ?? '') }}"
                                       class="w-full px-3 py-2 border-2 border-black rounded-lg bg-white text-xs font-bold text-black shadow-[2px_2px_0px_0px_#000] focus:outline-none">
                            </div>
                            <div>
                                <label for="bawah_tanggal_selesai" class="block font-bold text-black mb-1">Tanggal Selesai</label>
                                <input type="date" id="bawah_tanggal_selesai" name="tanggal_selesai" required
                                       value="{{ $isBawahSubmitted ? old('tanggal_selesai') : ($configBawah?->tanggal_selesai?->format('Y-m-d') ?? $selectedPeriode->tanggal_selesai?->format('Y-m-d') ?? '') }}"
                                       class="w-full px-3 py-2 border-2 border-black rounded-lg bg-white text-xs font-bold text-black shadow-[2px_2px_0px_0px_#000] focus:outline-none">
                            </div>
                        </div>

                        {{-- Kecualikan Minggu & Tanggal Libur --}}
                        <div class="space-y-2">
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="checkbox" name="kecualikan_minggu" value="1"
                                       {{ ($isBawahSubmitted ? old('kecualikan_minggu') : ($configBawah?->kecualikan_minggu ?? true)) ? 'checked' : '' }}
                                       class="w-4 h-4 border-2 border-black rounded text-black focus:ring-0">
                                <span class="font-bold text-slate-800">Kecualikan Hari Minggu (Tidak ada ujian pada hari Minggu)</span>
                            </label>

                            <div>
                                <label for="bawah_tanggal_libur" class="block font-bold text-slate-800 mb-1">
                                    Tanggal Libur Khusus / Dikecualikan
                                </label>
                                @php
                                    $valLiburBawah = $isBawahSubmitted
                                        ? (is_array(old('tanggal_libur')) ? implode(', ', old('tanggal_libur')) : old('tanggal_libur'))
                                        : ($configBawah?->tanggal_libur ? implode(', ', $configBawah->tanggal_libur) : '');
                                @endphp
                                <textarea id="bawah_tanggal_libur" name="tanggal_libur" rows="2"
                                          placeholder="Contoh: 2026-12-03, 2026-12-04 (pisahkan dengan koma atau baris baru)"
                                          class="w-full px-3 py-2 border-2 border-black rounded-lg bg-white text-xs font-mono text-black shadow-[2px_2px_0px_0px_#000] focus:outline-none">{{ $valLiburBawah }}</textarea>
                            </div>
                        </div>

                        {{-- Jumlah Sesi & Sesi Hari Terakhir --}}
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div>
                                <label for="bawah_jumlah_sesi" class="block font-bold text-black mb-1">Sesi per Hari (Biasa)</label>
                                <select id="bawah_jumlah_sesi" name="jumlah_sesi_per_hari" required
                                        class="w-full px-3 py-2 border-2 border-black rounded-lg bg-white text-xs font-bold text-black shadow-[2px_2px_0px_0px_#000] focus:outline-none">
                                    @for($s = 1; $s <= 4; $s++)
                                        <option value="{{ $s }}" {{ ($isBawahSubmitted ? old('jumlah_sesi_per_hari') : ($configBawah?->jumlah_sesi_per_hari ?? 2)) == $s ? 'selected' : '' }}>
                                            {{ $s }} Sesi / Hari
                                        </option>
                                    @endfor
                                </select>
                            </div>
                            <div>
                                <label for="bawah_sesi_hari_terakhir" class="block font-bold text-black mb-1">Sesi pada Hari Terakhir</label>
                                <select id="bawah_sesi_hari_terakhir" name="sesi_hari_terakhir" required
                                        class="w-full px-3 py-2 border-2 border-black rounded-lg bg-white text-xs font-bold text-black shadow-[2px_2px_0px_0px_#000] focus:outline-none">
                                    @for($s = 1; $s <= 4; $s++)
                                        <option value="{{ $s }}" {{ ($isBawahSubmitted ? old('sesi_hari_terakhir') : ($configBawah?->sesi_hari_terakhir ?? 1)) == $s ? 'selected' : '' }}>
                                            {{ $s }} Sesi (Hari Terakhir)
                                        </option>
                                    @endfor
                                </select>
                            </div>
                        </div>

                        {{-- Pengaturan Jam Sesi Dinamis --}}
                        <div>
                            <div class="flex items-center justify-between mb-1.5">
                                <label class="font-bold text-black">Alokasi Waktu Jam Ujian per Sesi</label>
                                <span class="text-[10px] text-slate-500 font-normal">Format: JJ:MM</span>
                            </div>
                            <div id="container_jam_bawah" class="space-y-2">
                                @php
                                    $currentCountBawah = (int) ($isBawahSubmitted ? old('jumlah_sesi_per_hari', 2) : ($configBawah?->jumlah_sesi_per_hari ?? 2));
                                    $jamBawahSaved = $isBawahSubmitted ? old('jam_sesi', []) : ($configBawah?->jam_sesi ?? []);
                                    $jamBawahMap = collect($jamBawahSaved)->keyBy('sesi')->all();
                                @endphp
                                @for($i = 1; $i <= 4; $i++)
                                    @php
                                        $rowMulai = $jamBawahMap[$i]['mulai'] ?? ($defaultJamArray[$i]['mulai'] ?? '07:30');
                                        $rowSelesai = $jamBawahMap[$i]['selesai'] ?? ($defaultJamArray[$i]['selesai'] ?? '09:00');
                                    @endphp
                                    <div class="session-row-bawah flex items-center gap-2 p-2 bg-slate-50 border border-black rounded-lg {{ $i > $currentCountBawah ? 'hidden' : '' }}" data-sesi="{{ $i }}">
                                        <span class="w-16 text-xs font-black text-black">Sesi {{ $i }}</span>
                                        <input type="hidden" name="jam_sesi[{{ $i - 1 }}][sesi]" value="{{ $i }}">
                                        <div class="flex items-center gap-1.5 flex-1">
                                            <input type="text" name="jam_sesi[{{ $i - 1 }}][mulai]" value="{{ $rowMulai }}"
                                                   placeholder="07:30" maxlength="5"
                                                   class="w-20 px-2 py-1 text-center font-mono font-bold border-2 border-black rounded bg-white text-xs shadow-[1.5px_1.5px_0px_0px_#000]">
                                            <span class="font-black text-black">s.d.</span>
                                            <input type="text" name="jam_sesi[{{ $i - 1 }}][selesai]" value="{{ $rowSelesai }}"
                                                   placeholder="09:00" maxlength="5"
                                                   class="w-20 px-2 py-1 text-center font-mono font-bold border-2 border-black rounded bg-white text-xs shadow-[1.5px_1.5px_0px_0px_#000]">
                                        </div>
                                    </div>
                                @endfor
                            </div>
                        </div>

                        {{-- Live Preview Box Bawah --}}
                        <div class="p-3 bg-amber-50/70 border-2 border-black rounded-lg space-y-2" aria-live="polite">
                            <div class="flex items-center justify-between">
                                <span class="text-xs font-black uppercase text-black flex items-center gap-1.5">
                                    <span>📊</span>
                                    <span>Kalkulasi Kalender:</span>
                                </span>
                                <span id="badge_preview_bawah" class="px-2.5 py-0.5 bg-[#FFD43B] text-black border border-black rounded-md font-black text-xs shadow-[1.5px_1.5px_0px_0px_#000]">
                                    {{ $previewBawah['hari_efektif'] ?? 0 }} Hari Efektif • {{ $previewBawah['total_slot'] ?? 0 }} Slot Sesi
                                </span>
                            </div>
                            <div id="chips_preview_bawah" class="flex flex-wrap gap-1.5">
                                @if(!empty($previewBawah['tanggal']))
                                    @foreach($previewBawah['tanggal'] as $t)
                                        <span class="px-2 py-0.5 bg-white border border-black rounded text-[10px] font-bold text-slate-800 shadow-[1px_1px_0px_0px_#000]">
                                            {{ $t['nama_hari'] }}, {{ $t['label'] }}
                                        </span>
                                    @endforeach
                                @else
                                    <span class="text-[11px] text-slate-500 italic">Preview akan muncul otomatis saat rentang tanggal valid.</span>
                                @endif
                            </div>
                        </div>

                        {{-- Tombol Aksi Bawah --}}
                        <div class="pt-2 flex flex-wrap items-center justify-between gap-2 border-t border-slate-200">
                            <a href="{{ route('ujian.jadwal.template', ['periode_id' => $selectedPeriode->id, 'kelompok' => 'bawah']) }}"
                               class="px-3 py-2 bg-white hover:bg-slate-100 text-black border-2 border-black rounded-lg font-bold text-xs shadow-[2px_2px_0px_0px_#000] flex items-center gap-1.5">
                                <span>📥</span>
                                <span>Unduh Template Bawah</span>
                            </a>
                            <button type="submit"
                                    class="px-4 py-2 bg-[#20C997] hover:bg-emerald-400 text-black border-2 border-black rounded-lg font-black text-xs shadow-[2px_2px_0px_0px_#000] cursor-pointer">
                                💾 Simpan Pemetaan Bawah
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            {{-- ============================================================== --}}
            {{-- KARTU 2: KELAS ATAS (3-6)                                      --}}
            {{-- ============================================================== --}}
            <div class="bg-white border-4 border-black rounded-xl p-5 shadow-[5px_5px_0px_0px_#000] flex flex-col justify-between">
                <div>
                    {{-- Header Kartu --}}
                    <div class="pb-3 border-b-2 border-black flex flex-wrap items-center justify-between gap-2">
                        <div>
                            <div class="flex items-center gap-2 mb-1">
                                <span class="px-2.5 py-0.5 bg-[#4DABF7] text-black border-2 border-black rounded-md text-xs font-black shadow-[1.5px_1.5px_0px_0px_#000]">
                                    Kelas Atas
                                </span>
                                <span class="px-2 py-0.5 bg-slate-100 text-slate-800 border border-black rounded text-[11px] font-bold">
                                    Tingkat 3–6
                                </span>
                                <span class="px-2 py-0.5 bg-purple-100 text-purple-900 border border-black rounded text-[11px] font-bold">
                                    Mode Seragam Tingkat
                                </span>
                            </div>
                            <h3 class="text-base font-black font-heading text-black">
                                Kalender Ujian Kelas Atas
                            </h3>
                            <p class="text-xs text-slate-600 mt-0.5">
                                Mengatur jadwal seragam per tingkat (3, 4, 5, 6) dengan alokasi slot mapel serentak.
                            </p>
                        </div>

                        @if($configAtas)
                            <span class="px-2 py-0.5 rounded text-[10px] font-black bg-emerald-100 text-emerald-950 border border-emerald-600">
                                Terkonfigurasi
                            </span>
                        @else
                            <span class="px-2 py-0.5 rounded text-[10px] font-black bg-amber-100 text-amber-950 border border-amber-600">
                                Belum Dikonfigurasi
                            </span>
                        @endif
                    </div>

                    {{-- Error Bag Atas --}}
                    @if($bagAtas->any())
                        <div class="my-3 p-3 bg-rose-50 border-2 border-rose-600 rounded-lg text-rose-900 text-xs font-bold space-y-1">
                            <div class="flex items-center gap-1.5 text-rose-950 font-black">
                                <span>⚠️</span>
                                <span>Terdapat kesalahan pada input Kelas Atas:</span>
                            </div>
                            <ul class="list-disc list-inside pl-1 text-[11px]">
                                @foreach($bagAtas->all() as $err)
                                    <li>{{ $err }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form id="form_pemetaan_atas" action="{{ route('ujian.jenis.pemetaan-hari.store') }}" method="POST" class="mt-4 space-y-4 text-xs font-semibold">
                        @csrf
                        <input type="hidden" name="ujian_periode_id" value="{{ $selectedPeriode->id }}">
                        <input type="hidden" name="kelompok" value="atas">

                        {{-- Rentang Tanggal --}}
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div>
                                <label for="atas_tanggal_mulai" class="block font-bold text-black mb-1">Tanggal Mulai</label>
                                <input type="date" id="atas_tanggal_mulai" name="tanggal_mulai" required
                                       value="{{ $isAtasSubmitted ? old('tanggal_mulai') : ($configAtas?->tanggal_mulai?->format('Y-m-d') ?? $selectedPeriode->tanggal_mulai?->format('Y-m-d') ?? '') }}"
                                       class="w-full px-3 py-2 border-2 border-black rounded-lg bg-white text-xs font-bold text-black shadow-[2px_2px_0px_0px_#000] focus:outline-none">
                            </div>
                            <div>
                                <label for="atas_tanggal_selesai" class="block font-bold text-black mb-1">Tanggal Selesai</label>
                                <input type="date" id="atas_tanggal_selesai" name="tanggal_selesai" required
                                       value="{{ $isAtasSubmitted ? old('tanggal_selesai') : ($configAtas?->tanggal_selesai?->format('Y-m-d') ?? $selectedPeriode->tanggal_selesai?->format('Y-m-d') ?? '') }}"
                                       class="w-full px-3 py-2 border-2 border-black rounded-lg bg-white text-xs font-bold text-black shadow-[2px_2px_0px_0px_#000] focus:outline-none">
                            </div>
                        </div>

                        {{-- Kecualikan Minggu & Tanggal Libur --}}
                        <div class="space-y-2">
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="checkbox" name="kecualikan_minggu" value="1"
                                       {{ ($isAtasSubmitted ? old('kecualikan_minggu') : ($configAtas?->kecualikan_minggu ?? true)) ? 'checked' : '' }}
                                       class="w-4 h-4 border-2 border-black rounded text-black focus:ring-0">
                                <span class="font-bold text-slate-800">Kecualikan Hari Minggu (Tidak ada ujian pada hari Minggu)</span>
                            </label>

                            <div>
                                <label for="atas_tanggal_libur" class="block font-bold text-slate-800 mb-1">
                                    Tanggal Libur Khusus / Dikecualikan
                                </label>
                                @php
                                    $valLiburAtas = $isAtasSubmitted
                                        ? (is_array(old('tanggal_libur')) ? implode(', ', old('tanggal_libur')) : old('tanggal_libur'))
                                        : ($configAtas?->tanggal_libur ? implode(', ', $configAtas->tanggal_libur) : '');
                                @endphp
                                <textarea id="atas_tanggal_libur" name="tanggal_libur" rows="2"
                                          placeholder="Contoh: 2026-12-03, 2026-12-04 (pisahkan dengan koma atau baris baru)"
                                          class="w-full px-3 py-2 border-2 border-black rounded-lg bg-white text-xs font-mono text-black shadow-[2px_2px_0px_0px_#000] focus:outline-none">{{ $valLiburAtas }}</textarea>
                            </div>
                        </div>

                        {{-- Jumlah Sesi & Sesi Hari Terakhir --}}
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div>
                                <label for="atas_jumlah_sesi" class="block font-bold text-black mb-1">Sesi per Hari (Biasa)</label>
                                <select id="atas_jumlah_sesi" name="jumlah_sesi_per_hari" required
                                        class="w-full px-3 py-2 border-2 border-black rounded-lg bg-white text-xs font-bold text-black shadow-[2px_2px_0px_0px_#000] focus:outline-none">
                                    @for($s = 1; $s <= 4; $s++)
                                        <option value="{{ $s }}" {{ ($isAtasSubmitted ? old('jumlah_sesi_per_hari') : ($configAtas?->jumlah_sesi_per_hari ?? 2)) == $s ? 'selected' : '' }}>
                                            {{ $s }} Sesi / Hari
                                        </option>
                                    @endfor
                                </select>
                            </div>
                            <div>
                                <label for="atas_sesi_hari_terakhir" class="block font-bold text-black mb-1">Sesi pada Hari Terakhir</label>
                                <select id="atas_sesi_hari_terakhir" name="sesi_hari_terakhir" required
                                        class="w-full px-3 py-2 border-2 border-black rounded-lg bg-white text-xs font-bold text-black shadow-[2px_2px_0px_0px_#000] focus:outline-none">
                                    @for($s = 1; $s <= 4; $s++)
                                        <option value="{{ $s }}" {{ ($isAtasSubmitted ? old('sesi_hari_terakhir') : ($configAtas?->sesi_hari_terakhir ?? 1)) == $s ? 'selected' : '' }}>
                                            {{ $s }} Sesi (Hari Terakhir)
                                        </option>
                                    @endfor
                                </select>
                            </div>
                        </div>

                        {{-- Pengaturan Jam Sesi Dinamis Atas --}}
                        <div>
                            <div class="flex items-center justify-between mb-1.5">
                                <label class="font-bold text-black">Alokasi Waktu Jam Ujian per Sesi</label>
                                <span class="text-[10px] text-slate-500 font-normal">Format: JJ:MM</span>
                            </div>
                            <div id="container_jam_atas" class="space-y-2">
                                @php
                                    $currentCountAtas = (int) ($isAtasSubmitted ? old('jumlah_sesi_per_hari', 2) : ($configAtas?->jumlah_sesi_per_hari ?? 2));
                                    $jamAtasSaved = $isAtasSubmitted ? old('jam_sesi', []) : ($configAtas?->jam_sesi ?? []);
                                    $jamAtasMap = collect($jamAtasSaved)->keyBy('sesi')->all();
                                @endphp
                                @for($i = 1; $i <= 4; $i++)
                                    @php
                                        $rowMulai = $jamAtasMap[$i]['mulai'] ?? ($defaultJamAtasArray[$i]['mulai'] ?? '07:30');
                                        $rowSelesai = $jamAtasMap[$i]['selesai'] ?? ($defaultJamAtasArray[$i]['selesai'] ?? '09:30');
                                    @endphp
                                    <div class="session-row-atas flex items-center gap-2 p-2 bg-slate-50 border border-black rounded-lg {{ $i > $currentCountAtas ? 'hidden' : '' }}" data-sesi="{{ $i }}">
                                        <span class="w-16 text-xs font-black text-black">Sesi {{ $i }}</span>
                                        <input type="hidden" name="jam_sesi[{{ $i - 1 }}][sesi]" value="{{ $i }}">
                                        <div class="flex items-center gap-1.5 flex-1">
                                            <input type="text" name="jam_sesi[{{ $i - 1 }}][mulai]" value="{{ $rowMulai }}"
                                                   placeholder="07:30" maxlength="5"
                                                   class="w-20 px-2 py-1 text-center font-mono font-bold border-2 border-black rounded bg-white text-xs shadow-[1.5px_1.5px_0px_0px_#000]">
                                            <span class="font-black text-black">s.d.</span>
                                            <input type="text" name="jam_sesi[{{ $i - 1 }}][selesai]" value="{{ $rowSelesai }}"
                                                   placeholder="09:30" maxlength="5"
                                                   class="w-20 px-2 py-1 text-center font-mono font-bold border-2 border-black rounded bg-white text-xs shadow-[1.5px_1.5px_0px_0px_#000]">
                                        </div>
                                    </div>
                                @endfor
                            </div>
                        </div>

                        {{-- Live Preview Box Atas --}}
                        <div class="p-3 bg-blue-50/70 border-2 border-black rounded-lg space-y-2" aria-live="polite">
                            <div class="flex items-center justify-between">
                                <span class="text-xs font-black uppercase text-black flex items-center gap-1.5">
                                    <span>📊</span>
                                    <span>Kalkulasi Kalender:</span>
                                </span>
                                <span id="badge_preview_atas" class="px-2.5 py-0.5 bg-[#4DABF7] text-black border border-black rounded-md font-black text-xs shadow-[1.5px_1.5px_0px_0px_#000]">
                                    {{ $previewAtas['hari_efektif'] ?? 0 }} Hari Efektif • {{ $previewAtas['total_slot'] ?? 0 }} Slot Sesi
                                </span>
                            </div>
                            <div id="chips_preview_atas" class="flex flex-wrap gap-1.5">
                                @if(!empty($previewAtas['tanggal']))
                                    @foreach($previewAtas['tanggal'] as $t)
                                        <span class="px-2 py-0.5 bg-white border border-black rounded text-[10px] font-bold text-slate-800 shadow-[1px_1px_0px_0px_#000]">
                                            {{ $t['nama_hari'] }}, {{ $t['label'] }}
                                        </span>
                                    @endforeach
                                @else
                                    <span class="text-[11px] text-slate-500 italic">Preview akan muncul otomatis saat rentang tanggal valid.</span>
                                @endif
                            </div>
                        </div>

                        {{-- Tombol Aksi Atas --}}
                        <div class="pt-2 flex flex-wrap items-center justify-between gap-2 border-t border-slate-200">
                            <a href="{{ route('ujian.jadwal.template', ['periode_id' => $selectedPeriode->id, 'kelompok' => 'atas']) }}"
                               class="px-3 py-2 bg-white hover:bg-slate-100 text-black border-2 border-black rounded-lg font-bold text-xs shadow-[2px_2px_0px_0px_#000] flex items-center gap-1.5">
                                <span>📥</span>
                                <span>Unduh Template Atas</span>
                            </a>
                            <button type="submit"
                                    class="px-4 py-2 bg-[#20C997] hover:bg-emerald-400 text-black border-2 border-black rounded-lg font-black text-xs shadow-[2px_2px_0px_0px_#000] cursor-pointer">
                                💾 Simpan Pemetaan Atas
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
</div>
