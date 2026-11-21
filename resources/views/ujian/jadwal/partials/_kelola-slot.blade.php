@if($activePeriode)
{{-- Peta Kesiapan Slot per Rombel/Tingkat --}}
<div id="kelolaSlotSection" class="p-5 bg-white border-4 border-black rounded-xl shadow-[4px_4px_0px_0px_#000]">
    <div class="flex flex-col lg:flex-row lg:items-start lg:justify-between gap-4 pb-4 border-b-2 border-black">
        <div>
            <div class="flex flex-wrap items-center gap-2 mb-1">
                <span class="text-xs font-black uppercase tracking-wider bg-[#D0EBFF] text-black px-2 py-0.5 rounded border border-black shadow-[1px_1px_0px_0px_#000]">
                    Peta Mapel
                </span>
                <span class="text-xs font-bold text-slate-500">{{ $subjects->count() }} mapel terdaftar</span>
                @if($subjects->count() < 16)
                    <span class="text-[10px] font-bold text-amber-900 bg-amber-100 border border-amber-500 px-2 py-0.5 rounded">
                        {{ 16 - $subjects->count() }} mapel belum ada di master
                    </span>
                @endif
            </div>
            <h2 class="text-lg font-black font-heading text-black">Kelola Slot per Rombel/Tingkat</h2>
            <p class="text-xs font-semibold text-slate-600 mt-0.5">
                Pilih tingkat atau rombel, lalu atur slot untuk setiap mata pelajaran yang sudah terdaftar.
            </p>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 shrink-0">
            <div>
                <label for="coverage_mode" class="block text-[10px] font-black uppercase tracking-wide text-slate-600 mb-1">Tampilan</label>
                <select id="coverage_mode" onchange="updateCoverageScopeOptions()"
                        class="w-full px-3 py-2 text-xs font-bold border-2 border-black rounded-lg bg-white shadow-[2px_2px_0px_0px_#000] focus:outline-none cursor-pointer">
                    <option value="tingkat">Per Tingkat</option>
                    <option value="rombel">Per Rombel</option>
                </select>
            </div>
            <div>
                <label for="coverage_scope" class="block text-[10px] font-black uppercase tracking-wide text-slate-600 mb-1">Pilih Scope</label>
                <select id="coverage_scope" onchange="renderCoverageGrid()"
                        class="w-full px-3 py-2 text-xs font-bold border-2 border-black rounded-lg bg-white shadow-[2px_2px_0px_0px_#000] focus:outline-none cursor-pointer"></select>
            </div>
        </div>
    </div>

    <div class="mt-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
        <div class="flex flex-wrap items-center gap-2">
            <span id="coverage_summary" class="px-2.5 py-1 text-[11px] font-black border-2 border-black rounded-lg bg-[#FFD43B] text-black shadow-[1.5px_1.5px_0px_0px_#000]">
                0/{{ $subjects->count() }} mapel terjadwal
            </span>
            <span id="coverage_unscheduled_badge" class="px-2.5 py-1 text-[11px] font-black border-2 border-black rounded-lg bg-slate-100 text-slate-700">
                -
            </span>
        </div>
        <label class="flex items-center gap-2 text-xs font-bold text-slate-700 cursor-pointer select-none">
            <input type="checkbox" id="coverage_only_unscheduled" onchange="renderCoverageGrid()" class="rounded border-black accent-black">
            <span>Hanya tampilkan yang belum terjadwal</span>
        </label>
    </div>

    <div id="coverageGrid" class="mt-3 grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-3"></div>

    <p class="mt-3 text-[10px] font-semibold text-slate-500">
        Slot mapel kustom tetap tampil di tabel slot jadwal di atas. Peta ini hanya menampilkan mapel yang terdaftar di master data.
    </p>
</div>
@endif
