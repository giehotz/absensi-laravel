{{-- TAB 4: Catatan Khusus Siswa --}}
<div id="tabContent-catatan" class="tab-pane hidden space-y-4">
    <div class="bg-white neo-box overflow-hidden">
        <div class="p-4 bg-slate-50 border-b-2 border-black flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3">
            <div>
                <h4 class="font-heading font-black text-sm text-black uppercase flex items-center gap-2">
                    <span>📌</span> Catatan Khusus & Pembinaan Siswa
                </h4>
                <p class="text-[11px] font-semibold text-slate-500">
                    Dokumentasikan kedisiplinan, prestasi, bimbingan konseling, kendala fisik, atau tindak lanjut dengan orang tua.
                </p>
            </div>
            <button type="button" onclick="openAddNoteModal()" 
                    class="neo-btn bg-[#20C997] hover:bg-emerald-400 text-black text-xs font-black px-4 py-2 flex items-center justify-center gap-1.5 cursor-pointer self-stretch sm:self-auto min-h-[42px] transition-colors">
                <span>➕</span> Tambah Catatan Siswa
            </button>
        </div>

        {{-- Desktop Table View (>= 768px) --}}
        <div class="hidden md:block overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-slate-100 border-b-2 border-black text-xs font-black uppercase text-black">
                    <tr>
                        <th class="p-3 w-12 text-center border-r-2 border-black">No</th>
                        <th class="p-3 border-r-2 border-black w-32">Tanggal</th>
                        <th class="p-3 border-r-2 border-black min-w-[180px]">Siswa</th>
                        <th class="p-3 text-center border-r-2 border-black w-36">Kategori</th>
                        <th class="p-3 border-r-2 border-black min-w-[240px]">Catatan Khusus</th>
                        <th class="p-3 border-r-2 border-black min-w-[200px]">Tindak Lanjut</th>
                        <th class="p-3 text-center w-24">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y-2 divide-black">
                    @forelse($studentNotes as $idx => $note)
                        <tr class="hover:bg-slate-50/80 transition-colors text-xs">
                            <td class="p-3 text-center font-mono font-bold border-r-2 border-black bg-slate-50/50">
                                {{ $idx + 1 }}
                            </td>
                            <td class="p-3 border-r-2 border-black font-mono font-bold text-slate-700">
                                {{ \Carbon\Carbon::parse($note->date)->translatedFormat('d M Y') }}
                            </td>
                            <td class="p-3 border-r-2 border-black">
                                <div class="font-bold text-black">{{ $note->student->user->name ?? '-' }}</div>
                                <div class="text-[10px] text-slate-500 font-mono">NIS: {{ $note->student->nis }}</div>
                            </td>
                            <td class="p-3 text-center border-r-2 border-black">
                                <span class="neo-badge text-[10px] font-black px-2.5 py-0.5 {{ $note->category_badge_class }}">
                                    {{ $note->category_label }}
                                </span>
                            </td>
                            <td class="p-3 border-r-2 border-black">
                                @if($note->title)
                                    <div class="font-black text-black text-xs mb-0.5">{{ $note->title }}</div>
                                @endif
                                <div class="text-slate-800 font-medium whitespace-pre-line leading-relaxed">{{ $note->content }}</div>
                                <div class="text-[10px] text-slate-500 font-semibold mt-1.5 flex items-center gap-1">
                                    <span>✍️</span> Oleh: {{ $note->teacher?->user?->name ?? 'Wali Kelas' }}
                                </div>
                            </td>
                            <td class="p-3 border-r-2 border-black">
                                @if($note->follow_up)
                                    <div class="inline-flex items-start gap-1.5 text-amber-950 font-semibold bg-amber-50 border border-amber-300 px-2 py-1 rounded-xs text-[11px]">
                                        <span>👉</span>
                                        <span>{{ $note->follow_up }}</span>
                                    </div>
                                @else
                                    <span class="text-slate-400 italic text-[11px]">Belum ada tindak lanjut</span>
                                @endif
                            </td>
                            <td class="p-3 text-center">
                                <form action="{{ route('guru.classes.binaan.notes.destroy', $note->id) }}" method="POST"
                                      onsubmit="return confirmAction(event, 'Hapus Catatan?', 'Catatan khusus untuk siswa ini akan dihapus permanen.')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" 
                                            class="neo-btn bg-[#FF6B6B] hover:bg-rose-500 text-white text-[10px] font-bold px-2.5 py-1.5 cursor-pointer flex items-center gap-1 mx-auto transition-colors">
                                        🗑 Hapus
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="p-8 text-center text-slate-500 font-semibold">
                                <div class="text-2xl mb-1">📝</div>
                                <div>Belum ada catatan khusus yang dibuat untuk siswa di kelas ini.</div>
                                <div class="text-xs text-slate-400 mt-1">Gunakan tombol <b>"Tambah Catatan Siswa"</b> di atas untuk mendokumentasikan catatan kedisiplinan, prestasi, atau pembinaan.</div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Mobile Adaptive Cards (< 768px) --}}
        <div class="block md:hidden divide-y-2 divide-black">
            @forelse($studentNotes as $idx => $note)
                <div class="p-4 space-y-3 bg-white hover:bg-slate-50/50 transition-colors">
                    {{-- Top Header Row: Date & Category --}}
                    <div class="flex items-center justify-between gap-2">
                        <div class="flex items-center gap-2">
                            <span class="w-6 h-6 rounded-full bg-black text-white font-mono text-[10px] font-black flex items-center justify-center shrink-0">
                                {{ $idx + 1 }}
                            </span>
                            <span class="font-mono text-xs font-bold text-slate-700">
                                📅 {{ \Carbon\Carbon::parse($note->date)->translatedFormat('d M Y') }}
                            </span>
                        </div>
                        <span class="neo-badge text-[10px] font-black px-2 py-0.5 {{ $note->category_badge_class }}">
                            {{ $note->category_label }}
                        </span>
                    </div>

                    {{-- Student Name & NIS --}}
                    <div class="bg-slate-50 border border-black/20 p-2.5 rounded-sm">
                        <div class="font-heading font-black text-xs text-black">{{ $note->student->user->name ?? '-' }}</div>
                        <div class="text-[10px] font-mono text-slate-500">NIS: {{ $note->student->nis }}</div>
                    </div>

                    {{-- Note Content --}}
                    <div class="space-y-1 text-xs">
                        @if($note->title)
                            <div class="font-heading font-black text-xs text-black">{{ $note->title }}</div>
                        @endif
                        <div class="text-amber-950 font-medium leading-relaxed whitespace-pre-line bg-amber-50 p-2.5 border border-amber-300 rounded-sm">
                            {{ $note->content }}
                        </div>
                        <div class="text-[10px] text-slate-500 font-semibold pt-1">
                            ✍️ Dicatat oleh: {{ $note->teacher?->user?->name ?? 'Wali Kelas' }}
                        </div>
                    </div>

                    {{-- Follow up if any --}}
                    @if($note->follow_up)
                        <div class="p-2.5 bg-blue-50 border border-blue-300 rounded-sm text-xs space-y-0.5">
                            <div class="font-black text-blue-950 flex items-center gap-1 text-[11px]">
                                <span>👉</span> Tindak Lanjut:
                            </div>
                            <div class="text-blue-950 font-medium text-[11px] leading-relaxed">
                                {{ $note->follow_up }}
                            </div>
                        </div>
                    @endif

                    {{-- Delete Action --}}
                    <div class="pt-1 flex justify-end">
                        <form action="{{ route('guru.classes.binaan.notes.destroy', $note->id) }}" method="POST"
                              onsubmit="return confirmAction(event, 'Hapus Catatan?', 'Catatan khusus untuk siswa ini akan dihapus permanen.')" class="w-full sm:w-auto">
                            @csrf
                            @method('DELETE')
                            <button type="submit" 
                                    class="w-full sm:w-auto neo-btn bg-[#FF6B6B] hover:bg-rose-500 text-white text-xs font-bold px-3 py-2 min-h-[42px] flex items-center justify-center gap-1.5 cursor-pointer transition-colors">
                                <span>🗑</span> Hapus Catatan
                            </button>
                        </form>
                    </div>
                </div>
            @empty
                <div class="p-8 text-center text-slate-500 font-semibold">
                    <div class="text-2xl mb-1">📝</div>
                    <div class="text-xs">Belum ada catatan khusus yang dibuat untuk siswa di kelas ini.</div>
                    <div class="text-[11px] text-slate-400 mt-1">Gunakan tombol "Tambah Catatan Siswa" di atas untuk menambahkan catatan baru.</div>
                </div>
            @endforelse
        </div>
    </div>
</div>
