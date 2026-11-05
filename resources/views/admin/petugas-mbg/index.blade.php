@extends('layouts.admin')

@section('title', 'Kelola Petugas MBG')

@section('content')
<div class="space-y-6">

    <!-- Top Action Bar -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-xl sm:text-2xl font-black font-heading text-slate-900 tracking-tight">Manajemen Akun Petugas MBG</h1>
            <p class="text-xs sm:text-sm font-semibold text-slate-500">
                Kelola hak akses pengguna dan guru yang ditugaskan untuk distribusi Makan Bergizi Gratis
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

    @if($errors->any())
        <div class="p-4 rounded-xl border-2 border-black bg-[#FFE3E3] text-[#D6336C] font-bold text-xs sm:text-sm shadow-[3px_3px_0px_0px_#000]">
            <div class="flex items-center gap-2 mb-1">
                <span>⚠️</span>
                <span>Terjadi kesalahan saat memproses data:</span>
            </div>
            <ul class="list-disc list-inside space-y-0.5 font-medium">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
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
                        <th class="py-3 px-4">NIP</th>
                        <th class="py-3 px-4">Alamat Email</th>
                        <th class="py-3 px-4 text-center">Asal Akun</th>
                        <th class="py-3 px-4 text-center">Status</th>
                        <th class="py-3 px-4 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    @forelse($officers as $idx => $officer)
                        @php
                            $isFromTeacher = $officer->isGuru();
                            $officerNip = $officer->teacher?->nip ?? $officer->nip;
                        @endphp
                        <tr class="hover:bg-slate-50 transition-colors">
                            <td class="py-3.5 px-4 text-center font-bold text-slate-500">
                                {{ $officers->firstItem() + $idx }}
                            </td>
                            <td class="py-3.5 px-4 font-black text-slate-900">
                                <div class="flex items-center gap-2.5">
                                    <div class="w-7 h-7 rounded-lg border border-black {{ $isFromTeacher ? 'bg-[#5294FF] text-white' : 'bg-[#FFD43B] text-black' }} flex items-center justify-center font-black text-xs shrink-0 shadow-[1px_1px_0px_0px_#000]">
                                        {{ $isFromTeacher ? '👨‍🏫' : '🍱' }}
                                    </div>
                                    <div>
                                        <div class="font-black text-slate-900">{{ $officer->name }}</div>
                                        @if($isFromTeacher && $officer->teacher?->nuptk)
                                            <div class="text-[10px] text-slate-400 font-semibold">NUPTK: {{ $officer->teacher->nuptk }}</div>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td class="py-3.5 px-4 font-mono font-bold text-slate-700">
                                {{ $officerNip ?: '-' }}
                            </td>
                            <td class="py-3.5 px-4 font-semibold text-slate-700">
                                {{ $officer->email ?: '-' }}
                            </td>
                            <td class="py-3.5 px-4 text-center whitespace-nowrap">
                                @if($isFromTeacher)
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider bg-blue-100 text-blue-900 border border-blue-400">
                                        <span>👨‍🏫</span>
                                        <span>Guru Madrasah</span>
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider bg-amber-100 text-amber-900 border border-amber-400">
                                        <span>🍱</span>
                                        <span>Akun Mandiri</span>
                                    </span>
                                @endif
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
                            <td class="py-3.5 px-4 text-center whitespace-nowrap">
                                <div class="flex items-center justify-center gap-1.5">
                                    <button type="button" 
                                            data-id="{{ $officer->id }}"
                                            data-name="{{ $officer->name }}"
                                            data-nip="{{ $officerNip ?? '' }}"
                                            data-email="{{ $officer->email ?? '' }}"
                                            data-active="{{ $officer->is_active ? '1' : '0' }}"
                                            data-teacher="{{ $isFromTeacher ? '1' : '0' }}"
                                            onclick="openEditModalFromButton(this)"
                                            class="p-1.5 rounded-lg border-2 border-black bg-[#FFD43B] hover:bg-[#FACC15] text-black shadow-[1px_1px_0px_0px_#000] active:translate-x-0.5 active:translate-y-0.5 transition-all"
                                            title="Edit Petugas">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                                        </svg>
                                    </button>

                                    @if($isFromTeacher)
                                        <form action="{{ route('admin.petugas-mbg.destroy', $officer) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin mencabut penugasan Petugas MBG untuk guru {{ $officer->name }}?\n\n(Akun Guru dan data sekolah tetap aman dan tidak dihapus)')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" 
                                                    class="p-1.5 rounded-lg border-2 border-black bg-orange-100 hover:bg-orange-200 text-orange-800 shadow-[1px_1px_0px_0px_#000] active:translate-x-0.5 active:translate-y-0.5 transition-all"
                                                    title="Cabut Penugasan Petugas MBG">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 7a4 4 0 11-8 0 4 4 0 018 0zM9 14a6 6 0 00-6 6v1h12v-1a6 6 0 00-6-6zM21 12h-6"/>
                                                </svg>
                                            </button>
                                        </form>
                                    @else
                                        <form action="{{ route('admin.petugas-mbg.destroy', $officer) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus akun Petugas MBG mandiri {{ $officer->name }}?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" 
                                                    class="p-1.5 rounded-lg border-2 border-black bg-red-100 hover:bg-red-200 text-red-700 shadow-[1px_1px_0px_0px_#000] active:translate-x-0.5 active:translate-y-0.5 transition-all"
                                                    title="Hapus Akun Mandiri">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                </svg>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-10 text-center text-slate-500 font-semibold">
                                <div class="text-3xl mb-2">🍱</div>
                                <p>Belum ada Petugas MBG yang ditunjuk atau dibuat.</p>
                                <button type="button" onclick="openCreateModal()" class="inline-block mt-3 text-xs font-bold text-blue-600 hover:underline">
                                    + Tambah Petugas MBG Pertama
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
    <div class="w-full max-w-lg bg-white rounded-2xl border-4 border-black shadow-[6px_6px_0px_0px_#000] overflow-hidden flex flex-col max-h-[90vh]">
        <div class="p-4 border-b-3 border-black bg-[#FFFBEB] flex items-center justify-between shrink-0">
            <div class="flex items-center gap-2">
                <span class="text-lg">🍱</span>
                <h3 class="font-heading font-black text-sm text-slate-900">Tambah Akun / Petugas MBG Baru</h3>
            </div>
            <button type="button" onclick="closeCreateModal()" class="font-black text-slate-500 hover:text-black">✕</button>
        </div>

        <!-- Tab Selection -->
        <div class="flex border-b-2 border-black bg-slate-100 p-1.5 gap-1.5 shrink-0">
            <button type="button" 
                    id="tabBtnGuru"
                    onclick="switchCreateTab('guru')"
                    class="flex-1 py-2 px-3 rounded-lg text-xs font-black transition-all border-2 border-black bg-[#FFD43B] text-black shadow-[2px_2px_0px_0px_#000]">
                👨‍🏫 Tunjuk dari Guru
            </button>
            <button type="button" 
                    id="tabBtnManual"
                    onclick="switchCreateTab('manual')"
                    class="flex-1 py-2 px-3 rounded-lg text-xs font-bold transition-all border-2 border-transparent bg-transparent text-slate-600 hover:bg-slate-200">
                ➕ Akun Mandiri Baru
            </button>
        </div>

        <!-- Form Tambah Petugas -->
        <form action="{{ route('admin.petugas-mbg.store') }}" method="POST" class="p-5 space-y-4 overflow-y-auto">
            @csrf
            <input type="hidden" id="create_account_type" name="account_type" value="guru">

            <!-- Tab 1: Pilih dari Guru -->
            <div id="tabContentGuru" class="space-y-4">
                <div class="p-3 rounded-xl bg-blue-50 border-2 border-blue-400 text-blue-950 text-xs">
                    <p class="font-bold mb-1">ℹ️ Hak Akses Guru sebagai Petugas MBG:</p>
                    <p class="text-[11px] leading-relaxed text-blue-800">
                        Guru yang ditunjuk dapat login menggunakan akun guru miliknya sendiri dan langsung memiliki akses ke menu pencatatan dan distribusi MBG di sidebar portal Guru.
                    </p>
                </div>

                <div>
                    <label for="create_teacher_id" class="block text-xs font-black uppercase text-slate-700 mb-1">
                        Pilih Guru (Dari Data Guru) <span class="text-red-500">*</span>
                    </label>
                    <select id="create_teacher_id" name="teacher_id" 
                            class="w-full px-3 py-2.5 rounded-lg border-2 border-black font-semibold text-xs focus:outline-none focus:ring-2 focus:ring-amber-400 bg-white">
                        <option value="">-- Pilih Guru yang Ditugaskan --</option>
                        @foreach($availableTeachers as $teacher)
                            <option value="{{ $teacher->id }}">
                                {{ $teacher->user?->name ?? 'Guru' }} 
                                @if($teacher->nip) (NIP: {{ $teacher->nip }}) @endif
                            </option>
                        @endforeach
                    </select>
                    @if($availableTeachers->isEmpty())
                        <p class="text-[11px] text-amber-600 font-bold mt-1">
                            Semua data guru saat ini sudah memiliki wewenang Petugas MBG atau belum ada data guru.
                        </p>
                    @endif
                </div>
            </div>

            <!-- Tab 2: Akun Mandiri -->
            <div id="tabContentManual" class="space-y-4 hidden">
                <div>
                    <label for="create_name" class="block text-xs font-black uppercase text-slate-700 mb-1">Nama Lengkap Petugas <span class="text-red-500">*</span></label>
                    <input type="text" id="create_name" name="name" placeholder="Contoh: Rahmat Santoso, S.Pd"
                           class="w-full px-3 py-2 rounded-lg border-2 border-black font-semibold text-xs focus:outline-none focus:ring-2 focus:ring-amber-400">
                </div>
                <div>
                    <label for="create_nip" class="block text-xs font-black uppercase text-slate-700 mb-1">NIP Petugas (Opsional)</label>
                    <input type="text" id="create_nip" name="nip" placeholder="Contoh: 198501012010011001"
                           class="w-full px-3 py-2 rounded-lg border-2 border-black font-semibold text-xs focus:outline-none focus:ring-2 focus:ring-amber-400">
                </div>
                <div>
                    <label for="create_email" class="block text-xs font-black uppercase text-slate-700 mb-1">Email / Username Login <span class="text-red-500">*</span></label>
                    <input type="email" id="create_email" name="email" placeholder="petugas.mbg@sekolah.id"
                           class="w-full px-3 py-2 rounded-lg border-2 border-black font-semibold text-xs focus:outline-none focus:ring-2 focus:ring-amber-400">
                </div>
                <div>
                    <label for="create_password" class="block text-xs font-black uppercase text-slate-700 mb-1">Password <span class="text-red-500">*</span></label>
                    <input type="password" id="create_password" name="password" minlength="6" placeholder="Minimal 6 karakter"
                           class="w-full px-3 py-2 rounded-lg border-2 border-black font-semibold text-xs focus:outline-none focus:ring-2 focus:ring-amber-400">
                </div>
                <div class="flex items-center gap-2">
                    <input type="checkbox" id="create_is_active" name="is_active" value="1" checked
                           class="rounded border-2 border-black text-amber-500 focus:ring-0">
                    <label for="create_is_active" class="text-xs font-bold text-slate-700">Akun Langsung Aktif</label>
                </div>
            </div>

            <div class="pt-3 border-t-2 border-black/10 flex justify-end gap-2">
                <button type="button" onclick="closeCreateModal()" class="px-4 py-2 rounded-lg border-2 border-black bg-slate-100 font-bold text-xs">
                    Batal
                </button>
                <button type="submit" class="px-4 py-2 rounded-lg border-2 border-black bg-[#FFD43B] hover:bg-[#FACC15] font-black text-xs shadow-[2px_2px_0px_0px_#000]">
                    Simpan Petugas
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Edit Petugas MBG -->
<div id="editModal" class="fixed inset-0 z-50 bg-black/60 hidden items-center justify-center p-4">
    <div class="w-full max-w-md bg-white rounded-2xl border-4 border-black shadow-[6px_6px_0px_0px_#000] overflow-hidden">
        <div class="p-4 border-b-3 border-black bg-[#FEF08A] flex items-center justify-between">
            <h3 class="font-heading font-black text-sm text-slate-900">Edit Data Petugas MBG</h3>
            <button type="button" onclick="closeEditModal()" class="font-black text-slate-500 hover:text-black">✕</button>
        </div>
        <form id="editForm" method="POST" class="p-5 space-y-4">
            @csrf
            @method('PUT')

            <div id="editTeacherBadge" class="p-2.5 rounded-lg bg-blue-50 border border-blue-400 text-blue-900 text-xs font-bold hidden">
                👨‍🏫 Petugas ini adalah Guru Madrasah. Nama dan email terikat dengan profil guru.
            </div>

            <div>
                <label for="edit_name" class="block text-xs font-black uppercase text-slate-700 mb-1">Nama Lengkap Petugas</label>
                <input type="text" id="edit_name" name="name" required
                       class="w-full px-3 py-2 rounded-lg border-2 border-black font-semibold text-xs focus:outline-none focus:ring-2 focus:ring-amber-400">
            </div>

            <div>
                <label for="edit_nip" class="block text-xs font-black uppercase text-slate-700 mb-1">NIP Petugas</label>
                <input type="text" id="edit_nip" name="nip" placeholder="Contoh: 198501012010011001"
                       class="w-full px-3 py-2 rounded-lg border-2 border-black font-semibold text-xs focus:outline-none focus:ring-2 focus:ring-amber-400">
            </div>

            <div id="editEmailContainer">
                <label for="edit_email" class="block text-xs font-black uppercase text-slate-700 mb-1">Email / Username Login</label>
                <input type="email" id="edit_email" name="email" required
                       class="w-full px-3 py-2 rounded-lg border-2 border-black font-semibold text-xs focus:outline-none focus:ring-2 focus:ring-amber-400">
            </div>

            <div id="editPasswordContainer">
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
                <button type="submit" class="px-4 py-2 rounded-lg border-2 border-black bg-[#20C997] hover:bg-emerald-500 font-black text-xs shadow-[2px_2px_0px_0px_#000]">
                    Perbarui Data
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
        switchCreateTab('guru');
    }

    function closeCreateModal() {
        const modal = document.getElementById('createModal');
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }

    function switchCreateTab(tab) {
        const btnGuru = document.getElementById('tabBtnGuru');
        const btnManual = document.getElementById('tabBtnManual');
        const contentGuru = document.getElementById('tabContentGuru');
        const contentManual = document.getElementById('tabContentManual');
        const typeInput = document.getElementById('create_account_type');

        const teacherSelect = document.getElementById('create_teacher_id');
        const inputName = document.getElementById('create_name');
        const inputEmail = document.getElementById('create_email');
        const inputPassword = document.getElementById('create_password');

        if (tab === 'guru') {
            typeInput.value = 'guru';
            btnGuru.className = "flex-1 py-2 px-3 rounded-lg text-xs font-black transition-all border-2 border-black bg-[#FFD43B] text-black shadow-[2px_2px_0px_0px_#000]";
            btnManual.className = "flex-1 py-2 px-3 rounded-lg text-xs font-bold transition-all border-2 border-transparent bg-transparent text-slate-600 hover:bg-slate-200";
            contentGuru.classList.remove('hidden');
            contentManual.classList.add('hidden');

            teacherSelect.required = true;
            inputName.required = false;
            inputEmail.required = false;
            inputPassword.required = false;
        } else {
            typeInput.value = 'manual';
            btnManual.className = "flex-1 py-2 px-3 rounded-lg text-xs font-black transition-all border-2 border-black bg-[#FFD43B] text-black shadow-[2px_2px_0px_0px_#000]";
            btnGuru.className = "flex-1 py-2 px-3 rounded-lg text-xs font-bold transition-all border-2 border-transparent bg-transparent text-slate-600 hover:bg-slate-200";
            contentManual.classList.remove('hidden');
            contentGuru.classList.add('hidden');

            teacherSelect.required = false;
            inputName.required = true;
            inputEmail.required = true;
            inputPassword.required = true;
        }
    }

    function openEditModalFromButton(btn) {
        openEditModal({
            id: btn.getAttribute('data-id'),
            name: btn.getAttribute('data-name'),
            nip: btn.getAttribute('data-nip'),
            email: btn.getAttribute('data-email'),
            is_active: btn.getAttribute('data-active') === '1',
            is_teacher: btn.getAttribute('data-teacher') === '1'
        });
    }

    function openEditModal(officer) {
        const modal = document.getElementById('editModal');
        const form = document.getElementById('editForm');
        form.action = `/admin/petugas-mbg/${officer.id}`;

        document.getElementById('edit_name').value = officer.name;
        document.getElementById('edit_nip').value = officer.nip || '';
        document.getElementById('edit_email').value = officer.email || '';
        document.getElementById('edit_password').value = '';
        document.getElementById('edit_is_active').checked = !!officer.is_active;

        const isTeacher = !!officer.is_teacher;
        const badgeTeacher = document.getElementById('editTeacherBadge');
        const nameInput = document.getElementById('edit_name');
        const emailInput = document.getElementById('edit_email');
        const passwordContainer = document.getElementById('editPasswordContainer');

        if (isTeacher) {
            badgeTeacher.classList.remove('hidden');
            nameInput.readOnly = true;
            nameInput.classList.add('bg-slate-100');
            emailInput.readOnly = true;
            emailInput.classList.add('bg-slate-100');
            passwordContainer.classList.add('hidden');
        } else {
            badgeTeacher.classList.add('hidden');
            nameInput.readOnly = false;
            nameInput.classList.remove('bg-slate-100');
            emailInput.readOnly = false;
            emailInput.classList.remove('bg-slate-100');
            passwordContainer.classList.remove('hidden');
        }

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
