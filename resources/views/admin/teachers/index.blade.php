@extends('layouts.admin')

@section('title', 'Manajemen Data Guru')
@section('page-title', 'Manajemen Data Guru')

@section('content')
<div class="space-y-6">
    <!-- Header & Action -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white neo-box p-5">
        <div>
            <h2 class="font-heading font-black text-xl text-black flex items-center gap-2">
                <span>👨‍🏫</span> Daftar Guru & Tenaga Pendidik
            </h2>
            <p class="text-xs font-semibold text-slate-600 mt-1">
                Kelola profil guru, akun login, dan penugasan wali kelas.
            </p>
        </div>
        <div class="flex flex-wrap items-center gap-2">
            <a href="{{ route('admin.teachers.template') }}" class="neo-btn bg-[#FFF9DB] hover:bg-[#ffec99] text-black px-3.5 py-2 text-xs uppercase flex items-center gap-1.5 cursor-pointer font-heading" title="Unduh Format Template Excel">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                </svg>
                <span>Unduh Template</span>
            </a>

            <button onclick="openModal('importTeacherModal')" class="neo-btn bg-[#FFD43B] hover:bg-[#fcc419] text-black px-3.5 py-2 text-xs uppercase flex items-center gap-1.5 cursor-pointer font-heading">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                </svg>
                <span>Upload Excel</span>
            </button>

            <button onclick="openModal('createTeacherModal')" class="neo-btn bg-[#20C997] hover:bg-[#12b886] text-black px-4 py-2 text-xs uppercase flex items-center gap-1.5 cursor-pointer font-heading">
                <span>+</span> Tambah Guru Baru
            </button>
        </div>
    </div>

    <!-- Table Card -->
    <div class="bg-white neo-box overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-[#FFF9DB] border-b-2 border-black text-xs font-black uppercase tracking-wider">
                    <tr>
                        <th class="p-3.5 border-r border-black">No</th>
                        <th class="p-3.5 border-r border-black">NIP / PEGID</th>
                        <th class="p-3.5 border-r border-black">Nama Guru & Email</th>
                        <th class="p-3.5 border-r border-black">No. Telepon</th>
                        <th class="p-3.5 border-r border-black">Wali Kelas</th>
                        <th class="p-3.5 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y-2 divide-black">
                    @forelse($teachers as $index => $teacher)
                    <tr class="hover:bg-slate-50 font-medium">
                        <td class="p-3.5 font-bold text-center border-r border-black">
                            {{ $teachers->firstItem() + $index }}
                        </td>
                        <td class="p-3.5 border-r border-black">
                            <span class="neo-badge bg-[#E7F5FF] text-blue-900 font-mono text-[11px]">
                                {{ $teacher->nip ?: '-' }}
                            </span>
                        </td>
                        <td class="p-3.5 border-r border-black">
                            <div class="font-bold text-black">{{ $teacher->user->name ?? '-' }}</div>
                            <div class="text-xs text-slate-500 font-mono">{{ $teacher->user->email ?? '-' }}</div>
                        </td>
                        <td class="p-3.5 border-r border-black font-mono text-xs">
                            {{ $teacher->phone ?? '-' }}
                        </td>
                        <td class="p-3.5 border-r border-black">
                            @if($teacher->homeroomClasses->isNotEmpty())
                                @foreach($teacher->homeroomClasses as $cls)
                                    <span class="neo-badge bg-[#D3F9D8] text-emerald-950 text-[10px]">
                                        {{ $cls->name }}
                                    </span>
                                @endforeach
                            @else
                                <span class="text-xs text-slate-400 font-semibold italic">Bukan Wali Kelas</span>
                            @endif
                        </td>
                        <td class="p-3.5 text-center">
                            <div class="flex items-center justify-center gap-2">
                                <button onclick="editTeacher({{ json_encode([
                                    'id' => $teacher->id,
                                    'name' => $teacher->user->name ?? '',
                                    'email' => $teacher->user->email ?? '',
                                    'nip' => $teacher->nip,
                                    'phone' => $teacher->phone ?? '',
                                ]) }})" class="neo-btn bg-[#FFD43B] text-black px-2.5 py-1 text-xs cursor-pointer">
                                    Edit
                                </button>

                                <form action="{{ route('admin.teachers.destroy', $teacher) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus data guru ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="neo-btn bg-[#FF6B6B] text-white px-2.5 py-1 text-xs cursor-pointer">
                                        Hapus
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="p-8 text-center text-slate-500 font-semibold">
                            Belum ada data guru. Klik tombol "+ Tambah Guru Baru" untuk menambahkan.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($teachers->hasPages())
        <div class="p-4 border-t-2 border-black bg-slate-50">
            {{ $teachers->links() }}
        </div>
        @endif
    </div>
</div>

<!-- Modal Import Excel Guru -->
<div id="importTeacherModal" class="fixed inset-0 bg-black/60 z-50 flex items-center justify-center p-4 hidden">
    <div class="bg-white neo-box-lg max-w-lg w-full p-6 space-y-5 relative">
        <div class="flex items-center justify-between border-b-2 border-black pb-3">
            <h3 class="font-heading font-black text-lg text-black flex items-center gap-2">
                <span>📑</span> Import Data Guru dari Excel
            </h3>
            <button onclick="closeModal('importTeacherModal')" class="text-black font-black text-xl hover:opacity-75">✕</button>
        </div>

        <div class="bg-[#E7F5FF] border-2 border-black p-3.5 rounded text-xs text-blue-950 space-y-1.5 font-medium">
            <div class="font-bold flex items-center gap-1.5">
                <span>💡</span> Petunjuk Pengisian File:
            </div>
            <ul class="list-disc list-inside space-y-0.5 text-slate-700 pl-1">
                <li>Gunakan template resmi agar header kolom terbaca tepat.</li>
                <li>Kolom <b>Nama Lengkap</b>, <b>NIP / PEGID</b>, dan <b>Email</b> wajib diisi.</li>
                <li>Kata sandi default jika kosong otomatis: <code>password</code>.</li>
                <li>Format file: <b>.xlsx, .xls, .csv</b> (Maksimal 5MB).</li>
            </ul>
            <div class="pt-1">
                <a href="{{ route('admin.teachers.template') }}" class="inline-flex items-center gap-1.5 text-blue-700 font-bold underline hover:text-blue-900">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                    </svg>
                    Unduh file template contoh di sini (.xlsx)
                </a>
            </div>
        </div>

        <form action="{{ route('admin.teachers.import') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
            @csrf
            <div>
                <label class="block font-heading font-bold text-xs text-black mb-1.5">Pilih File Excel / CSV *</label>
                <div class="border-2 border-dashed border-black rounded-lg p-5 bg-slate-50 text-center hover:bg-slate-100 transition-all cursor-pointer relative">
                    <input type="file" name="file" accept=".xlsx,.xls,.csv" required class="absolute inset-0 opacity-0 cursor-pointer w-full h-full" onchange="document.getElementById('fileNameDisplay').textContent = this.files[0] ? this.files[0].name : 'Belum ada file dipilih'">
                    <div class="space-y-1.5 pointer-events-none">
                        <div class="text-3xl">📊</div>
                        <div class="text-xs font-bold text-black font-heading">Tarik & Lepas File ke Sini atau Klik untuk Memilih</div>
                        <div id="fileNameDisplay" class="text-[11px] font-mono text-slate-500 font-semibold truncate max-w-xs mx-auto">
                            Format .xlsx, .xls, .csv (Maks 5MB)
                        </div>
                    </div>
                </div>
            </div>

            <div class="pt-3 border-t-2 border-slate-200 flex items-center justify-end gap-3">
                <button type="button" onclick="closeModal('importTeacherModal')" class="neo-btn bg-white text-black px-4 py-2 text-xs">
                    Batal
                </button>
                <button type="submit" class="neo-btn bg-[#FFD43B] hover:bg-[#fcc419] text-black px-5 py-2 text-xs font-heading flex items-center gap-1.5">
                    <span>🚀</span> Upload & Proses Data
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Tambah Guru -->
<div id="createTeacherModal" class="fixed inset-0 bg-black/60 z-50 flex items-center justify-center p-4 hidden">
    <div class="bg-white neo-box-lg max-w-lg w-full p-6 space-y-5 relative">
        <div class="flex items-center justify-between border-b-2 border-black pb-3">
            <h3 class="font-heading font-black text-lg text-black flex items-center gap-2">
                <span>➕</span> Tambah Guru Baru
            </h3>
            <button onclick="closeModal('createTeacherModal')" class="text-black font-black text-xl hover:opacity-75">✕</button>
        </div>

        <form action="{{ route('admin.teachers.store') }}" method="POST" class="space-y-4">
            @csrf
            <div>
                <label class="block font-heading font-bold text-xs text-black mb-1">Nama Lengkap & Gelar *</label>
                <input type="text" name="name" required placeholder="Contoh: Ahmad Dahlan, M.Pd." class="w-full px-3 py-2 neo-input text-sm bg-slate-50">
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block font-heading font-bold text-xs text-black mb-1">NIP / PEGID *</label>
                    <input type="text" name="nip" required placeholder="NIP atau PegID..." class="w-full px-3 py-2 neo-input text-sm bg-slate-50">
                </div>
                <div>
                    <label class="block font-heading font-bold text-xs text-black mb-1">No. WhatsApp / HP</label>
                    <input type="text" name="phone" placeholder="0812..." class="w-full px-3 py-2 neo-input text-sm bg-slate-50">
                </div>
            </div>

            <div>
                <label class="block font-heading font-bold text-xs text-black mb-1">Alamat Email Login *</label>
                <input type="email" name="email" required placeholder="guru@sekolah.sch.id" class="w-full px-3 py-2 neo-input text-sm bg-slate-50">
            </div>

            <div>
                <label class="block font-heading font-bold text-xs text-black mb-1">Kata Sandi (Opsional)</label>
                <input type="password" name="password" placeholder="Default: password (jika dikosongkan)" class="w-full px-3 py-2 neo-input text-sm bg-slate-50">
                <span class="text-[11px] text-slate-500 font-semibold mt-0.5 block">Jika tidak diisi, kata sandi otomatis: <code>password</code></span>
            </div>

            <div class="pt-3 border-t-2 border-slate-200 flex items-center justify-end gap-3">
                <button type="button" onclick="closeModal('createTeacherModal')" class="neo-btn bg-white text-black px-4 py-2 text-xs">
                    Batal
                </button>
                <button type="submit" class="neo-btn bg-[#20C997] text-black px-5 py-2 text-xs font-heading">
                    Simpan Data Guru
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Edit Guru -->
<div id="editTeacherModal" class="fixed inset-0 bg-black/60 z-50 flex items-center justify-center p-4 hidden">
    <div class="bg-white neo-box-lg max-w-lg w-full p-6 space-y-5 relative">
        <div class="flex items-center justify-between border-b-2 border-black pb-3">
            <h3 class="font-heading font-black text-lg text-black flex items-center gap-2">
                <span>✏️</span> Edit Data Guru
            </h3>
            <button onclick="closeModal('editTeacherModal')" class="text-black font-black text-xl hover:opacity-75">✕</button>
        </div>

        <form id="editTeacherForm" method="POST" class="space-y-4">
            @csrf
            @method('PUT')
            <div>
                <label class="block font-heading font-bold text-xs text-black mb-1">Nama Lengkap & Gelar *</label>
                <input type="text" id="edit_name" name="name" required class="w-full px-3 py-2 neo-input text-sm bg-slate-50">
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block font-heading font-bold text-xs text-black mb-1">NIP / PEGID *</label>
                    <input type="text" id="edit_nip" name="nip" required placeholder="NIP atau PegID..." class="w-full px-3 py-2 neo-input text-sm bg-slate-50">
                </div>
                <div>
                    <label class="block font-heading font-bold text-xs text-black mb-1">No. WhatsApp / HP</label>
                    <input type="text" id="edit_phone" name="phone" class="w-full px-3 py-2 neo-input text-sm bg-slate-50">
                </div>
            </div>

            <div>
                <label class="block font-heading font-bold text-xs text-black mb-1">Alamat Email *</label>
                <input type="email" id="edit_email" name="email" required class="w-full px-3 py-2 neo-input text-sm bg-slate-50">
            </div>

            <div>
                <label class="block font-heading font-bold text-xs text-black mb-1">Ubah Kata Sandi (Opsional)</label>
                <input type="password" name="password" placeholder="Biarkan kosong jika tidak diubah" class="w-full px-3 py-2 neo-input text-sm bg-slate-50">
            </div>

            <div class="pt-3 border-t-2 border-slate-200 flex items-center justify-end gap-3">
                <button type="button" onclick="closeModal('editTeacherModal')" class="neo-btn bg-white text-black px-4 py-2 text-xs">
                    Batal
                </button>
                <button type="submit" class="neo-btn bg-[#5294FF] text-white px-5 py-2 text-xs font-heading">
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    function editTeacher(data) {
        document.getElementById('edit_name').value = data.name;
        document.getElementById('edit_nip').value = data.nip;
        document.getElementById('edit_email').value = data.email;
        document.getElementById('edit_phone').value = data.phone;
        document.getElementById('editTeacherForm').action = '/admin/teachers/' + data.id;
        openModal('editTeacherModal');
    }
</script>
@endpush
@endsection
