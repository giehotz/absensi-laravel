@extends('layouts.admin')

@section('title', 'Kelola Petugas MBG')

@section('content')
<div class="space-y-6">

    <!-- Top Action Bar -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-xl sm:text-2xl font-black font-heading text-slate-900 tracking-tight">Manajemen Akun Petugas MBG</h1>
            <p class="text-xs sm:text-sm font-semibold text-slate-500">
                Kelola hak akses pengguna untuk pencatatan dan distribusi Makan Bergizi Gratis
            </p>
        </div>
        <div class="flex items-center gap-2.5">
            <a href="{{ route('mbg.dashboard') }}" 
               class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-white hover:bg-slate-100 text-black font-bold text-xs border-2 border-black shadow-[2px_2px_0px_0px_#000] active:translate-x-0.5 active:translate-y-0.5 transition-all">
                <span>🍱</span>
                <span>Buka Portal MBG</span>
            </a>
            <button type="button" 
                    onclick="openCreateModal()"
                    class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-[#FFD43B] hover:bg-[#FACC15] text-black font-black text-xs border-2 border-black shadow-[3px_3px_0px_0px_#000] active:translate-x-0.5 active:translate-y-0.5 transition-all">
                <span>➕</span>
                <span>Tambah Petugas Baru</span>
            </button>
        </div>
    </div>

    <!-- Alert / Notifikasi -->
    @if(session('success'))
        <div class="p-4 rounded-xl border-2 border-black bg-[#D1F2D9] text-[#0A5C28] font-bold text-xs sm:text-sm flex items-center justify-between shadow-[3px_3px_0px_0px_#000]">
            <div class="flex items-center gap-2">
                <span>✅</span>
                <span>{{ session('success') }}</span>
            </div>
            <button onclick="this.parentElement.remove()" class="text-slate-600 hover:text-black font-black">✕</button>
        </div>
    @endif

    <!-- Tabel Akun Petugas MBG -->
    <div class="rounded-2xl border-4 border-black bg-white shadow-[5px_5px_0px_0px_#000] overflow-hidden">
        <div class="p-4 border-b-3 border-black bg-slate-50 flex items-center justify-between">
            <h3 class="font-heading font-black text-sm text-slate-900">
                Daftar Petugas MBG Aktif ({{ $officers->total() }})
            </h3>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-slate-100 border-b-2 border-black text-slate-700 uppercase font-black tracking-wider text-[11px]">
                        <th class="py-3 px-4 w-12 text-center">No</th>
                        <th class="py-3 px-4">Nama Petugas</th>
                        <th class="py-3 px-4">Alamat Email</th>
                        <th class="py-3 px-4 text-center">Status Akun</th>
                        <th class="py-3 px-4 text-center">Tanggal Dibuat</th>
                        <th class="py-3 px-4 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    @forelse($officers as $idx => $officer)
                        <tr class="hover:bg-slate-50 transition-colors">
                            <td class="py-3.5 px-4 text-center font-bold text-slate-500">
                                {{ $officers->firstItem() + $idx }}
                            </td>
                            <td class="py-3.5 px-4 font-black text-slate-900">
                                <div class="flex items-center gap-2.5">
                                    <div class="w-7 h-7 rounded-lg border border-black bg-[#FFD43B] flex items-center justify-center font-black text-xs">
                                        🍱
                                    </div>
                                    <span>{{ $officer->name }}</span>
                                </div>
                            </td>
                            <td class="py-3.5 px-4 font-semibold text-slate-700">
                                {{ $officer->email }}
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                @if($officer->is_active)
                                    <span class="inline-block px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider bg-emerald-100 text-emerald-800 border border-emerald-300">
                                        Aktif
                                    </span>
                                @else
                                    <span class="inline-block px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider bg-red-100 text-red-800 border border-red-300">
                                        Nonaktif
                                    </span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 text-center text-slate-500 font-medium">
                                {{ $officer->created_at ? $officer->created_at->format('d/m/Y H:i') : '-' }}
                            </td>
                            <td class="py-3.5 px-4 text-center whitespace-nowrap">
                                <div class="flex items-center justify-center gap-1.5">
                                    <button type="button" 
                                            onclick='openEditModal(@json($officer))'
                                            class="p-1.5 rounded-lg border-2 border-black bg-[#FFD43B] hover:bg-[#FACC15] text-black shadow-[1px_1px_0px_0px_#000] active:translate-x-0.5 active:translate-y-0.5 transition-all"
                                            title="Edit Akun">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                                        </svg>
                                    </button>
                                    <form action="{{ route('admin.petugas-mbg.destroy', $officer) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus akun petugas {{ $officer->name }}?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" 
                                                class="p-1.5 rounded-lg border-2 border-black bg-red-100 hover:bg-red-200 text-red-700 shadow-[1px_1px_0px_0px_#000] active:translate-x-0.5 active:translate-y-0.5 transition-all"
                                                title="Hapus Akun">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                            </svg>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-10 text-center text-slate-500 font-semibold">
                                <div class="text-3xl mb-2">🍱</div>
                                <p>Belum ada akun Petugas MBG yang dibuat.</p>
                                <button type="button" onclick="openCreateModal()" class="inline-block mt-3 text-xs font-bold text-blue-600 hover:underline">
                                    + Tambah Akun Petugas Pertama
                                </button>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($officers->hasPages())
            <div class="p-4 border-t-2 border-black bg-slate-50">
                {{ $officers->links() }}
            </div>
        @endif
    </div>

</div>

<!-- Modal Tambah Petugas MBG -->
<div id="createModal" class="fixed inset-0 z-50 bg-black/60 hidden items-center justify-center p-4">
    <div class="w-full max-w-md bg-white rounded-2xl border-4 border-black shadow-[6px_6px_0px_0px_#000] overflow-hidden">
        <div class="p-4 border-b-3 border-black bg-[#FFFBEB] flex items-center justify-between">
            <h3 class="font-heading font-black text-sm text-slate-900">Tambah Akun Petugas MBG</h3>
            <button type="button" onclick="closeCreateModal()" class="font-black text-slate-500 hover:text-black">✕</button>
        </div>
        <form action="{{ route('admin.petugas-mbg.store') }}" method="POST" class="p-5 space-y-4">
            @csrf
            <div>
                <label for="create_name" class="block text-xs font-black uppercase text-slate-700 mb-1">Nama Lengkap Petugas</label>
                <input type="text" id="create_name" name="name" required placeholder="Contoh: Rahmat Santoso, S.Pd"
                       class="w-full px-3 py-2 rounded-lg border-2 border-black font-semibold text-xs focus:outline-none focus:ring-2 focus:ring-amber-400">
            </div>
            <div>
                <label for="create_email" class="block text-xs font-black uppercase text-slate-700 mb-1">Email / Username Login</label>
                <input type="email" id="create_email" name="email" required placeholder="petugas.mbg@sekolah.id"
                       class="w-full px-3 py-2 rounded-lg border-2 border-black font-semibold text-xs focus:outline-none focus:ring-2 focus:ring-amber-400">
            </div>
            <div>
                <label for="create_password" class="block text-xs font-black uppercase text-slate-700 mb-1">Password</label>
                <input type="password" id="create_password" name="password" required minlength="6" placeholder="Minimal 6 karakter"
                       class="w-full px-3 py-2 rounded-lg border-2 border-black font-semibold text-xs focus:outline-none focus:ring-2 focus:ring-amber-400">
            </div>
            <div class="flex items-center gap-2">
                <input type="checkbox" id="create_is_active" name="is_active" value="1" checked
                       class="rounded border-2 border-black text-amber-500 focus:ring-0">
                <label for="create_is_active" class="text-xs font-bold text-slate-700">Akun Langsung Aktif</label>
            </div>
            <div class="pt-3 border-t-2 border-black/10 flex justify-end gap-2">
                <button type="button" onclick="closeCreateModal()" class="px-4 py-2 rounded-lg border-2 border-black bg-slate-100 font-bold text-xs">
                    Batal
                </button>
                <button type="submit" class="px-4 py-2 rounded-lg border-2 border-black bg-[#FFD43B] font-black text-xs shadow-[2px_2px_0px_0px_#000]">
                    Simpan Akun
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Edit Petugas MBG -->
<div id="editModal" class="fixed inset-0 z-50 bg-black/60 hidden items-center justify-center p-4">
    <div class="w-full max-w-md bg-white rounded-2xl border-4 border-black shadow-[6px_6px_0px_0px_#000] overflow-hidden">
        <div class="p-4 border-b-3 border-black bg-[#FEF08A] flex items-center justify-between">
            <h3 class="font-heading font-black text-sm text-slate-900">Edit Akun Petugas MBG</h3>
            <button type="button" onclick="closeEditModal()" class="font-black text-slate-500 hover:text-black">✕</button>
        </div>
        <form id="editForm" method="POST" class="p-5 space-y-4">
            @csrf
            @method('PUT')
            <div>
                <label for="edit_name" class="block text-xs font-black uppercase text-slate-700 mb-1">Nama Lengkap Petugas</label>
                <input type="text" id="edit_name" name="name" required
                       class="w-full px-3 py-2 rounded-lg border-2 border-black font-semibold text-xs focus:outline-none focus:ring-2 focus:ring-amber-400">
            </div>
            <div>
                <label for="edit_email" class="block text-xs font-black uppercase text-slate-700 mb-1">Email / Username Login</label>
                <input type="email" id="edit_email" name="email" required
                       class="w-full px-3 py-2 rounded-lg border-2 border-black font-semibold text-xs focus:outline-none focus:ring-2 focus:ring-amber-400">
            </div>
            <div>
                <label for="edit_password" class="block text-xs font-black uppercase text-slate-700 mb-1">Password Baru (Kosongkan jika tidak diganti)</label>
                <input type="password" id="edit_password" name="password" minlength="6" placeholder="Kosongkan jika tidak diubah"
                       class="w-full px-3 py-2 rounded-lg border-2 border-black font-semibold text-xs focus:outline-none focus:ring-2 focus:ring-amber-400">
            </div>
            <div class="flex items-center gap-2">
                <input type="checkbox" id="edit_is_active" name="is_active" value="1"
                       class="rounded border-2 border-black text-amber-500 focus:ring-0">
                <label for="edit_is_active" class="text-xs font-bold text-slate-700">Status Akun Aktif</label>
            </div>
            <div class="pt-3 border-t-2 border-black/10 flex justify-end gap-2">
                <button type="button" onclick="closeEditModal()" class="px-4 py-2 rounded-lg border-2 border-black bg-slate-100 font-bold text-xs">
                    Batal
                </button>
                <button type="submit" class="px-4 py-2 rounded-lg border-2 border-black bg-[#20C997] font-black text-xs shadow-[2px_2px_0px_0px_#000]">
                    Perbarui Akun
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    function openCreateModal() {
        const modal = document.getElementById('createModal');
        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }
    function closeCreateModal() {
        const modal = document.getElementById('createModal');
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }

    function openEditModal(officer) {
        const modal = document.getElementById('editModal');
        const form = document.getElementById('editForm');
        form.action = `/admin/petugas-mbg/${officer.id}`;
        document.getElementById('edit_name').value = officer.name;
        document.getElementById('edit_email').value = officer.email;
        document.getElementById('edit_password').value = '';
        document.getElementById('edit_is_active').checked = !!officer.is_active;

        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }
    function closeEditModal() {
        const modal = document.getElementById('editModal');
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }
</script>
@endpush
@endsection
