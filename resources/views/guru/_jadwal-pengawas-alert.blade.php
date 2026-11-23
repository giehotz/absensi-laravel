@if(!empty($activeExamPeriode) && ($totalExamDutiesCount ?? 0) > 0)
    <div class="bg-[#FFF3BF] border-3 border-black p-4 sm:p-5 neo-box text-black flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div class="flex items-start gap-3.5">
            <div class="w-11 h-11 bg-[#5294FF] text-white border-2 border-black flex items-center justify-center text-xl shrink-0 shadow-[2px_2px_0px_0px_#000]">
                📋
            </div>
            <div>
                <div class="flex items-center gap-2 flex-wrap">
                    <span class="text-[10px] font-black uppercase tracking-wider bg-black text-[#FFD43B] px-2 py-0.5 rounded border border-black shadow-[1px_1px_0px_0px_#000]">
                        PENUGASAN UJIAN
                    </span>
                    <span class="text-xs font-bold text-slate-700">
                        {{ $activeExamPeriode->nama_periode }}
                    </span>
                    @if(($examDutiesTodayCount ?? 0) > 0)
                        <span class="text-[10px] font-black bg-[#FF6B6B] text-white px-2 py-0.5 rounded border border-black animate-pulse shadow-[1px_1px_0px_0px_#000]">
                            ⚡ {{ $examDutiesTodayCount }} Sesi Hari Ini
                        </span>
                    @endif
                </div>
                <h4 class="font-heading font-black text-sm sm:text-base text-black mt-1">
                    Anda memiliki {{ $totalExamDutiesCount }} jadwal mengawas ujian pada periode ini
                </h4>
                <p class="text-xs font-semibold text-slate-700 mt-0.5">
                    Silakan periksa kelas, ruangan, sesi waktu, dan rekan pengawas tugas Anda.
                </p>
            </div>
        </div>

        <div class="flex items-center gap-2 w-full sm:w-auto shrink-0">
            <a href="{{ route('guru.pengawas.index', ['periode_id' => $activeExamPeriode->id]) }}" 
               class="w-full sm:w-auto px-4 py-2 bg-black hover:bg-slate-800 text-yellow-300 border-2 border-black font-heading font-black text-xs uppercase tracking-wider text-center shadow-[2px_2px_0px_0px_#000] hover:translate-x-0.5 hover:translate-y-0.5 transition-all">
                Lihat Jadwal Mengawas →
            </a>
        </div>
    </div>
@endif
