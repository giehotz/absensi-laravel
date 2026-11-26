@extends('layouts.supervisi')
@section('title', 'Formulir C & D: Tindak Lanjut')
@section('page_title', 'Formulir C & D: Tindak Lanjut & Refleksi')

@section('content')
@php $tl = $jadwal->tindakLanjut; @endphp

<div class="bg-white border-2 border-black rounded-xl p-5 shadow-[4px_4px_0px_0px_#000] space-y-4">
    <h2 class="font-heading font-black text-base">Formulir C: Rencana Tindak Lanjut Pasca Observasi</h2>
    <form action="{{ route('supervisi.guru.tindak-lanjut.save-c', $jadwal) }}" method="POST" class="space-y-3">
        @csrf
        <div>
            <label class="block text-xs font-black mb-1">Refleksi Guru atas Hasil Observasi</label>
            <textarea name="refleksi_guru" rows="3" required class="w-full px-3 py-2 text-xs border-2 border-black rounded-lg">{{ old('refleksi_guru', $tl?->refleksi_guru) }}</textarea>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
            <div>
                <label class="block text-xs font-black mb-1">Tujuan Tindak Lanjut</label>
                <textarea name="tujuan_tindak_lanjut" rows="2" required class="w-full px-3 py-2 text-xs border-2 border-black rounded-lg">{{ old('tujuan_tindak_lanjut', $tl?->tujuan_tindak_lanjut) }}</textarea>
            </div>
            <div>
                <label class="block text-xs font-black mb-1">Upaya Tindak Lanjut</label>
                <textarea name="upaya_tindak_lanjut" rows="2" required class="w-full px-3 py-2 text-xs border-2 border-black rounded-lg">{{ old('upaya_tindak_lanjut', $tl?->upaya_tindak_lanjut) }}</textarea>
            </div>
            <div>
                <label class="block text-xs font-black mb-1">Pilihan Belajar PMM</label>
                <input type="text" name="pilihan_belajar_pmm" value="{{ old('pilihan_belajar_pmm', $tl?->pilihan_belajar_pmm) }}" class="w-full px-3 py-2 text-xs border-2 border-black rounded-lg">
            </div>
            <div>
                <label class="block text-xs font-black mb-1">Periode Pelaksanaan</label>
                <input type="text" name="periode_tindak_lanjut" value="{{ old('periode_tindak_lanjut', $tl?->periode_tindak_lanjut) }}" placeholder="Contoh: 2 minggu ke depan" class="w-full px-3 py-2 text-xs border-2 border-black rounded-lg">
            </div>
            <div>
                <label class="block text-xs font-black mb-1">Kebutuhan Dukungan (Kombel/Sekolah)</label>
                <input type="text" name="kebutuhan_dukungan" value="{{ old('kebutuhan_dukungan', $tl?->kebutuhan_dukungan) }}" placeholder="Contoh: Sumber daya pembelajaran / Coaching rekan" class="w-full px-3 py-2 text-xs border-2 border-black rounded-lg">
            </div>
            <div>
                <label class="block text-xs font-black mb-1">Detail Dukungan</label>
                <input type="text" name="detail_dukungan" value="{{ old('detail_dukungan', $tl?->detail_dukungan) }}" class="w-full px-3 py-2 text-xs border-2 border-black rounded-lg">
            </div>
        </div>
        <div class="flex justify-end">
            <button type="submit" class="px-4 py-2 bg-[#FFD43B] text-black text-xs font-black rounded-lg border-2 border-black cursor-pointer">Simpan Formulir C</button>
        </div>
    </form>
</div>

@if($tl)
<div class="bg-white border-2 border-black rounded-xl p-5 shadow-[4px_4px_0px_0px_#000] space-y-4">
    <h2 class="font-heading font-black text-base">Formulir D: Refleksi Tindak Lanjut</h2>
    <form action="{{ route('supervisi.guru.tindak-lanjut.save-d', $jadwal) }}" method="POST" class="grid grid-cols-1 md:grid-cols-2 gap-3">
        @csrf
        <div>
            <label class="block text-xs font-black mb-1">Inspirasi Baru yang Didapat</label>
            <textarea name="inspirasi_baru" rows="2" required class="w-full px-3 py-2 text-xs border-2 border-black rounded-lg">{{ old('inspirasi_baru', $tl->inspirasi_baru) }}</textarea>
        </div>
        <div>
            <label class="block text-xs font-black mb-1">Perubahan Praktik di Kelas</label>
            <textarea name="perubahan_praktik" rows="2" required class="w-full px-3 py-2 text-xs border-2 border-black rounded-lg">{{ old('perubahan_praktik', $tl->perubahan_praktik) }}</textarea>
        </div>
        <div>
            <label class="block text-xs font-black mb-1">Kendala Pelaksanaan</label>
            <textarea name="kendala_tindak_lanjut" rows="2" class="w-full px-3 py-2 text-xs border-2 border-black rounded-lg">{{ old('kendala_tindak_lanjut', $tl->kendala_tindak_lanjut) }}</textarea>
        </div>
        <div>
            <label class="block text-xs font-black mb-1">Rencana Perbaikan Berkelanjutan</label>
            <textarea name="rencana_perbaikan_berkelanjutan" rows="2" class="w-full px-3 py-2 text-xs border-2 border-black rounded-lg">{{ old('rencana_perbaikan_berkelanjutan', $tl->rencana_perbaikan_berkelanjutan) }}</textarea>
        </div>
        <div class="md:col-span-2 flex justify-end">
            <button type="submit" class="px-5 py-2 bg-[#20C997] text-black text-xs font-black rounded-lg border-2 border-black cursor-pointer">Simpan Formulir D</button>
        </div>
    </form>
</div>
@endif
@endsection
