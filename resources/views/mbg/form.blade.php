@extends('layouts.mbg')

@section('title', $isEdit ? 'Koreksi Distribusi MBG Per Kelas' : 'Catat Distribusi MBG Per Kelas')
@section('page_title', $isEdit ? 'Koreksi Data Distribusi MBG Per Kelas' : 'Pencatatan Distribusi MBG Berdasarkan Kelas')

@section('content')
<div class="max-w-5xl mx-auto space-y-6">

    <!-- Tombol Kembali & Info -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <a href="{{ route('mbg.dashboard') }}" 
           class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl bg-white hover:bg-slate-100 text-black font-bold text-xs border-2 border-black shadow-[2px_2px_0px_0px_#000] active:translate-x-0.5 active:translate-y-0.5 transition-all w-fit">
            <span>&larr;</span>
            <span>Kembali ke Dashboard</span>
        </a>
        <div class="text-xs font-semibold text-slate-500">
            Periksa target porsi per kelas. Kolom bertanda <span class="text-red-500 font-bold">*</span> wajib diisi.
        </div>
    </div>

    <!-- Form Card Utama -->
    <div class="rounded-2xl border-4 border-black bg-white shadow-[6px_6px_0px_0px_#000] overflow-hidden">
        <div class="p-6 border-b-3 border-black bg-[#FFFBEB]">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl border-2 border-black bg-[#FFD43B] flex items-center justify-center text-xl shrink-0 shadow-[2px_2px_0px_0px_#000]">
                    🍱
                </div>
                <div>
                    <h2 class="text-lg font-black font-heading tracking-tight text-slate-900">
                        {{ $isEdit ? 'Form Edit Data Penyaluran MBG Per Kelas' : 'Form Pencatatan Distribusi MBG Per Rombel Kelas' }}
                    </h2>
                    <p class="text-xs text-slate-600 font-semibold">
                        Sistem telah mengisi target porsi berdasarkan data jumlah siswa di tiap kelas. Sesuaikan porsi yang berhasil disalurkan jika ada siswa yang tidak hadir.
                    </p>
                </div>
            </div>
        </div>

        <form action="{{ $isEdit ? route('mbg.distributions.update', $distribution) : route('mbg.distributions.store') }}" 
              method="POST" 
              enctype="multipart/form-data" 
              class="p-6 sm:p-8 space-y-7">
            @csrf
            @if($isEdit)
                @method('PUT')
            @endif

            <!-- Section 1: Informasi Pelaksanaan -->
            <div>
                <h3 class="text-xs font-black font-heading uppercase tracking-wider text-slate-600 mb-3 flex items-center gap-2">
                    <span>📋</span>
                    <span>1. Informasi Pelaksanaan & Menu Makanan</span>
                </h3>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">
                    <!-- Tanggal Distribusi -->
                    <div>
                        <label for="date" class="block text-xs font-black uppercase tracking-wider text-slate-700 mb-1.5">
                            Tanggal Distribusi <span class="text-red-500">*</span>
                        </label>
                        <input type="date" 
                               id="date" 
                               name="date" 
                               value="{{ old('date', $distribution->date ? $distribution->date->format('Y-m-d') : date('Y-m-d')) }}" 
                               required 
                               class="w-full px-4 py-2.5 rounded-xl border-2 border-black bg-slate-50 font-bold text-xs sm:text-sm focus:bg-white focus:outline-none focus:ring-2 focus:ring-amber-400 shadow-[2px_2px_0px_0px_#000]">
                    </div>

                    <!-- Status Pelaksanaan -->
                    <div>
                        <label for="status" class="block text-xs font-black uppercase tracking-wider text-slate-700 mb-1.5">
                            Status Pelaksanaan <span class="text-red-500">*</span>
                        </label>
                        <select id="status" 
                                name="status" 
                                required 
                                class="w-full px-4 py-2.5 rounded-xl border-2 border-black bg-slate-50 font-bold text-xs sm:text-sm focus:bg-white focus:outline-none focus:ring-2 focus:ring-amber-400 shadow-[2px_2px_0px_0px_#000]">
                            <option value="selesai" {{ old('status', $distribution->status) === 'selesai' ? 'selected' : '' }}>✅ Selesai (Disalurkan Penuh)</option>
                            <option value="sebagian" {{ old('status', $distribution->status) === 'sebagian' ? 'selected' : '' }}>⚠️ Sebagian (Ada Sisa / Rusak)</option>
                            <option value="dibatalkan" {{ old('status', $distribution->status) === 'dibatalkan' ? 'selected' : '' }}>❌ Dibatalkan / Batal Salur</option>
                        </select>
                    </div>

                    <!-- Foto Bukti Dokumentasi -->
                    <div>
                        <label for="documentation_image" class="block text-xs font-black uppercase tracking-wider text-slate-700 mb-1.5">
                            Foto Dokumentasi (Opsional)
                        </label>
                        <input type="file" 
                               id="documentation_image" 
                               name="documentation_image" 
                               accept="image/*"
                               onchange="previewImage(this)"
                               class="w-full text-xs font-semibold text-slate-600 file:mr-2 file:py-2 file:px-3 file:rounded-lg file:border-2 file:border-black file:text-xs file:font-black file:bg-[#FFD43B] file:text-black file:cursor-pointer hover:file:bg-amber-300">
                    </div>
                </div>

                <!-- Nama / Rincian Menu -->
                <div class="mt-4">
                    <label for="menu_name" class="block text-xs font-black uppercase tracking-wider text-slate-700 mb-1.5">
                        Nama / Rincian Menu Makanan Hari Ini <span class="text-red-500">*</span>
                    </label>
                    <input type="text" 
                           id="menu_name" 
                           name="menu_name" 
                           value="{{ old('menu_name', $distribution->menu_name) }}" 
                           placeholder="Contoh: Nasi Putih, Ayam Teriyaki, Sayur Brokoli Wortel, Buah Jeruk, Susu Kotak" 
                           required 
                           class="w-full px-4 py-2.5 rounded-xl border-2 border-black bg-slate-50 font-semibold text-xs sm:text-sm focus:bg-white focus:outline-none focus:ring-2 focus:ring-amber-400 shadow-[2px_2px_0px_0px_#000]">
                </div>

                <!-- Preview Gambar jika ada -->
                <div id="image_preview_box" class="mt-3 {{ $distribution->documentation_image ? '' : 'hidden' }}">
                    <div class="text-[10px] font-bold text-slate-500 mb-1">Pratinjau Foto Dokumentasi:</div>
                    <img id="image_preview" 
                         src="{{ $distribution->documentation_image ? asset('storage/' . $distribution->documentation_image) : '' }}" 
                         alt="Preview" 
                         class="h-28 w-auto rounded-lg border-2 border-black object-cover shadow-[2px_2px_0px_0px_#000]">
                </div>
            </div>

            <hr class="border-t-2 border-black/10">

            <!-- Section 2: Tabel Matriks Distribusi Per Rombel Kelas & Guru -->
            <div>
                <div class="flex items-center justify-between mb-3">
                    <h3 class="text-xs font-black font-heading uppercase tracking-wider text-slate-600 flex items-center gap-2">
                        <span>🏫</span>
                        <span>2. Tabel Matriks Penyaluran Per Kelas & Guru</span>
                    </h3>
                    <span class="text-[11px] font-bold bg-[#E0E7FF] text-indigo-900 px-2.5 py-0.5 rounded-lg border border-black shadow-[1px_1px_0px_0px_#000]">
                        {{ $classes->count() }} Rombel Kelas Terdaftar
                    </span>
                </div>

                <div class="rounded-xl border-3 border-black overflow-hidden shadow-[3px_3px_0px_0px_#000]">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse text-xs">
                            <thead>
                                <tr class="bg-slate-100 border-b-2 border-black text-slate-800 uppercase font-black tracking-wider text-[11px]">
                                    <th class="py-3 px-3 text-center w-12">No</th>
                                    <th class="py-3 px-4">Rombel / Sasaran</th>
                                    <th class="py-3 px-3 text-center w-36">Jatah Target (Porsi)</th>
                                    <th class="py-3 px-3 text-center w-40">Disalurkan (Porsi) <span class="text-red-500">*</span></th>
                                    <th class="py-3 px-3 text-center w-28">Sisa</th>
                                    <th class="py-3 px-4">Keterangan / Catatan Rombel</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-200">
                                @forelse($classes as $idx => $cls)
                                    @php
                                        $existing = $existingClassDistributions->get($cls->id);
                                        $defaultTarget = $existing ? $existing->target_portions : $cls->students_count;
                                        $defaultDist = $existing ? $existing->distributed_portions : $cls->students_count;
                                        $defaultLeftover = $existing ? $existing->leftover_portions : max(0, $defaultTarget - $defaultDist);
                                        $defaultNotes = $existing ? $existing->notes : '';
                                    @endphp
                                    <tr class="hover:bg-slate-50/80 transition-colors">
                                        <td class="py-3 px-3 text-center font-bold text-slate-500">
                                            {{ $idx + 1 }}
                                            <input type="hidden" name="classes[{{ $idx }}][school_class_id]" value="{{ $cls->id }}">
                                        </td>
                                        <td class="py-3 px-4 font-black text-slate-900 whitespace-nowrap">
                                            <div class="flex items-center gap-2">
                                                <span class="w-6 h-6 rounded border border-black bg-[#D1F2D9] text-xs font-black flex items-center justify-center">
                                                    🎒
                                                </span>
                                                <span>Kelas {{ $cls->name }}</span>
                                            </div>
                                            <div class="text-[10px] text-slate-500 font-semibold mt-0.5">
                                                Terdaftar: {{ $cls->students_count }} Siswa
                                            </div>
                                        </td>
                                        <!-- Target Porsi Kelas -->
                                        <td class="py-2.5 px-3 text-center">
                                            <input type="number" 
                                                   name="classes[{{ $idx }}][target_portions]" 
                                                   id="class_target_{{ $idx }}"
                                                   value="{{ old('classes.' . $idx . '.target_portions', $defaultTarget) }}" 
                                                   min="0" 
                                                   required 
                                                   oninput="recalculateMatrix()"
                                                   class="class-target-input w-28 text-center px-2 py-1.5 rounded-lg border-2 border-black bg-slate-50 font-black text-xs focus:bg-white focus:outline-none focus:ring-2 focus:ring-amber-400 shadow-[1px_1px_0px_0px_#000]">
                                        </td>
                                        <!-- Realisasi Disalurkan Kelas -->
                                        <td class="py-2.5 px-3 text-center">
                                            <input type="number" 
                                                   name="classes[{{ $idx }}][distributed_portions]" 
                                                   id="class_dist_{{ $idx }}"
                                                   value="{{ old('classes.' . $idx . '.distributed_portions', $defaultDist) }}" 
                                                   min="0" 
                                                   required 
                                                   oninput="recalculateRow({{ $idx }})"
                                                   class="class-dist-input w-28 text-center px-2 py-1.5 rounded-lg border-2 border-black bg-white font-black text-xs text-emerald-700 focus:outline-none focus:ring-2 focus:ring-emerald-400 shadow-[2px_2px_0px_0px_#000]">
                                        </td>
                                        <!-- Sisa Porsi Kelas -->
                                        <td class="py-2.5 px-3 text-center">
                                            <input type="number" 
                                                   name="classes[{{ $idx }}][leftover_portions]" 
                                                   id="class_leftover_{{ $idx }}"
                                                   value="{{ old('classes.' . $idx . '.leftover_portions', $defaultLeftover) }}" 
                                                   min="0" 
                                                   oninput="recalculateMatrix()"
                                                   class="class-leftover-input w-20 text-center px-2 py-1.5 rounded-lg border-2 border-black bg-slate-50 font-bold text-xs text-red-600 focus:outline-none shadow-[1px_1px_0px_0px_#000]">
                                        </td>
                                        <!-- Catatan Kelas -->
                                        <td class="py-2.5 px-4">
                                            <input type="text" 
                                                   name="classes[{{ $idx }}][notes]" 
                                                   value="{{ old('classes.' . $idx . '.notes', $defaultNotes) }}" 
                                                   placeholder="Misal: 2 siswa sakit / izin"
                                                   class="w-full px-2.5 py-1.5 rounded-lg border border-black bg-white text-xs font-semibold focus:outline-none focus:ring-1 focus:ring-amber-400">
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="py-6 text-center text-slate-500 font-semibold">
                                            Belum ada rombel kelas yang terdaftar di sistem.
                                        </td>
                                    </tr>
                                @endforelse

                                <!-- Baris Khusus: Guru & Tenaga Kependidikan (Staf) -->
                                <tr class="bg-[#FEF08A]/30 border-t-3 border-black font-black">
                                    <td class="py-3 px-3 text-center text-slate-700">★</td>
                                    <td class="py-3 px-4 whitespace-nowrap">
                                        <div class="flex items-center gap-2">
                                            <span class="w-6 h-6 rounded border border-black bg-[#FFD43B] text-xs font-black flex items-center justify-center">
                                                👨‍🏫
                                            </span>
                                            <span class="text-slate-900 font-black">Guru & Tenaga Kependidikan</span>
                                        </div>
                                        <div class="text-[10px] text-slate-600 font-semibold mt-0.5">
                                            Jatah Konsumsi Guru & Staf Sekolah
                                        </div>
                                    </td>
                                    <!-- Target Guru -->
                                    <td class="py-2.5 px-3 text-center">
                                        <input type="number" 
                                               id="target_teachers" 
                                               name="target_teachers" 
                                               value="{{ old('target_teachers', $distribution->target_teachers ?? 0) }}" 
                                               min="0" 
                                               required 
                                               oninput="recalculateMatrix()"
                                               class="w-28 text-center px-2 py-1.5 rounded-lg border-2 border-black bg-slate-50 font-black text-xs focus:bg-white focus:outline-none focus:ring-2 focus:ring-amber-400 shadow-[1px_1px_0px_0px_#000]">
                                    </td>
                                    <!-- Realisasi Guru -->
                                    <td class="py-2.5 px-3 text-center">
                                        <input type="number" 
                                               id="distributed_teachers" 
                                               name="distributed_teachers" 
                                               value="{{ old('distributed_teachers', $distribution->distributed_teachers ?? 0) }}" 
                                               min="0" 
                                               required 
                                               oninput="recalculateTeacherRow()"
                                               class="w-28 text-center px-2 py-1.5 rounded-lg border-2 border-black bg-white font-black text-xs text-amber-800 focus:outline-none focus:ring-2 focus:ring-amber-400 shadow-[2px_2px_0px_0px_#000]">
                                    </td>
                                    <!-- Sisa Guru -->
                                    <td class="py-2.5 px-3 text-center">
                                        <input type="number" 
                                               id="teacher_leftover" 
                                               readonly 
                                               value="0"
                                               class="w-20 text-center px-2 py-1.5 rounded-lg border-2 border-black bg-slate-100 font-bold text-xs text-red-600 shadow-[1px_1px_0px_0px_#000]">
                                    </td>
                                    <td class="py-2.5 px-4 text-[11px] text-slate-600 italic">
                                        Penyaluran konsumsi untuk dewan guru dan staf TU
                                    </td>
                                </tr>
                            </tbody>

                            <!-- Kaki Tabel: Grand Total Ringkasan -->
                            <tfoot class="bg-slate-100 border-t-3 border-black font-black text-slate-900 text-xs">
                                <tr>
                                    <td colspan="2" class="py-3 px-4 uppercase font-heading tracking-wider">
                                        Total Seluruh Penyaluran (Siswa + Guru)
                                    </td>
                                    <td class="py-3 px-3 text-center text-sm font-heading" id="disp_grand_target">
                                        0
                                    </td>
                                    <td class="py-3 px-3 text-center text-sm font-heading text-emerald-700" id="disp_grand_dist">
                                        0
                                    </td>
                                    <td class="py-3 px-3 text-center text-sm font-heading text-red-600" id="disp_grand_leftover">
                                        0
                                    </td>
                                    <td class="py-3 px-4 text-[11px] text-slate-600 font-semibold" id="disp_grand_rate">
                                        Capaian: 0%
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>

            <hr class="border-t-2 border-black/10">

            <!-- Section 3: Catatan Umum -->
            <div>
                <label for="notes" class="block text-xs font-black uppercase tracking-wider text-slate-700 mb-1.5">
                    Catatan Umum / Keterangan Tambahan Serah Terima
                </label>
                <textarea id="notes" 
                          name="notes" 
                          rows="3" 
                          placeholder="Misal: Seluruh makanan diterima dalam kondisi baik pada pukul 10.30 WIB dari penyedia katering."
                          class="w-full px-4 py-2.5 rounded-xl border-2 border-black bg-slate-50 font-medium text-xs sm:text-sm focus:bg-white focus:outline-none focus:ring-2 focus:ring-amber-400 shadow-[2px_2px_0px_0px_#000]">{{ old('notes', $distribution->notes) }}</textarea>
            </div>

            <!-- Tombol Simpan -->
            <div class="pt-4 border-t-3 border-black flex items-center justify-between gap-3">
                <div class="text-xs font-bold text-slate-600 hidden sm:block">
                    Pastikan seluruh porsi kelas telah dicek sebelum menekan tombol simpan.
                </div>
                <div class="flex items-center gap-3">
                    <a href="{{ route('mbg.dashboard') }}" 
                       class="px-5 py-2.5 rounded-xl bg-slate-200 hover:bg-slate-300 text-slate-800 font-bold text-xs sm:text-sm border-2 border-black shadow-[2px_2px_0px_0px_#000] active:translate-x-0.5 active:translate-y-0.5 transition-all">
                        Batal
                    </a>
                    <button type="submit" 
                            class="px-6 py-2.5 rounded-xl bg-[#20C997] hover:bg-[#1BA87E] text-black font-black text-xs sm:text-sm border-2 border-black shadow-[3px_3px_0px_0px_#000] active:translate-x-0.5 active:translate-y-0.5 transition-all flex items-center gap-2">
                        <span>💾</span>
                        <span>{{ $isEdit ? 'Simpan Perubahan Per Kelas' : 'Simpan Distribusi MBG Per Kelas' }}</span>
                    </button>
                </div>
            </div>
        </form>
    </div>

</div>

@push('scripts')
<script>
    function recalculateRow(idx) {
        const targetInput = document.getElementById('class_target_' + idx);
        const distInput = document.getElementById('class_dist_' + idx);
        const leftoverInput = document.getElementById('class_leftover_' + idx);

        if (targetInput && distInput && leftoverInput) {
            const target = parseInt(targetInput.value) || 0;
            const dist = parseInt(distInput.value) || 0;
            leftoverInput.value = Math.max(0, target - dist);
        }
        recalculateMatrix();
    }

    function recalculateTeacherRow() {
        const targetInput = document.getElementById('target_teachers');
        const distInput = document.getElementById('distributed_teachers');
        const leftoverInput = document.getElementById('teacher_leftover');

        if (targetInput && distInput && leftoverInput) {
            const target = parseInt(targetInput.value) || 0;
            const dist = parseInt(distInput.value) || 0;
            leftoverInput.value = Math.max(0, target - dist);
        }
        recalculateMatrix();
    }

    function recalculateMatrix() {
        let totalTarget = 0;
        let totalDist = 0;
        let totalLeftover = 0;

        // Sum across all class rows
        document.querySelectorAll('.class-target-input').forEach(el => {
            totalTarget += parseInt(el.value) || 0;
        });

        document.querySelectorAll('.class-dist-input').forEach(el => {
            totalDist += parseInt(el.value) || 0;
        });

        document.querySelectorAll('.class-leftover-input').forEach(el => {
            totalLeftover += parseInt(el.value) || 0;
        });

        // Add teachers
        const teacherTarget = parseInt(document.getElementById('target_teachers')?.value) || 0;
        const teacherDist = parseInt(document.getElementById('distributed_teachers')?.value) || 0;
        const teacherLeftover = Math.max(0, teacherTarget - teacherDist);

        const teacherLeftoverEl = document.getElementById('teacher_leftover');
        if (teacherLeftoverEl) {
            teacherLeftoverEl.value = teacherLeftover;
        }

        totalTarget += teacherTarget;
        totalDist += teacherDist;
        totalLeftover += teacherLeftover;

        // Update footer displays
        document.getElementById('disp_grand_target').innerText = totalTarget + ' porsi';
        document.getElementById('disp_grand_dist').innerText = totalDist + ' porsi';
        document.getElementById('disp_grand_leftover').innerText = totalLeftover + ' porsi';

        const rate = totalTarget > 0 ? ((totalDist / totalTarget) * 100).toFixed(1) : '0';
        document.getElementById('disp_grand_rate').innerText = 'Capaian: ' + rate + '%';
    }

    function previewImage(input) {
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                document.getElementById('image_preview').src = e.target.result;
                document.getElementById('image_preview_box').classList.remove('hidden');
            }
            reader.readAsDataURL(input.files[0]);
        }
    }

    // Run on initial load
    document.addEventListener('DOMContentLoaded', function() {
        recalculateTeacherRow();
        recalculateMatrix();
    });
</script>
@endpush
@endsection
