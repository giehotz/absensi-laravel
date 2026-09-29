@extends('layouts.guru')

@section('title', 'Buat Jurnal Harian Mengajar')
@section('page-title', 'Form Jurnal Mengajar')

@section('content')
<div class="max-w-4xl mx-auto space-y-6 pb-12">
    <!-- Header Back Navigation -->
    <div class="flex items-center justify-between bg-white border-2 border-black p-4 rounded-lg shadow-[4px_4px_0px_0px_#000]">
        <div class="flex items-center gap-3">
            <a href="{{ route('guru.teaching-journals.index') }}" 
               class="neo-btn bg-slate-100 hover:bg-slate-200 text-black px-3 py-1.5 text-xs font-black flex items-center gap-1.5 shadow-[2px_2px_0px_0px_#000]">
                <span>←</span> Kembali
            </a>
            <div>
                <h2 class="font-heading font-black text-xl text-black">Input Jurnal Mengajar Harian</h2>
                <p class="text-xs text-slate-600">Lengkapi agenda KBM dan pastikan presensi kelas telah selesai diinput.</p>
            </div>
        </div>
        <span class="bg-[#FFD43B] text-black text-[10px] font-black uppercase px-2.5 py-1 border border-black shadow-[1px_1px_0px_0px_#000]">
            KBM Harian
        </span>
    </div>

    @if(session('error_attendance'))
    <div class="bg-[#FFF4E6] border-3 border-black p-5 rounded-lg shadow-[4px_4px_0px_0px_#000] flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div class="flex items-start gap-3">
            <span class="w-8 h-8 rounded-full bg-[#FF922B] text-white font-black text-base flex items-center justify-center border-2 border-black shrink-0">!</span>
            <div>
                <div class="font-heading font-black text-sm text-black">Prasyarat Belum Terpenuhi: Presensi Siswa Wajib Diisi</div>
                <p class="text-xs text-amber-900 mt-0.5">{{ session('error_attendance')['message'] }}</p>
            </div>
        </div>
        <a href="{{ session('error_attendance')['url'] }}" 
           class="neo-btn bg-[#FF922B] hover:bg-[#f76707] text-white text-xs font-black px-4 py-2 flex items-center justify-center gap-2 shadow-[2px_2px_0px_0px_#000] shrink-0">
            <span>📝 Isi Presensi Sekarang →</span>
        </a>
    </div>
    @endif

    <!-- Form Utama Jurnal -->
    <form action="{{ route('guru.teaching-journals.store') }}" method="POST" id="journalForm" class="space-y-6">
        @csrf

        <!-- Card 1: Identitas KBM & Jadwal -->
        <div class="bg-white border-2 border-black p-5 rounded-lg shadow-[4px_4px_0px_0px_#000] space-y-4">
            <div class="border-b-2 border-black pb-2 flex items-center justify-between">
                <span class="font-heading font-black text-sm uppercase tracking-wider text-black">
                    1. Identitas Kelas & Tanggal KBM
                </span>
                <span class="text-[11px] font-bold text-slate-500">Pilih jadwal atau tentukan manual</span>
            </div>

            <!-- Pilih Jadwal Cepat (Jika Ada) -->
            @if($schedules->isNotEmpty())
            <div>
                <label class="block text-xs font-bold text-black uppercase tracking-wider mb-1.5">
                    Pilih Dari Jadwal Mengajar Anda (Opsional)
                </label>
                <select id="scheduleSelect" name="schedule_id" class="w-full neo-input px-3 py-2 text-xs bg-slate-50 font-bold focus:bg-white">
                    <option value="">-- Pilih Jadwal Mengajar (Otomatis isi Kelas & Mapel) --</option>
                    @foreach($schedules as $sch)
                        @php
                            $days = [1 => 'Senin', 2 => 'Selasa', 3 => 'Rabu', 4 => 'Kamis', 5 => 'Jumat', 6 => 'Sabtu', 7 => 'Minggu'];
                            $dayName = $days[$sch->day_of_week] ?? 'Hari ' . $sch->day_of_week;
                        @endphp
                        <option value="{{ $sch->id }}" 
                                data-class-id="{{ $sch->school_class_id }}" 
                                data-subject-id="{{ $sch->subject_id }}"
                                {{ (string)$selectedScheduleId === (string)$sch->id ? 'selected' : '' }}>
                            {{ $dayName }} | {{ $sch->schoolClass?->name }} - {{ $sch->subject?->name }} ({{ substr($sch->start_time, 0, 5) }} - {{ substr($sch->end_time, 0, 5) }})
                        </option>
                    @endforeach
                </select>
            </div>
            @endif

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <!-- Tanggal KBM -->
                <div>
                    <label class="block text-xs font-bold text-black uppercase tracking-wider mb-1.5">
                        Tanggal KBM <span class="text-rose-600">*</span>
                    </label>
                    <input type="date" 
                           id="dateInput" 
                           name="date" 
                           value="{{ old('date', $date) }}" 
                           required 
                           class="w-full neo-input px-3 py-2 text-xs bg-slate-50 font-bold focus:bg-white @error('date') border-rose-500 @enderror">
                    @error('date')
                        <p class="text-[11px] text-rose-600 font-bold mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Kelas -->
                <div>
                    <label class="block text-xs font-bold text-black uppercase tracking-wider mb-1.5">
                        Kelas <span class="text-rose-600">*</span>
                    </label>
                    <select id="classSelect" name="school_class_id" required class="w-full neo-input px-3 py-2 text-xs bg-slate-50 font-bold focus:bg-white @error('school_class_id') border-rose-500 @enderror">
                        <option value="">-- Pilih Kelas --</option>
                        @foreach($classes as $c)
                            <option value="{{ $c->id }}" {{ (string)old('school_class_id', $selectedClassId) === (string)$c->id ? 'selected' : '' }}>
                                {{ $c->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('school_class_id')
                        <p class="text-[11px] text-rose-600 font-bold mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Mata Pelajaran -->
                <div>
                    <label class="block text-xs font-bold text-black uppercase tracking-wider mb-1.5">
                        Mata Pelajaran <span class="text-rose-600">*</span>
                    </label>
                    <select id="subjectSelect" name="subject_id" required class="w-full neo-input px-3 py-2 text-xs bg-slate-50 font-bold focus:bg-white @error('subject_id') border-rose-500 @enderror">
                        <option value="">-- Pilih Mata Pelajaran --</option>
                        @foreach($subjects as $s)
                            <option value="{{ $s->id }}" {{ (string)old('subject_id', $selectedSubjectId) === (string)$s->id ? 'selected' : '' }}>
                                {{ $s->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('subject_id')
                        <p class="text-[11px] text-rose-600 font-bold mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>

        <!-- Card 2: Box Dinamis Verifikasi & Status Presensi Siswa -->
        <div id="attendanceVerificationBox" class="transition-all duration-300">
            <!-- State 1: Loading Spinner -->
            <div id="attendanceLoading" class="hidden bg-slate-100 border-2 border-black p-4 rounded-lg text-center font-bold text-xs text-slate-700">
                ⏳ Memeriksa data presensi siswa kelas pada tanggal terpilih...
            </div>

            <!-- State 2: Belum Pilih Kelas/Tanggal -->
            <div id="attendancePrompt" class="bg-blue-50 border-2 border-black p-4 rounded-lg shadow-[3px_3px_0px_0px_#000] text-xs font-medium text-blue-950 flex items-center gap-3">
                <span class="text-xl">ℹ️</span>
                <span>Pilih <strong>Tanggal</strong> dan <strong>Kelas</strong> di atas untuk memverifikasi data presensi siswa secara otomatis.</span>
            </div>

            <!-- State 3: Presensi BELUM DIISI (Warning Banner + Structured CTA) -->
            <div id="attendanceMissing" class="hidden bg-[#FFE3E3] border-3 border-black p-5 rounded-lg shadow-[4px_4px_0px_0px_#000] space-y-3">
                <div class="flex items-start gap-3">
                    <div class="w-8 h-8 rounded-full bg-[#FF6B6B] border-2 border-black flex items-center justify-center font-black text-white shrink-0">✕</div>
                    <div>
                        <div class="font-heading font-black text-base text-rose-950">Presensi Siswa Belum Diisi!</div>
                        <p class="text-xs text-rose-900 mt-0.5">
                            Data absensi untuk kelas dan tanggal ini belum diisi oleh siapapun (guru, wali kelas, atau admin). Jurnal mengajar hanya dapat disimpan apabila presensi siswa telah dicatat.
                        </p>
                    </div>
                </div>
                <div class="pt-2 border-t border-rose-300 flex flex-wrap items-center justify-between gap-3">
                    <span class="text-xs font-bold text-rose-950">Langkah yang perlu dilakukan:</span>
                    <!-- Structured CTA Button -->
                    <a id="ctaManualAttendanceBtn" 
                       href="#" 
                       target="_blank"
                       class="neo-btn bg-[#FF6B6B] hover:bg-[#fa5252] text-white px-4 py-2 text-xs font-black flex items-center gap-2 shadow-[2px_2px_0px_0px_#000] cursor-pointer">
                        <span>📝 Isi Presensi Siswa Sekarang →</span>
                    </a>
                </div>
            </div>

            <!-- State 4: Presensi SUDAH DIISI (Verified & Locked Auto-Filled Stats) -->
            <div id="attendanceSuccess" class="hidden bg-[#D3F9D8] border-3 border-black p-5 rounded-lg shadow-[4px_4px_0px_0px_#000] space-y-3">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2.5">
                        <span class="w-7 h-7 rounded-full bg-[#20C997] border-2 border-black flex items-center justify-center font-black text-black shrink-0 text-sm">✓</span>
                        <span class="font-heading font-black text-sm text-emerald-950">Presensi Siswa Terverifikasi (Terkunci Otomatis)</span>
                    </div>
                    <span class="text-[10px] font-black uppercase tracking-wider bg-white px-2 py-0.5 border border-black rounded shadow-[1px_1px_0px_0px_#000]">
                        Sistem Otomatis
                    </span>
                </div>

                <div class="grid grid-cols-2 sm:grid-cols-6 gap-2.5 pt-1">
                    <div class="bg-white border-2 border-black p-2 rounded text-center shadow-[1px_1px_0px_0px_#000]">
                        <div class="text-[10px] font-black uppercase text-slate-500">Total Siswa</div>
                        <div id="statTotalStudents" class="font-mono font-black text-base text-black mt-0.5">0</div>
                    </div>
                    <div class="bg-white border-2 border-black p-2 rounded text-center shadow-[1px_1px_0px_0px_#000]">
                        <div class="text-[10px] font-black uppercase text-emerald-700">Hadir (H)</div>
                        <div id="statHadir" class="font-mono font-black text-base text-emerald-700 mt-0.5">0</div>
                    </div>
                    <div class="bg-white border-2 border-black p-2 rounded text-center shadow-[1px_1px_0px_0px_#000]">
                        <div class="text-[10px] font-black uppercase text-amber-700">Sakit (S)</div>
                        <div id="statSakit" class="font-mono font-black text-base text-amber-700 mt-0.5">0</div>
                    </div>
                    <div class="bg-white border-2 border-black p-2 rounded text-center shadow-[1px_1px_0px_0px_#000]">
                        <div class="text-[10px] font-black uppercase text-blue-700">Izin (I)</div>
                        <div id="statIzin" class="font-mono font-black text-base text-blue-700 mt-0.5">0</div>
                    </div>
                    <div class="bg-white border-2 border-black p-2 rounded text-center shadow-[1px_1px_0px_0px_#000]">
                        <div class="text-[10px] font-black uppercase text-rose-700">Alpa (A)</div>
                        <div id="statAlpa" class="font-mono font-black text-base text-rose-700 mt-0.5">0</div>
                    </div>
                    <div class="bg-emerald-100 border-2 border-black p-2 rounded text-center shadow-[1px_1px_0px_0px_#000]">
                        <div class="text-[10px] font-black uppercase text-emerald-950">% Kehadiran</div>
                        <div id="statPercentage" class="font-mono font-black text-base text-emerald-950 mt-0.5">0%</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Card 3: Rincian Kegiatan Mengajar (KBM) -->
        <div class="bg-white border-2 border-black p-5 rounded-lg shadow-[4px_4px_0px_0px_#000] space-y-4">
            <div class="border-b-2 border-black pb-2 flex items-center justify-between">
                <span class="font-heading font-black text-sm uppercase tracking-wider text-black">
                    2. Rincian Agenda & Evaluasi KBM
                </span>
                <span class="text-[11px] font-bold text-slate-500">Isi sesuai materi pembelajaran</span>
            </div>

            <!-- Pertemuan Ke- -->
            <div class="w-full sm:w-1/3">
                <label class="block text-xs font-bold text-black uppercase tracking-wider mb-1.5">
                    Pertemuan Ke- <span class="text-rose-600">*</span>
                </label>
                <div class="flex items-center gap-2">
                    <input type="number" 
                           id="meetingNumberInput" 
                           name="meeting_number" 
                           value="{{ old('meeting_number', 1) }}" 
                           min="1" 
                           required 
                           class="w-full neo-input px-3 py-2 text-sm font-mono font-black bg-slate-50 focus:bg-white @error('meeting_number') border-rose-500 @enderror">
                    <span class="text-[11px] text-slate-500 font-bold whitespace-nowrap">(Otomatis/manual)</span>
                </div>
                @error('meeting_number')
                    <p class="text-[11px] text-rose-600 font-bold mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Tujuan Pembelajaran -->
            <div>
                <label class="block text-xs font-bold text-black uppercase tracking-wider mb-1.5">
                    Tujuan Pembelajaran <span class="text-rose-600">*</span>
                </label>
                <textarea name="learning_objective" 
                          rows="3" 
                          required 
                          placeholder="Contoh: Peserta didik mampu memahami konsep dasar perkalian aljabar dan menerapkannya dalam menyelesaikan soal cerita..." 
                          class="w-full neo-input px-3 py-2.5 text-xs font-medium bg-slate-50 focus:bg-white @error('learning_objective') border-rose-500 @enderror">{{ old('learning_objective') }}</textarea>
                @error('learning_objective')
                    <p class="text-[11px] text-rose-600 font-bold mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Kegiatan Belajar Mengajar -->
            <div>
                <label class="block text-xs font-bold text-black uppercase tracking-wider mb-1.5">
                    Kegiatan Belajar Mengajar (KBM) <span class="text-rose-600">*</span>
                </label>
                <textarea name="teaching_activity" 
                          rows="4" 
                          required 
                          placeholder="Contoh: 1. Pembukaan dan apersepsi materi aljabar. 2. Eksplorasi kelompok menggunakan LKS. 3. Presentasi perwakilan siswa dan refleksi bersama..." 
                          class="w-full neo-input px-3 py-2.5 text-xs font-medium bg-slate-50 focus:bg-white @error('teaching_activity') border-rose-500 @enderror">{{ old('teaching_activity') }}</textarea>
                @error('teaching_activity')
                    <p class="text-[11px] text-rose-600 font-bold mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Permasalahan Dalam Proses KBM (Internal) -->
            <div>
                <div class="flex items-center justify-between mb-1.5">
                    <label class="block text-xs font-bold text-black uppercase tracking-wider">
                        Permasalahan Dalam Proses KBM (Opsional)
                    </label>
                    <span class="text-[10px] font-bold text-amber-900 bg-amber-100 px-2 py-0.5 border border-amber-300 rounded">
                        🔒 Internal (Hanya Anda & Admin)
                    </span>
                </div>
                <textarea name="teaching_problem" 
                          rows="3" 
                          placeholder="Catatan kendala, kesulitan pemahaman siswa tertentu, atau fasilitas KBM yang bermasalah. (Kolom ini tidak ditampilkan ke siswa)" 
                          class="w-full neo-input px-3 py-2.5 text-xs font-medium bg-slate-50 focus:bg-white @error('teaching_problem') border-rose-500 @enderror">{{ old('teaching_problem') }}</textarea>
                @error('teaching_problem')
                    <p class="text-[11px] text-rose-600 font-bold mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Toggle Switch Visibilitas Siswa -->
            <div class="pt-3 border-t-2 border-black/10 flex items-center justify-between">
                <div>
                    <div class="font-heading font-black text-xs text-black">Tampilkan Jurnal Ini ke Siswa di Kelas Terkait?</div>
                    <p class="text-[11px] text-slate-500">Siswa dapat melihat ringkasan materi KBM dan tujuan pembelajaran (permasalahan internal tetap disembunyikan).</p>
                </div>
                <label class="relative inline-flex items-center cursor-pointer">
                    <input type="checkbox" name="is_shared_with_students" value="1" {{ old('is_shared_with_students', '0') == '1' ? 'checked' : '' }} class="sr-only peer">
                    <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none border-2 border-black rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-black after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-2 after:border-black after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-[#20C997]"></div>
                </label>
            </div>
        </div>

        <!-- Tombol Aksi Submit -->
        <div class="flex items-center justify-between gap-4">
            <a href="{{ route('guru.teaching-journals.index') }}" class="neo-btn bg-slate-100 hover:bg-slate-200 text-black px-4 py-2.5 text-xs font-bold shadow-[2px_2px_0px_0px_#000]">
                Batal
            </a>

            <div class="flex items-center gap-3">
                <span id="submitWarningNote" class="text-xs font-bold text-rose-600 hidden">
                    ⚠️ Presensi siswa belum diverifikasi!
                </span>
                <button type="submit" 
                        id="submitJournalBtn"
                        class="neo-btn bg-[#FFD43B] hover:bg-[#fcc419] text-black px-6 py-2.5 text-xs font-black flex items-center gap-2 shadow-[3px_3px_0px_0px_#000] cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                    </svg>
                    <span>Simpan Jurnal Mengajar</span>
                </button>
            </div>
        </div>
    </form>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const scheduleSelect = document.getElementById('scheduleSelect');
        const classSelect = document.getElementById('classSelect');
        const subjectSelect = document.getElementById('subjectSelect');
        const dateInput = document.getElementById('dateInput');
        const meetingNumberInput = document.getElementById('meetingNumberInput');

        const attendanceLoading = document.getElementById('attendanceLoading');
        const attendancePrompt = document.getElementById('attendancePrompt');
        const attendanceMissing = document.getElementById('attendanceMissing');
        const attendanceSuccess = document.getElementById('attendanceSuccess');
        const ctaManualAttendanceBtn = document.getElementById('ctaManualAttendanceBtn');

        const statTotalStudents = document.getElementById('statTotalStudents');
        const statHadir = document.getElementById('statHadir');
        const statSakit = document.getElementById('statSakit');
        const statIzin = document.getElementById('statIzin');
        const statAlpa = document.getElementById('statAlpa');
        const statPercentage = document.getElementById('statPercentage');

        const submitJournalBtn = document.getElementById('submitJournalBtn');
        const submitWarningNote = document.getElementById('submitWarningNote');

        let isAttendanceVerified = false;

        // Jika jadwal dipilih, autofill kelas dan mapel
        if (scheduleSelect) {
            scheduleSelect.addEventListener('change', function () {
                const opt = this.options[this.selectedIndex];
                if (opt && opt.value) {
                    const classId = opt.getAttribute('data-class-id');
                    const subjectId = opt.getAttribute('data-subject-id');
                    if (classId) classSelect.value = classId;
                    if (subjectId) subjectSelect.value = subjectId;
                    checkAttendanceData();
                }
            });
        }

        // Listener perubahan kelas, mapel, atau tanggal
        classSelect.addEventListener('change', checkAttendanceData);
        dateInput.addEventListener('change', checkAttendanceData);
        subjectSelect.addEventListener('change', checkAttendanceData);

        function checkAttendanceData() {
            const classId = classSelect.value;
            const date = dateInput.value;
            const subjectId = subjectSelect.value;

            if (!classId || !date) {
                showState('prompt');
                setSubmitEnabled(false);
                return;
            }

            showState('loading');
            setSubmitEnabled(false);

            const url = `{{ route('guru.teaching-journals.check-attendance') }}?school_class_id=${classId}&date=${date}&subject_id=${subjectId}`;

            fetch(url, {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(res => res.json())
            .then(data => {
                if (!data.success) {
                    showState('prompt');
                    setSubmitEnabled(false);
                    return;
                }

                if (data.has_attendance) {
                    // Isi statistik
                    statTotalStudents.textContent = data.stats.total_students;
                    statHadir.textContent = data.stats.count_hadir + (data.stats.count_terlambat > 0 ? ` (+${data.stats.count_terlambat} terlambat)` : '');
                    statSakit.textContent = data.stats.count_sakit;
                    statIzin.textContent = data.stats.count_izin;
                    statAlpa.textContent = data.stats.count_alpa;
                    statPercentage.textContent = `${data.stats.attendance_percentage}%`;

                    // Rekomendasikan nomor pertemuan jika form masih default
                    if (data.next_meeting && (!meetingNumberInput.value || meetingNumberInput.value == '1')) {
                        meetingNumberInput.value = data.next_meeting;
                    }

                    showState('success');
                    setSubmitEnabled(true);
                } else {
                    // Update CTA link
                    if (ctaManualAttendanceBtn && data.manual_attendance_url) {
                        ctaManualAttendanceBtn.href = data.manual_attendance_url;
                    }
                    showState('missing');
                    setSubmitEnabled(false);
                }
            })
            .catch(err => {
                console.error('Error checking attendance:', err);
                showState('prompt');
                setSubmitEnabled(false);
            });
        }

        function showState(state) {
            attendanceLoading.classList.add('hidden');
            attendancePrompt.classList.add('hidden');
            attendanceMissing.classList.add('hidden');
            attendanceSuccess.classList.add('hidden');

            if (state === 'loading') attendanceLoading.classList.remove('hidden');
            if (state === 'prompt') attendancePrompt.classList.remove('hidden');
            if (state === 'missing') attendanceMissing.classList.remove('hidden');
            if (state === 'success') attendanceSuccess.classList.remove('hidden');
        }

        function setSubmitEnabled(enabled) {
            isAttendanceVerified = enabled;
            if (enabled) {
                submitJournalBtn.removeAttribute('disabled');
                submitWarningNote.classList.add('hidden');
            } else {
                submitJournalBtn.setAttribute('disabled', 'disabled');
                submitWarningNote.classList.remove('hidden');
            }
        }

        // Jalankan pengecekan pertama kali jika sudah ada nilai kelas & tanggal awal
        if (classSelect.value && dateInput.value) {
            checkAttendanceData();
        } else {
            setSubmitEnabled(false);
        }
    });
</script>
@endsection
