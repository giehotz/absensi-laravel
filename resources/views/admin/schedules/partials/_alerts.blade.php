<!-- Alert / Status Notifikasi -->
@if(session('success'))
<div class="bg-[#D3F9D8] border-2 border-black p-4 neo-box text-emerald-950 font-bold text-xs flex items-center justify-between">
    <div class="flex items-center gap-2">
        <span>✅</span> {{ session('success') }}
    </div>
    <button type="button" onclick="this.parentElement.remove()" class="text-black font-black hover:opacity-75 cursor-pointer">✕</button>
</div>
@endif

@if(session('conflict_error'))
<div class="bg-[#FFE3E3] border-2 border-black p-4 neo-box text-rose-950 font-bold text-xs flex items-start gap-2.5">
    <span class="text-lg">⚠️</span>
    <div class="flex-1">
        <span class="font-black uppercase block mb-0.5">Jadwal Gagal Disimpan (Bentrok)</span>
        <span>{{ session('conflict_error') }}</span>
    </div>
    <button type="button" onclick="this.parentElement.remove()" class="text-black font-black hover:opacity-75 cursor-pointer">✕</button>
</div>
@endif
