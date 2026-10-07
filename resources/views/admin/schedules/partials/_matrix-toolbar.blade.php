@php
    $totalScheduledJp = $selectedClass->total_jp ?? 0;
    $targetJp = $selectedClass->target_jp ?? 38;
    $percentJp = $selectedClass->percent_jp ?? min(100, (int) round(($totalScheduledJp / max(1, $targetJp)) * 100));
    $shortageJp = $selectedClass->shortage_jp ?? max(0, $targetJp - $totalScheduledJp);
@endphp

<!-- Toolbar: Pemilihan Kelas & Kesesuaian Regulasi EMIS GTK -->
<div class="grid grid-cols-1 lg:grid-cols-12 gap-4">
    <!-- Selector Kelas & Info Wali -->
    <div class="lg:col-span-5 bg-white neo-box p-4 flex flex-col justify-between space-y-3">
        <form method="GET" action="{{ route('admin.schedules.index') }}" class="space-y-1.5">
            <label for="school_class_id" class="block text-xs font-black uppercase text-black flex items-center justify-between">
                <span>Pilih / Ganti Kelas:</span>
                <a href="{{ route('admin.schedules.index') }}" class="text-[11px] font-bold text-blue-900 underline hover:text-black">
                    ← Semua Kelas
                </a>
            </label>
            <div class="flex gap-2">
                <select name="school_class_id" id="school_class_id" onchange="this.form.submit()"
                        class="flex-1 bg-slate-50 border-2 border-black px-3 py-2 text-xs font-black text-black focus:outline-hidden rounded">
                    @foreach($classes as $cls)
                        <option value="{{ $cls->id }}" {{ $selectedClassId == $cls->id ? 'selected' : '' }}>
                            Kelas {{ $cls->name }} (Tingkat {{ $cls->level }})
                        </option>
                    @endforeach
                </select>
            </div>
        </form>

        <div class="flex items-center justify-between gap-2 pt-2 border-t border-slate-200 text-xs">
            <span class="text-slate-600 font-bold">Wali Kelas:</span>
            <span class="font-heading font-black text-slate-900 bg-[#E7F5FF] border border-black px-2 py-0.5 rounded truncate max-w-[240px]">
                👨‍🏫 {{ $selectedClass->homeroomTeacher->user->name ?? 'Belum Ditugaskan' }}
            </span>
        </div>
    </div>

    <!-- Kesesuaian Regulasi (Target JP vs Terjadwal) ala EMIS GTK -->
    <div class="lg:col-span-7 bg-white neo-box p-4 flex flex-col justify-between space-y-3">
        <div class="flex items-center justify-between gap-2">
            <div>
                <h4 class="font-heading font-black text-xs uppercase text-black flex items-center gap-1.5">
                    <span>📊</span> Kesesuaian Beban Regulasi KBM
                </h4>
                <p class="text-[11px] font-medium text-slate-600">
                    Target {{ $targetJp }} JP · Terjadwal <b>{{ $totalScheduledJp }} JP</b> · Kurang <b>{{ $shortageJp }} JP</b>
                </p>
            </div>
            <div>
                @if($shortageJp === 0)
                    <span class="neo-badge bg-[#20C997] text-black font-black text-[11px]">
                        ✅ Lengkap (100%)
                    </span>
                @else
                    <span class="neo-badge bg-[#FFF3BF] text-amber-950 font-black text-[11px]">
                        ⚠️ Kurang {{ $shortageJp }} JP ({{ $percentJp }}%)
                    </span>
                @endif
            </div>
        </div>

        <!-- Progress Bar -->
        <div class="space-y-1">
            <div class="w-full bg-slate-100 border-2 border-black h-4 rounded overflow-hidden p-0.5">
                <div class="h-full {{ $shortageJp === 0 ? 'bg-[#20C997]' : 'bg-[#5294FF]' }} transition-all duration-500 rounded-xs" 
                     style="width: {{ $percentJp }}%;"></div>
            </div>
            <div class="flex justify-between text-[10px] font-mono font-bold text-slate-500">
                <span>0 JP</span>
                <span>50%</span>
                <span>Target: {{ $targetJp }} JP</span>
            </div>
        </div>
    </div>
</div>
