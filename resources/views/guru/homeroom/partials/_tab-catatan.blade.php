{{-- TAB 4: Catatan Khusus Siswa --}}
<div id="tabContent-catatan" class="tab-pane hidden space-y-4">
    <div class="bg-white neo-box overflow-hidden">
        <div class="p-4 bg-slate-50 border-b-2 border-black flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3">
            <div>
                <h4 class="font-heading font-black text-sm text-black uppercase flex items-center gap-2">
                    <span>📌</span> Catatan Khusus & Pembinaan Siswa Binaan
                </h4>
                <p class="text-[11px] font-semibold text-slate-500">
                    Dokumentasikan perkembangan kedisiplinan, prestasi, bimbingan, kendala kesehatan, atau tindak lanjut dengan orang tua.
                </p>
            </div>
            <button type="button" onclick="openAddNoteModal()" 
                    class="neo-btn bg-[#20C997] text-black text-xs font-black px-4 py-2 flex items-center gap-1.5 hover:bg-emerald-400 cursor-pointer">
                <span>➕</span> Tambah Catatan Siswa
            </button>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-slate-100 border-b-2 border-black text-xs font-black uppercase text-black">
                    <tr>
                        <th class="p-3 w-12 text-center border-r-2 border-black">No</th>
                        <th class="p-3 border-r-2 border-black w-28">Tanggal</th>
                        <th class="p-3 border-r-2 border-black min-w-[180px]">Siswa</th>
                        <th class="p-3 text-center border-r-2 border-black w-32">Kategori</th>
                        <th class="p-3 border-r-2 border-black min-w-[220px]">Catatan Khusus</th>
                        <th class="p-3 border-r-2 border-black min-w-[180px]">Tindak Lanjut</th>
                        <th class="p-3 text-center w-24">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y-2 divide-black">
                    @forelse($studentNotes as $idx => $note)
                        <tr class="hover:bg-slate-50 text-xs">
                            <td class="p-3 text-center font-mono font-bold border-r-2 border-black">
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
                                <span class="neo-badge text-[10px] {{ $note->category_badge_class }}">
                                    {{ $note->category_label }}
                                </span>
                            </td>
                            <td class="p-3 border-r-2 border-black">
                                @if($note->title)
                                    <div class="font-black text-black text-xs mb-0.5">{{ $note->title }}</div>
                                @endif
                                <div class="text-slate-800 font-medium whitespace-pre-line">{{ $note->content }}</div>
                                <div class="text-[10px] text-slate-400 font-semibold mt-1">
                                    Oleh: {{ $note->teacher?->user?->name ?? 'Wali Kelas' }}
                                </div>
                            </td>
                            <td class="p-3 border-r-2 border-black">
                                @if($note->follow_up)
                                    <div class="inline-flex items-center gap-1 text-slate-800 font-semibold bg-amber-50 border border-amber-300 px-2 py-1 rounded-xs">
                                        <span>👉</span> {{ $note->follow_up }}
                                    </div>
                                @else
                                    <span class="text-slate-400 italic">Belum ada tindak lanjut</span>
                                @endif
                            </td>
                            <td class="p-3 text-center">
                                <form action="{{ route('guru.classes.binaan.notes.destroy', $note->id) }}" method="POST"
                                      onsubmit="return confirmAction(event, 'Hapus Catatan?', 'Catatan khusus untuk siswa ini akan dihapus permanen.')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" 
                                            class="neo-btn bg-[#FF6B6B] text-white text-[10px] font-bold px-2.5 py-1 hover:bg-rose-600 cursor-pointer">
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
    </div>
</div>
