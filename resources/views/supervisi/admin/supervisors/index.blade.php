@extends('layouts.supervisi')
@section('title', 'Tim Supervisor')
@section('page_title', 'Manajemen Tim Supervisor')

@section('content')
<div class="bg-white border-2 border-black rounded-xl p-5 shadow-[4px_4px_0px_0px_#000] space-y-4">
    <h2 class="font-heading font-black text-sm">Tambah Guru ke Tim Supervisor</h2>
    <form action="{{ route('supervisi.admin.supervisors.store') }}" method="POST" class="grid grid-cols-1 md:grid-cols-5 gap-3 items-end">
        @csrf
        <div>
            <label for="teacher_id" class="block text-xs font-black mb-1">Pilih Guru</label>
            <select id="teacher_id" name="teacher_id" required class="w-full px-3 py-2 text-xs border-2 border-black rounded-lg bg-white">
                <option value="">-- Pilih Guru --</option>
                @foreach($availableTeachers as $t)
                    <option value="{{ $t->id }}">{{ $t->user?->name }} ({{ $t->nip ?: 'Tanpa NIP' }})</option>
                @endforeach
            </select>
        </div>
        <div>
            <label for="jabatan" class="block text-xs font-black mb-1">Jabatan / Peran</label>
            <input id="jabatan" type="text" name="jabatan" value="Tim Supervisor" class="w-full px-3 py-2 text-xs border-2 border-black rounded-lg">
        </div>
        <div>
            <label for="nomor_sk" class="block text-xs font-black mb-1">Nomor SK Kepala Madrasah</label>
            <input id="nomor_sk" type="text" name="nomor_sk" placeholder="Contoh: 421/SK-SUP/2026" class="w-full px-3 py-2 text-xs border-2 border-black rounded-lg">
        </div>
        <div>
            <label for="tanggal_penugasan" class="block text-xs font-black mb-1">Tanggal Penugasan</label>
            <input id="tanggal_penugasan" type="date" name="tanggal_penugasan" value="{{ now()->toDateString() }}" class="w-full px-3 py-2 text-xs border-2 border-black rounded-lg">
        </div>
        <button type="submit" class="px-4 py-2 bg-[#FFD43B] text-black font-black text-xs rounded-lg border-2 border-black shadow-[2px_2px_0px_0px_#000] cursor-pointer">
            + Angkat Supervisor
        </button>
    </form>
</div>

<div class="bg-white border-2 border-black rounded-xl p-5 shadow-[4px_4px_0px_0px_#000] overflow-x-auto">
    <table class="w-full text-xs border-collapse border-2 border-black">
        <thead class="bg-slate-900 text-white">
            <tr>
                <th class="p-2.5 border border-black text-left">Nama Supervisor</th>
                <th class="p-2.5 border border-black text-left">NIP</th>
                <th class="p-2.5 border border-black text-left">Jabatan &amp; Nomor SK</th>
                <th class="p-2.5 border border-black text-center">Jumlah Jadwal</th>
                <th class="p-2.5 border border-black text-center">Status</th>
                <th class="p-2.5 border border-black text-center">Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse($supervisors as $sup)
                @php
                    $isHeadmaster = $sup->isHeadmaster($headmasterTeacherId ?? null);
                @endphp
                <tr class="odd:bg-white even:bg-slate-50">
                    <td class="p-2.5 border border-black font-black">
                        <div>{{ $sup->teacher?->user?->name }}</div>
                        @if($isHeadmaster)
                            <span class="mt-1 inline-block px-2 py-0.5 rounded border border-black bg-[#FFD43B] text-[10px] font-black">
                                🎓 Kepala Madrasah (Otomatis)
                            </span>
                        @endif
                    </td>
                    <td class="p-2.5 border border-black">{{ $sup->teacher?->nip ?: '-' }}</td>
                    <td class="p-2.5 border border-black">
                        <div class="font-bold">{{ $sup->jabatan ?: '-' }}</div>
                        <div class="text-[11px] font-mono text-slate-600">SK: {{ $sup->nomor_sk ?: '-' }}</div>
                    </td>
                    <td class="p-2.5 border border-black text-center font-black tabular-nums">{{ $sup->jadwals_count }}</td>
                    <td class="p-2.5 border border-black text-center">
                        <span class="px-2 py-0.5 rounded border border-black text-[11px] font-black {{ $sup->is_active ? 'bg-emerald-200' : 'bg-slate-200' }}">
                            {{ $sup->is_active ? 'Aktif' : 'Nonaktif' }}
                        </span>
                    </td>
                    <td class="p-2.5 border border-black text-center">
                        <div class="inline-flex gap-1.5">
                            <form action="{{ route('supervisi.admin.supervisors.toggle', $sup) }}" method="POST">
                                @csrf
                                <button type="submit" class="px-2.5 py-1 bg-amber-100 border border-black rounded font-bold cursor-pointer">
                                    {{ $sup->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                                </button>
                            </form>
                            @if(! $isHeadmaster)
                                <form action="{{ route('supervisi.admin.supervisors.destroy', $sup) }}" method="POST">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="px-2.5 py-1 bg-rose-200 border border-black rounded font-bold cursor-pointer">Hapus</button>
                                </form>
                            @endif
                        </div>
                    </td>
                </tr>
            @empty
                <tr><td colspan="6" class="p-4 text-center text-slate-500">Belum ada guru yang ditunjuk sebagai Supervisor.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
