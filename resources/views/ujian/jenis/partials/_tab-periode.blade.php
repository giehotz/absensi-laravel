{{-- 1. Daftar Periode Pelaksanaan Ujian --}}
<div class="p-5 bg-white border-4 border-black rounded-xl shadow-[4px_4px_0px_0px_#000]">
    <div class="pb-3 border-b-2 border-black flex items-center justify-between">
        <h2 class="text-base font-black font-heading text-black flex items-center gap-2">
            <span>🗓️</span>
            <span>Daftar Periode Pelaksanaan Ujian</span>
        </h2>
        <span class="text-xs font-bold text-slate-500">Total: {{ $periodes->count() }} Periode</span>
    </div>

    <div class="mt-4 overflow-x-auto">
        <table class="w-full text-xs text-left border-collapse border-2 border-black">
            <thead class="bg-slate-100 uppercase font-black text-black border-b-2 border-black">
                <tr>
                    <th class="p-2.5 border-r border-black w-12 text-center">No</th>
                    <th class="p-2.5 border-r border-black">Nama Periode Ujian</th>
                    <th class="p-2.5 border-r border-black">Jenis Asesmen</th>
                    <th class="p-2.5 border-r border-black text-center">Tahun Ajaran / Sem</th>
                    <th class="p-2.5 border-r border-black text-center">Rentang Tanggal</th>
                    <th class="p-2.5 border-r border-black text-center">Status</th>
                    <th class="p-2.5 text-center w-36">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-black font-medium">
                @forelse($periodes as $idx => $p)
                    <tr class="hover:bg-amber-50/50 {{ $p->is_active ? 'bg-emerald-50/60 font-semibold' : '' }}">
                        <td class="p-2.5 border-r border-black text-center font-bold">{{ $idx + 1 }}</td>
                        <td class="p-2.5 border-r border-black font-bold text-black">
                            {{ $p->nama_periode }}
                        </td>
                        <td class="p-2.5 border-r border-black">
                            <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-slate-100 border border-black">
                                {{ $p->jenis?->nama_jenis }}
                            </span>
                        </td>
                        <td class="p-2.5 border-r border-black text-center font-mono">
                            {{ $p->tahun_pelajaran }} ({{ $p->semester }})
                        </td>
                        <td class="p-2.5 border-r border-black text-center font-mono text-[11px]">
                            {{ $p->tanggal_mulai ? $p->tanggal_mulai->format('d/m/Y') : '-' }} s.d. {{ $p->tanggal_selesai ? $p->tanggal_selesai->format('d/m/Y') : '-' }}
                        </td>
                        <td class="p-2.5 border-r border-black text-center">
                            @if($p->is_active)
                                <span class="px-2 py-0.5 rounded text-[10px] font-black bg-[#20C997] text-white border border-black shadow-[1px_1px_0px_0px_#000]">
                                    AKTIF
                                </span>
                            @else
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-slate-200 text-slate-700 border border-slate-400">
                                    Nonaktif
                                </span>
                            @endif
                        </td>
                        <td class="p-2.5 text-center flex items-center justify-center gap-1.5">
                            @if(! $p->is_active)
                                <form action="{{ route('ujian.periode.activate', $p) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="px-2 py-1 bg-[#20C997] hover:bg-emerald-400 text-white rounded text-[10px] font-black border border-black shadow-[1px_1px_0px_0px_#000] cursor-pointer" title="Aktifkan Periode">
                                        Aktifkan
                                    </button>
                                </form>
                            @endif
                            <button type="button"
                                onclick="openEditPeriodeModal({{ json_encode([
                                    'id' => $p->id,
                                    'update_url' => route('ujian.periode.update', $p),
                                    'ujian_jenis_id' => $p->ujian_jenis_id,
                                    'nama_periode' => $p->nama_periode,
                                    'tahun_pelajaran' => $p->tahun_pelajaran,
                                    'semester' => $p->semester,
                                    'tanggal_mulai' => $p->tanggal_mulai ? $p->tanggal_mulai->format('Y-m-d') : '',
                                    'tanggal_selesai' => $p->tanggal_selesai ? $p->tanggal_selesai->format('Y-m-d') : '',
                                    'tempat_cetak' => $p->tempat_cetak ?? 'Tanggamus',
                                    'tanggal_cetak_kartu' => $p->tanggal_cetak_kartu ? $p->tanggal_cetak_kartu->format('Y-m-d') : '',
                                ]) }})"
                                class="px-2 py-1 bg-[#4DABF7] hover:bg-blue-400 text-white rounded text-[10px] font-black border border-black shadow-[1px_1px_0px_0px_#000] cursor-pointer inline-flex items-center gap-1"
                                title="Edit Periode">
                                <span>✏️</span>
                                <span>Edit</span>
                            </button>
                            <a href="{{ route('ujian.kartu.index', ['periode_id' => $p->id]) }}" class="px-2 py-1 bg-[#FFD43B] text-black rounded text-[10px] font-black border border-black shadow-[1px_1px_0px_0px_#000]">
                                Peserta
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="p-6 text-center text-slate-500 italic">Belum ada periode ujian yang dibuat.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
