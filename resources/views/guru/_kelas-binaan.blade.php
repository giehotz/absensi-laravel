<!-- Kelas Binaan (Wali Kelas) -->
<div id="kelas-binaan" class="space-y-4">
    <div class="flex items-center justify-between flex-wrap gap-2">
        <h2 class="font-heading font-black text-xl text-black flex items-center gap-2">
            <span>🛡</span> Siswa Kelas Binaan (Wali Kelas)
        </h2>
        <div class="flex items-center gap-2">
            @if($homeroomClasses->isNotEmpty())
                <span class="neo-badge bg-[#20C997] text-white">WALI KELAS AKTIF</span>
            @endif
            <a href="{{ route('guru.classes.binaan') }}" class="neo-btn bg-white text-black text-xs font-bold px-3 py-1.5 flex items-center gap-1.5 hover:bg-slate-100">
                Buka Halaman Penuh →
            </a>
        </div>
    </div>

    @forelse($homeroomClasses as $hClass)
    <div class="bg-white neo-box p-5 space-y-4">
        <div class="flex items-center justify-between border-b-2 border-black pb-3">
            <div>
                <h3 class="font-heading font-black text-lg text-black">{{ $hClass->name }}</h3>
                <p class="text-xs text-slate-500 font-semibold">Tingkat {{ $hClass->level }} • Jurusan: {{ $hClass->major ?? 'Umum' }}</p>
            </div>
            <span class="text-xs font-bold bg-[#D3F9D8] border-2 border-black px-2.5 py-1">
                {{ $hClass->students->count() }} Siswa Terdaftar
            </span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-slate-100 border-b-2 border-black text-xs font-black uppercase">
                    <tr>
                        <th class="p-2.5 border-r border-black">NIS</th>
                        <th class="p-2.5 border-r border-black">Nama Siswa</th>
                        <th class="p-2.5 border-r border-black">L/P</th>
                        <th class="p-2.5 text-center">Presensi Hari Ini</th>
                    </tr>
                </thead>
                <tbody class="divide-y-2 divide-black">
                    @forelse($hClass->students as $stu)
                    @php
                        $att = $stu->attendances->first();
                    @endphp
                    <tr class="hover:bg-slate-50 font-medium text-xs">
                        <td class="p-2.5 font-mono font-bold border-r border-black">{{ $stu->nis }}</td>
                        <td class="p-2.5 font-bold text-black border-r border-black">{{ $stu->user->name ?? '-' }}</td>
                        <td class="p-2.5 border-r border-black text-center font-bold">{{ $stu->gender }}</td>
                        <td class="p-2.5 text-center">
                            @if($att)
                                <span class="neo-badge text-[10px]
                                    @if($att->status === 'hadir') bg-[#20C997] text-white 
                                    @elseif($att->status === 'terlambat') bg-[#FFD43B] text-black 
                                    @elseif($att->status === 'sakit') bg-[#74C0FC] text-blue-900 
                                    @elseif($att->status === 'izin') bg-[#A5D8FF] text-blue-900 
                                    @else bg-[#FF6B6B] text-white @endif">
                                    {{ strtoupper($att->status) }}
                                </span>
                            @else
                                <span class="neo-badge bg-slate-200 text-slate-700 text-[10px]">Belum Hadir</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="p-4 text-center text-slate-500">Belum ada data siswa di kelas ini.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @empty
    <div class="bg-white neo-box p-8 text-center text-slate-500 font-semibold space-y-1">
        <div class="text-2xl">📋</div>
        <div>Anda saat ini tidak terdaftar sebagai wali kelas untuk kelas manapun.</div>
        <div class="text-xs text-slate-400">Hubungi administrator jika Anda ditugaskan menjadi wali kelas.</div>
    </div>
    @endforelse
</div>
