@if($packages->isEmpty())
    <div class="bg-white border-2 border-black p-12 text-center rounded-lg shadow-[4px_4px_0px_0px_#000]">
        <div class="w-16 h-16 bg-[#FFF3BF] border-2 border-black rounded-full flex items-center justify-center mx-auto mb-3">
            <svg class="w-8 h-8 text-amber-900" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
        </div>
        <h3 class="font-heading font-black text-lg text-black">Belum Ada Paket Asesmen STS</h3>
        <p class="text-xs text-slate-600 max-w-md mx-auto mt-1 mb-4">
            Kelola evaluasi capaian belajar tengah semester (setelah 2-3 bulan KBM) untuk memetakan pemahaman materi siswa.
        </p>
        <a href="{{ route('guru.penilaian.create', ['type' => 'sts']) }}" class="neo-btn bg-[#FFD43B] text-black px-4 py-2 text-xs font-black inline-flex items-center gap-1.5 border-2 border-black rounded shadow-[2px_2px_0px_0px_#000]">
            + Buat Asesmen STS Sekarang
        </a>
    </div>
@else
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
        @foreach($packages as $pkg)
            @php
                $isOwner = $pkg->teacher_id === $teacher->id;
                $isHomeroom = $pkg->schoolClass->homeroom_teacher_id === $teacher->id;
            @endphp
            <div class="bg-white border-2 border-black rounded-lg shadow-[4px_4px_0px_0px_#000] flex flex-col justify-between overflow-hidden">
                <div class="p-5">
                    <div class="flex items-center justify-between gap-2 mb-2">
                        <div class="flex items-center gap-1.5">
                            <span class="text-[10px] font-black uppercase tracking-wider px-2 py-0.5 rounded border border-black bg-[#FFF3BF] text-amber-950">
                                STS
                            </span>
                            <span class="text-[10px] font-black uppercase tracking-wider px-2 py-0.5 rounded border border-black {{ $pkg->isLocked() ? 'bg-[#D3F9D8] text-emerald-950' : 'bg-slate-100 text-slate-700' }}">
                                {{ $pkg->isLocked() ? 'FINAL' : 'DRAFT' }}
                            </span>
                        </div>
                        <span class="text-[10px] font-bold text-slate-500">
                            KKTP: <strong class="text-black">{{ $pkg->kktp_default }}</strong>
                        </span>
                    </div>

                    <h3 class="font-heading font-black text-base text-black mb-1 hover:text-[#5294FF] transition-colors">
                        <a href="{{ route('guru.penilaian.show', $pkg) }}">
                            {{ $pkg->title }}
                        </a>
                    </h3>

                    <div class="space-y-1.5 text-xs text-slate-700 mt-3 pt-3 border-t-2 border-slate-100">
                        <div class="flex items-center justify-between">
                            <span class="text-slate-500 font-medium">Kelas / Rombel:</span>
                            <span class="font-black text-black bg-slate-100 px-1.5 py-0.5 rounded border border-slate-300">{{ $pkg->schoolClass->name }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-slate-500 font-medium">Mata Pelajaran:</span>
                            <span class="font-bold text-black">{{ $pkg->subject->name }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-slate-500 font-medium">Tahun Ajaran:</span>
                            <span class="text-slate-800">{{ $pkg->academicYear->name }} ({{ ucfirst($pkg->academicYear->semester) }})</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-slate-500 font-medium">Guru Pengampu:</span>
                            <span class="font-bold text-slate-900">{{ $pkg->teacher->user?->name ?? 'Guru' }}</span>
                        </div>
                    </div>

                    @if($isHomeroom && ! $isOwner)
                        <div class="mt-3 p-2 bg-[#E7F5FF] border border-blue-400 rounded text-[11px] font-bold text-blue-950 flex items-center gap-1.5">
                            <svg class="w-3.5 h-3.5 shrink-0 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                            </svg>
                            <span>Akses Wali Kelas (Mode Pantau & Rekap)</span>
                        </div>
                    @endif
                </div>

                <div class="bg-slate-50 border-t-2 border-black p-3.5 flex items-center justify-between gap-2">
                    <a href="{{ route('guru.penilaian.template', $pkg) }}" 
                       class="text-xs font-bold text-slate-700 hover:text-black flex items-center gap-1 py-1 px-2 rounded hover:bg-slate-200 transition-colors"
                       title="Unduh Template Excel Evaluasi STS">
                        <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                        </svg>
                        <span>Template STS</span>
                    </a>

                    <div class="flex items-center gap-1.5">
                        @if($isOwner)
                            @if(! $pkg->isLocked())
                                <form action="{{ route('guru.penilaian.destroy', $pkg) }}" method="POST"
                                      onsubmit="return confirm('Hapus paket asesmen STS \'{{ addslashes($pkg->title) }}\'?\n\nSeluruh data nilai STS di dalamnya akan ikut terhapus. Tindakan ini tidak dapat dibatalkan.');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" 
                                            class="neo-btn bg-[#FF6B6B] hover:bg-red-600 text-white p-1.5 text-xs font-bold flex items-center justify-center rounded border border-black shadow-[2px_2px_0px_0px_#000] cursor-pointer transition-colors"
                                            title="Hapus Asesmen STS">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
                                    </button>
                                </form>
                            @else
                                <button type="button" 
                                        onclick="showLockedModal('{{ addslashes($pkg->title) }}')"
                                        class="p-1.5 bg-slate-200 hover:bg-slate-300 text-slate-500 hover:text-black rounded border border-slate-400 shadow-[1px_1px_0px_0px_#94a3b8] cursor-pointer flex items-center justify-center transition-colors"
                                        title="Paket Terkunci (Klik untuk info cara menghapus)">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                    </svg>
                                </button>
                            @endif
                        @endif

                        <a href="{{ route('guru.penilaian.show', $pkg) }}" 
                           class="neo-btn bg-[#FFD43B] hover:bg-[#fcc419] text-black px-3.5 py-1.5 text-xs font-black flex items-center gap-1 rounded border border-black shadow-[2px_2px_0px_0px_#000]">
                            <span>Buka Nilai</span>
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/>
                            </svg>
                        </a>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="mt-6">
        {{ $packages->links() }}
    </div>
@endif
