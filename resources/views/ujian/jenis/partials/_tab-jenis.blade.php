{{-- 2. Master Jenis Asesmen Kurikulum Merdeka --}}
<div class="p-5 bg-white border-4 border-black rounded-xl shadow-[4px_4px_0px_0px_#000]">
    <div class="pb-3 border-b-2 border-black flex items-center justify-between">
        <h2 class="text-base font-black font-heading text-black flex items-center gap-2">
            <span>📚</span>
            <span>Daftar Master Jenis Asesmen (Kurikulum Merdeka)</span>
        </h2>
        <span class="text-xs font-bold text-slate-500">Total: {{ $jenisList->count() }} Jenis</span>
    </div>

    <div class="mt-4 overflow-x-auto">
        <table class="w-full text-xs text-left border-collapse border-2 border-black">
            <thead class="bg-slate-100 uppercase font-black text-black border-b-2 border-black">
                <tr>
                    <th class="p-2.5 border-r border-black w-12 text-center">No</th>
                    <th class="p-2.5 border-r border-black w-24">Kode</th>
                    <th class="p-2.5 border-r border-black">Nama Jenis Asesmen</th>
                    <th class="p-2.5 border-r border-black">Kategori Kurikulum</th>
                    <th class="p-2.5 border-r border-black">Fase Sasaran</th>
                    <th class="p-2.5 border-r border-black">Teks Kop Kartu (Kop 2)</th>
                    <th class="p-2.5 border-r border-black text-center">Status</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-black font-medium">
                @foreach($jenisList as $idx => $j)
                    <tr class="hover:bg-amber-50/50">
                        <td class="p-2.5 border-r border-black text-center font-bold">{{ $idx + 1 }}</td>
                        <td class="p-2.5 border-r border-black font-mono font-bold">{{ $j->kode_jenis }}</td>
                        <td class="p-2.5 border-r border-black font-bold text-black">{{ $j->nama_jenis }}</td>
                        <td class="p-2.5 border-r border-black">{{ $j->kategori_kurikulum }}</td>
                        <td class="p-2.5 border-r border-black font-semibold text-slate-700">{{ $j->fase_sasaran }}</td>
                        <td class="p-2.5 border-r border-black text-[11px] font-bold text-slate-800">{{ $j->judul_kop_kartu ?: '-' }}</td>
                        <td class="p-2.5 border-r border-black text-center">
                            <span class="px-2 py-0.5 rounded text-[10px] font-bold {{ $j->status_aktif ? 'bg-emerald-100 text-emerald-950 border border-emerald-600' : 'bg-slate-100 text-slate-500' }}">
                                {{ $j->status_aktif ? 'Aktif' : 'Nonaktif' }}
                            </span>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
