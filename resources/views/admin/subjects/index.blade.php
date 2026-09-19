@extends('layouts.admin')

@section('title', 'Manajemen Mata Pelajaran')
@section('page-title', 'Manajemen Mata Pelajaran')

@section('content')
<div class="space-y-6">
    <!-- Header & Action -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white neo-box p-5">
        <div>
            <h2 class="font-heading font-black text-xl text-black flex items-center gap-2">
                <span>📚</span> Master Mata Pelajaran
            </h2>
            <p class="text-xs font-semibold text-slate-600 mt-1">
                Kelola daftar kurikulum mata pelajaran dan kode singkat untuk jadwal pembelajaran.
            </p>
        </div>
        <div>
            <button onclick="openModal('createSubjectModal')" class="neo-btn bg-[#20C997] hover:bg-[#12b886] text-black px-4 py-2.5 text-xs uppercase flex items-center gap-2 cursor-pointer font-heading">
                <span>+</span> Tambah Mapel Baru
            </button>
        </div>
    </div>

    <!-- Table Card -->
    <div class="bg-white neo-box overflow-hidden max-w-4xl">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-[#FFF9DB] border-b-2 border-black text-xs font-black uppercase tracking-wider">
                    <tr>
                        <th class="p-3.5 border-r border-black w-16 text-center">No</th>
                        <th class="p-3.5 border-r border-black w-32">Kode Mapel</th>
                        <th class="p-3.5 border-r border-black">Nama Mata Pelajaran</th>
                        <th class="p-3.5 border-r border-black text-center w-36">Jadwal Terkait</th>
                        <th class="p-3.5 text-center w-40">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y-2 divide-black">
                    @forelse($subjects as $index => $subject)
                    <tr class="hover:bg-slate-50 font-medium">
                        <td class="p-3.5 font-bold text-center border-r border-black">
                            {{ $subjects->firstItem() + $index }}
                        </td>
                        <td class="p-3.5 border-r border-black">
                            <span class="neo-badge bg-[#E7F5FF] text-blue-900 font-mono text-xs">
                                {{ $subject->code }}
                            </span>
                        </td>
                        <td class="p-3.5 font-bold text-black border-r border-black">
                            {{ $subject->name }}
                        </td>
                        <td class="p-3.5 border-r border-black text-center font-bold">
                            <span class="bg-slate-100 border border-black px-2 py-0.5 text-xs font-mono">
                                {{ $subject->schedules_count }} Jadwal
                            </span>
                        </td>
                        <td class="p-3.5 text-center">
                            <div class="flex items-center justify-center gap-2">
                                <button onclick="editSubject({{ json_encode([
                                    'id' => $subject->id,
                                    'name' => $subject->name,
                                    'code' => $subject->code,
                                ]) }})" class="neo-btn bg-[#FFD43B] text-black px-2.5 py-1 text-xs cursor-pointer">
                                    Edit
                                </button>

                                <form action="{{ route('admin.subjects.destroy', $subject) }}" method="POST" onsubmit="return confirm('Hapus mata pelajaran ini?')">
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
                        <td colspan="5" class="p-8 text-center text-slate-500 font-semibold">
                            Belum ada mata pelajaran. Klik tombol "+ Tambah Mapel Baru" untuk menambahkan.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($subjects->hasPages())
        <div class="p-4 border-t-2 border-black bg-slate-50">
            {{ $subjects->links() }}
        </div>
        @endif
    </div>
</div>

<!-- Modal Tambah Mapel -->
<div id="createSubjectModal" class="fixed inset-0 bg-black/60 z-50 flex items-center justify-center p-4 hidden">
    <div class="bg-white neo-box-lg max-w-md w-full p-6 space-y-5 relative">
        <div class="flex items-center justify-between border-b-2 border-black pb-3">
            <h3 class="font-heading font-black text-lg text-black flex items-center gap-2">
                <span>➕</span> Tambah Mata Pelajaran
            </h3>
            <button onclick="closeModal('createSubjectModal')" class="text-black font-black text-xl hover:opacity-75">✕</button>
        </div>

        <form action="{{ route('admin.subjects.store') }}" method="POST" class="space-y-4">
            @csrf
            <div>
                <label class="block font-heading font-bold text-xs text-black mb-1">Kode Mapel *</label>
                <input type="text" name="code" required placeholder="Contoh: MTK, IPA, PAI" class="w-full px-3 py-2 neo-input text-sm bg-slate-50 uppercase font-mono">
            </div>

            <div>
                <label class="block font-heading font-bold text-xs text-black mb-1">Nama Mata Pelajaran *</label>
                <input type="text" name="name" required placeholder="Contoh: Matematika Wajib" class="w-full px-3 py-2 neo-input text-sm bg-slate-50">
            </div>

            <div class="pt-3 border-t-2 border-slate-200 flex items-center justify-end gap-3">
                <button type="button" onclick="closeModal('createSubjectModal')" class="neo-btn bg-white text-black px-4 py-2 text-xs">
                    Batal
                </button>
                <button type="submit" class="neo-btn bg-[#20C997] text-black px-5 py-2 text-xs font-heading">
                    Simpan Mapel
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Edit Mapel -->
<div id="editSubjectModal" class="fixed inset-0 bg-black/60 z-50 flex items-center justify-center p-4 hidden">
    <div class="bg-white neo-box-lg max-w-md w-full p-6 space-y-5 relative">
        <div class="flex items-center justify-between border-b-2 border-black pb-3">
            <h3 class="font-heading font-black text-lg text-black flex items-center gap-2">
                <span>✏️</span> Edit Mata Pelajaran
            </h3>
            <button onclick="closeModal('editSubjectModal')" class="text-black font-black text-xl hover:opacity-75">✕</button>
        </div>

        <form id="editSubjectForm" method="POST" class="space-y-4">
            @csrf
            @method('PUT')
            <div>
                <label class="block font-heading font-bold text-xs text-black mb-1">Kode Mapel *</label>
                <input type="text" id="edit_sub_code" name="code" required class="w-full px-3 py-2 neo-input text-sm bg-slate-50 uppercase font-mono">
            </div>

            <div>
                <label class="block font-heading font-bold text-xs text-black mb-1">Nama Mata Pelajaran *</label>
                <input type="text" id="edit_sub_name" name="name" required class="w-full px-3 py-2 neo-input text-sm bg-slate-50">
            </div>

            <div class="pt-3 border-t-2 border-slate-200 flex items-center justify-end gap-3">
                <button type="button" onclick="closeModal('editSubjectModal')" class="neo-btn bg-white text-black px-4 py-2 text-xs">
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
    function editSubject(data) {
        document.getElementById('edit_sub_code').value = data.code;
        document.getElementById('edit_sub_name').value = data.name;
        document.getElementById('editSubjectForm').action = '/admin/subjects/' + data.id;
        openModal('editSubjectModal');
    }
</script>
@endpush
@endsection
