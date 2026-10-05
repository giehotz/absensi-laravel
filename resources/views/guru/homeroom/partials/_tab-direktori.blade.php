<!-- TAB 1: Direktori Siswa & Kontak Orang Tua (Adapted, Optimized, Colorized Neo-Brutalism) -->
<div id="tabContent-direktori" class="tab-pane space-y-4">
    <div class="bg-white neo-box overflow-hidden border-2 border-black shadow-[3px_3px_0px_0px_#000]">
        <!-- 1. DESKTOP VIEW: Structured Tabular (Hidden on Mobile) -->
        <div class="hidden md:block overflow-x-auto">
            <table class="w-full text-left text-sm border-collapse">
                <thead class="bg-slate-100 border-b-2 border-black text-xs font-heading font-black uppercase text-black">
                    <tr>
                        <th class="p-3 w-12 text-center border-r-2 border-black">No</th>
                        <th class="p-3 border-r-2 border-black min-w-[200px]">Siswa</th>
                        <th class="p-3 w-14 text-center border-r-2 border-black">L/P</th>
                        <th class="p-3 text-center border-r-2 border-black min-w-[130px]">Status Hari Ini</th>
                        <th class="p-3 border-r-2 border-black min-w-[150px]">Kontak Siswa</th>
                        <th class="p-3 border-r-2 border-black min-w-[240px]">Orang Tua / Wali</th>
                        <th class="p-3 text-center w-36">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y-2 divide-black/15">
                    @forelse($students as $index => $stu)
                        @php
                            $todayAtt = $stu->attendances->first();
                            $firstParent = $stu->parents->first();
                            $parentName = $firstParent?->user?->name ?? '-';
                            $parentRelation = $firstParent?->relation ? ucfirst($firstParent->relation) : '-';
                            $parentPhone = $firstParent?->phone ?? '';
                            
                            // Format no WhatsApp
                            $cleanPhone = preg_replace('/[^0-9]/', '', $parentPhone);
                            if (str_starts_with($cleanPhone, '0')) {
                                $cleanPhone = '62' . substr($cleanPhone, 1);
                            }
                            $waUrl = $cleanPhone ? 'https://wa.me/' . $cleanPhone . '?text=' . urlencode("Halo Bapak/Ibu {$parentName}, saya Wali Kelas {$selectedClass->name} ingin menginformasikan terkait kehadiran siswa an. {$stu->user->name}.") : null;
                        @endphp
                        <tr class="hover:bg-amber-50/50 transition-colors text-xs">
                            <td class="p-3 text-center font-mono font-bold border-r-2 border-black/15 text-slate-700">
                                {{ $index + 1 }}
                            </td>
                            <td class="p-3 border-r-2 border-black/15">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-12 rounded-xs border-2 border-black overflow-hidden shrink-0 bg-slate-100 flex items-center justify-center shadow-[1.5px_1.5px_0px_#000]">
                                        @if($stu->photo)
                                            <img src="{{ $stu->photo_url }}" alt="{{ $stu->user->name ?? 'Foto Siswa' }}" class="w-full h-full object-cover">
                                        @else
                                            <div class="w-full h-full bg-[#5294FF] text-white font-heading font-black text-xs flex items-center justify-center">
                                                {{ strtoupper(substr($stu->user->name ?? 'S', 0, 2)) }}
                                            </div>
                                        @endif
                                    </div>
                                    <div class="min-w-0">
                                        <div class="font-heading font-black text-black text-sm truncate">{{ $stu->user->name ?? '-' }}</div>
                                        <div class="flex items-center gap-2 mt-0.5 text-xs text-slate-600 font-mono">
                                            <span>NIS: <b>{{ $stu->nis }}</b></span>
                                            <span>•</span>
                                            <span>NISN: {{ $stu->nisn ?? '-' }}</span>
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="p-3 text-center font-heading font-black text-xs border-r-2 border-black/15">
                                <span class="inline-block px-1.5 py-0.5 rounded-xs border border-black shadow-[1px_1px_0px_#000] {{ $stu->gender == 'L' ? 'bg-blue-100 text-blue-950' : 'bg-pink-100 text-pink-950' }}">
                                    {{ $stu->gender }}
                                </span>
                            </td>
                            <td class="p-3 text-center border-r-2 border-black/15">
                                @if($todayAtt)
                                    <span class="text-[10px] font-heading font-black uppercase px-2 py-1 rounded-xs border border-black shadow-[1px_1px_0px_#000]
                                        @if($todayAtt->status === 'hadir') bg-[#20C997] text-white 
                                        @elseif($todayAtt->status === 'terlambat') bg-[#339AF0] text-white 
                                        @elseif($todayAtt->status === 'izin') bg-[#868E96] text-white 
                                        @elseif($todayAtt->status === 'sakit') bg-[#FFD43B] text-black 
                                        @else bg-[#FF6B6B] text-white @endif">
                                        {{ strtoupper($todayAtt->status) }}
                                    </span>
                                @else
                                    <span class="text-[10px] font-heading font-black uppercase px-2 py-1 rounded-xs bg-slate-100 text-slate-600 border border-slate-400">
                                        BELUM HADIR
                                    </span>
                                @endif
                            </td>
                            <td class="p-3 border-r-2 border-black/15 font-mono text-xs">
                                @if($stu->phone)
                                    <div class="font-bold text-slate-900">{{ $stu->phone }}</div>
                                @else
                                    <span class="text-slate-400 italic font-sans">Tidak ada</span>
                                @endif
                            </td>
                            <td class="p-3 border-r-2 border-black/15">
                                @if($firstParent)
                                    <div class="space-y-1">
                                        <div class="font-bold text-black text-xs flex items-center gap-1.5">
                                            <span class="font-heading font-black">{{ $parentName }}</span>
                                            <span class="text-[10px] bg-slate-100 border border-black/30 px-1 py-0.2 rounded-xs text-slate-700 font-semibold">
                                                {{ $parentRelation }}
                                            </span>
                                        </div>
                                        <div class="flex items-center gap-2">
                                            <span class="font-mono text-xs text-slate-700 font-bold">{{ $parentPhone ?: '-' }}</span>
                                            @if($waUrl)
                                                <a href="{{ $waUrl }}" target="_blank" 
                                                   title="Hubungi Wali Murid via WhatsApp"
                                                   class="neo-btn bg-[#20C997] hover:bg-emerald-400 text-black text-[10px] font-heading font-black px-2 py-0.5 inline-flex items-center gap-1 border border-black shadow-[1px_1px_0px_#000] cursor-pointer">
                                                    <span>💬</span> WhatsApp
                                                </a>
                                            @endif
                                        </div>
                                    </div>
                                @else
                                    <span class="text-xs text-slate-400 italic">Belum ditautkan</span>
                                @endif
                            </td>
                            <td class="p-3 text-center">
                                <div class="flex items-center justify-center gap-1.5">
                                    <!-- 1. Detail Profil -->
                                    <button type="button" 
                                            onclick="openStudentDetailModal({{ json_encode([
                                                'id' => $stu->id,
                                                'name' => $stu->user->name ?? '-',
                                                'nis' => $stu->nis,
                                                'nisn' => $stu->nisn ?? '-',
                                                'gender' => $stu->gender == 'L' ? 'Laki-laki' : 'Perempuan',
                                                'birth_date' => $stu->birth_date ? \Carbon\Carbon::parse($stu->birth_date)->translatedFormat('d F Y') : '-',
                                                'phone' => $stu->phone ?? '-',
                                                'class_name' => $selectedClass->name,
                                                'parent_name' => $parentName,
                                                'parent_relation' => $parentRelation,
                                                'parent_phone' => $parentPhone,
                                                'wa_url' => $waUrl,
                                                'm_hadir' => $stu->month_hadir,
                                                'm_terlambat' => $stu->month_terlambat,
                                                'm_izin' => $stu->month_izin,
                                                'm_sakit' => $stu->month_sakit,
                                                'm_alpa' => $stu->month_alpa,
                                                'm_total' => $stu->month_total,
                                                'photo_url' => $stu->photo ? $stu->photo_url : null,
                                                'initials' => strtoupper(substr($stu->user->name ?? 'S', 0, 2)),
                                            ]) }})"
                                            class="neo-btn bg-white hover:bg-slate-100 text-black p-1.5 text-xs cursor-pointer border border-black shadow-[1.5px_1.5px_0px_#000] transition-transform active:scale-90"
                                            title="Lihat Detail Profil Siswa" aria-label="Lihat Detail Profil Siswa">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                        </svg>
                                    </button>

                                    <!-- 2. Edit Siswa -->
                                    <button type="button" 
                                            onclick="editStudentHomeroom({{ json_encode([
                                                'id' => $stu->id,
                                                'name' => $stu->user->name ?? '',
                                                'email' => $stu->user->email ?? '',
                                                'nis' => $stu->nis,
                                                'nisn' => $stu->nisn ?? '',
                                                'school_class_id' => $stu->school_class_id,
                                                'class_name' => $selectedClass->name,
                                                'gender' => $stu->gender,
                                                'birth_place' => $stu->birth_place ?? '',
                                                'birth_date' => $stu->birth_date ? \Carbon\Carbon::parse($stu->birth_date)->format('Y-m-d') : '',
                                                'religion' => $stu->religion ?? '',
                                                'phone' => $stu->phone ?? '',
                                                'address' => $stu->address ?? '',
                                                'family_status' => $stu->family_status ?? '',
                                                'child_number' => $stu->child_number ?? '',
                                                'previous_school' => $stu->previous_school ?? '',
                                                'admission_date' => $stu->admission_date ? \Carbon\Carbon::parse($stu->admission_date)->format('Y-m-d') : '',
                                                'entry_grade' => $stu->entry_grade ?? '',
                                                'father_name' => $stu->father_name ?? '',
                                                'father_job' => $stu->father_job ?? '',
                                                'mother_name' => $stu->mother_name ?? '',
                                                'mother_job' => $stu->mother_job ?? '',
                                                'parent_address' => $stu->parent_address ?? '',
                                                'guardian_name' => $stu->guardian_name ?? '',
                                                'guardian_job' => $stu->guardian_job ?? '',
                                                'guardian_address' => $stu->guardian_address ?? '',
                                                'photo_url' => $stu->photo ? $stu->photo_url : null,
                                            ]) }})"
                                            class="neo-btn bg-[#FFD43B] hover:bg-yellow-400 text-black p-1.5 text-xs cursor-pointer border border-black shadow-[1.5px_1.5px_0px_#000] transition-transform active:scale-90"
                                            title="Edit Data Siswa" aria-label="Edit Data Siswa">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                        </svg>
                                    </button>

                                    <!-- 3. Reset Password Akun Siswa -->
                                    @php
                                        $resetPassLabel = !empty($stu->nisn) ? 'NISN: '.$stu->nisn : (!empty($stu->nis) ? 'NIS: '.$stu->nis : 'default');
                                    @endphp
                                    <form action="{{ route('guru.classes.binaan.students.reset-password', $stu) }}" method="POST" class="inline">
                                        @csrf
                                        <button type="button" 
                                                onclick="confirmResetPasswordHomeroom(this, {{ json_encode($stu->user->name ?? 'Siswa') }}, {{ json_encode($resetPassLabel) }})"
                                                class="neo-btn bg-[#339AF0] hover:bg-blue-600 text-white p-1.5 text-xs cursor-pointer border border-black shadow-[1.5px_1.5px_0px_#000] transition-transform active:scale-90"
                                                title="Reset Password ({{ $resetPassLabel }})" aria-label="Reset Password">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
                                            </svg>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="p-8 text-center text-slate-500 font-semibold">
                                Tidak ada data siswa yang cocok dengan filter.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- 2. MOBILE VIEW: Adaptive Student Card System (block md:hidden) -->
        <div class="md:hidden p-3 sm:p-4 space-y-3.5 bg-slate-50">
            @forelse($students as $index => $stu)
                @php
                    $todayAtt = $stu->attendances->first();
                    $firstParent = $stu->parents->first();
                    $parentName = $firstParent?->user?->name ?? '-';
                    $parentRelation = $firstParent?->relation ? ucfirst($firstParent->relation) : '-';
                    $parentPhone = $firstParent?->phone ?? '';
                    
                    $cleanPhone = preg_replace('/[^0-9]/', '', $parentPhone);
                    if (str_starts_with($cleanPhone, '0')) {
                        $cleanPhone = '62' . substr($cleanPhone, 1);
                    }
                    $waUrl = $cleanPhone ? 'https://wa.me/' . $cleanPhone . '?text=' . urlencode("Halo Bapak/Ibu {$parentName}, saya Wali Kelas {$selectedClass->name} ingin menginformasikan terkait kehadiran siswa an. {$stu->user->name}.") : null;
                @endphp
                <div class="bg-white border-2 border-black rounded-lg p-4 space-y-3 shadow-[3px_3px_0px_0px_#000]">
                    <!-- Top Row: Student Identity & Gender -->
                    <div class="flex items-start justify-between gap-3 pb-2.5 border-b-2 border-black/10">
                        <div class="flex items-center gap-3 min-w-0">
                            <div class="w-11 h-13 rounded-xs border-2 border-black overflow-hidden shrink-0 bg-slate-100 flex items-center justify-center shadow-[1.5px_1.5px_0px_#000]">
                                @if($stu->photo)
                                    <img src="{{ $stu->photo_url }}" alt="{{ $stu->user->name ?? 'Foto Siswa' }}" class="w-full h-full object-cover">
                                @else
                                    <div class="w-full h-full bg-[#5294FF] text-white font-heading font-black text-xs flex items-center justify-center">
                                        {{ strtoupper(substr($stu->user->name ?? 'S', 0, 2)) }}
                                    </div>
                                @endif
                            </div>
                            <div class="min-w-0">
                                <div class="font-heading font-black text-sm text-black truncate leading-tight">
                                    {{ $stu->user->name ?? '-' }}
                                </div>
                                <div class="text-[11px] font-mono text-slate-600 mt-0.5">
                                    NIS: <strong>{{ $stu->nis }}</strong>
                                </div>
                                <div class="text-[10px] text-slate-500 font-mono">
                                    NISN: {{ $stu->nisn ?? '-' }}
                                </div>
                            </div>
                        </div>

                        <div class="flex flex-col items-end gap-1.5 shrink-0">
                            <span class="text-[10px] font-heading font-black uppercase px-2 py-0.5 rounded-xs border border-black shadow-[1px_1px_0px_#000] {{ $stu->gender == 'L' ? 'bg-blue-100 text-blue-950' : 'bg-pink-100 text-pink-950' }}">
                                {{ $stu->gender == 'L' ? 'Laki-laki' : 'Perempuan' }}
                            </span>
                            @if($todayAtt)
                                <span class="text-[10px] font-heading font-black uppercase px-2 py-0.5 rounded-xs border border-black shadow-[1px_1px_0px_#000]
                                    @if($todayAtt->status === 'hadir') bg-[#20C997] text-white 
                                    @elseif($todayAtt->status === 'terlambat') bg-[#339AF0] text-white 
                                    @elseif($todayAtt->status === 'izin') bg-[#868E96] text-white 
                                    @elseif($todayAtt->status === 'sakit') bg-[#FFD43B] text-black 
                                    @else bg-[#FF6B6B] text-white @endif">
                                    {{ strtoupper($todayAtt->status) }}
                                </span>
                            @else
                                <span class="text-[10px] font-heading font-black uppercase px-2 py-0.5 rounded-xs bg-slate-100 text-slate-600 border border-slate-300">
                                    Belum Hadir
                                </span>
                            @endif
                        </div>
                    </div>

                    <!-- Parent / Guardian Contact Box with Direct WhatsApp -->
                    <div class="p-3 bg-slate-50 border border-black rounded-xs space-y-2">
                        <div class="flex items-center justify-between text-xs">
                            <div>
                                <span class="text-[10px] font-heading font-black uppercase text-slate-500 block">Wali Murid:</span>
                                <div class="font-heading font-black text-black">
                                    {{ $parentName }} <span class="text-[10px] font-bold text-slate-600 font-sans">({{ $parentRelation }})</span>
                                </div>
                            </div>
                            <div class="text-right font-mono text-xs font-bold text-slate-700">
                                {{ $parentPhone ?: '-' }}
                            </div>
                        </div>

                        @if($waUrl)
                            <a href="{{ $waUrl }}" target="_blank" 
                               class="w-full neo-btn bg-[#20C997] hover:bg-emerald-400 text-black py-2 text-xs font-heading font-black flex items-center justify-center gap-1.5 border border-black rounded-xs shadow-[1.5px_1.5px_0px_#000] min-h-[42px]">
                                <span>💬</span>
                                <span>Kirim Pesan WhatsApp ke Wali Murid</span>
                            </a>
                        @endif
                    </div>

                    <!-- Bottom Action Buttons: Full-width Mobile Grid (min-h 44px) -->
                    <div class="grid grid-cols-3 gap-2 pt-1">
                        <button type="button" 
                                onclick="openStudentDetailModal({{ json_encode([
                                    'id' => $stu->id,
                                    'name' => $stu->user->name ?? '-',
                                    'nis' => $stu->nis,
                                    'nisn' => $stu->nisn ?? '-',
                                    'gender' => $stu->gender == 'L' ? 'Laki-laki' : 'Perempuan',
                                    'birth_date' => $stu->birth_date ? \Carbon\Carbon::parse($stu->birth_date)->translatedFormat('d F Y') : '-',
                                    'phone' => $stu->phone ?? '-',
                                    'class_name' => $selectedClass->name,
                                    'parent_name' => $parentName,
                                    'parent_relation' => $parentRelation,
                                    'parent_phone' => $parentPhone,
                                    'wa_url' => $waUrl,
                                    'm_hadir' => $stu->month_hadir,
                                    'm_terlambat' => $stu->month_terlambat,
                                    'm_izin' => $stu->month_izin,
                                    'm_sakit' => $stu->month_sakit,
                                    'm_alpa' => $stu->month_alpa,
                                    'm_total' => $stu->month_total,
                                    'photo_url' => $stu->photo ? $stu->photo_url : null,
                                    'initials' => strtoupper(substr($stu->user->name ?? 'S', 0, 2)),
                                ]) }})"
                                class="neo-btn bg-white hover:bg-slate-100 text-black py-2 text-xs font-heading font-black flex items-center justify-center gap-1 border border-black rounded-xs shadow-[1.5px_1.5px_0px_#000] min-h-[44px]">
                            <span>👁</span> Detail
                        </button>

                        <button type="button" 
                                onclick="editStudentHomeroom({{ json_encode([
                                    'id' => $stu->id,
                                    'name' => $stu->user->name ?? '',
                                    'email' => $stu->user->email ?? '',
                                    'nis' => $stu->nis,
                                    'nisn' => $stu->nisn ?? '',
                                    'school_class_id' => $stu->school_class_id,
                                    'class_name' => $selectedClass->name,
                                    'gender' => $stu->gender,
                                    'birth_place' => $stu->birth_place ?? '',
                                    'birth_date' => $stu->birth_date ? \Carbon\Carbon::parse($stu->birth_date)->format('Y-m-d') : '',
                                    'religion' => $stu->religion ?? '',
                                    'phone' => $stu->phone ?? '',
                                    'address' => $stu->address ?? '',
                                    'family_status' => $stu->family_status ?? '',
                                    'child_number' => $stu->child_number ?? '',
                                    'previous_school' => $stu->previous_school ?? '',
                                    'admission_date' => $stu->admission_date ? \Carbon\Carbon::parse($stu->admission_date)->format('Y-m-d') : '',
                                    'entry_grade' => $stu->entry_grade ?? '',
                                    'father_name' => $stu->father_name ?? '',
                                    'father_job' => $stu->father_job ?? '',
                                    'mother_name' => $stu->mother_name ?? '',
                                    'mother_job' => $stu->mother_job ?? '',
                                    'parent_address' => $stu->parent_address ?? '',
                                    'guardian_name' => $stu->guardian_name ?? '',
                                    'guardian_job' => $stu->guardian_job ?? '',
                                    'guardian_address' => $stu->guardian_address ?? '',
                                    'photo_url' => $stu->photo ? $stu->photo_url : null,
                                ]) }})"
                                class="neo-btn bg-[#FFD43B] hover:bg-yellow-400 text-black py-2 text-xs font-heading font-black flex items-center justify-center gap-1 border border-black rounded-xs shadow-[1.5px_1.5px_0px_#000] min-h-[44px]">
                            <span>✏️</span> Edit
                        </button>

                        @php
                            $resetPassLabel = !empty($stu->nisn) ? 'NISN: '.$stu->nisn : (!empty($stu->nis) ? 'NIS: '.$stu->nis : 'default');
                        @endphp
                        <form action="{{ route('guru.classes.binaan.students.reset-password', $stu) }}" method="POST" class="w-full">
                            @csrf
                            <button type="button" 
                                    onclick="confirmResetPasswordHomeroom(this, {{ json_encode($stu->user->name ?? 'Siswa') }}, {{ json_encode($resetPassLabel) }})"
                                    class="w-full neo-btn bg-[#339AF0] hover:bg-blue-600 text-white py-2 text-xs font-heading font-black flex items-center justify-center gap-1 border border-black rounded-xs shadow-[1.5px_1.5px_0px_#000] min-h-[44px]">
                                <span>🔑</span> Reset
                            </button>
                        </form>
                    </div>
                </div>
            @empty
                <div class="bg-white border-2 border-black p-8 text-center text-slate-500 rounded-lg">
                    Tidak ada data siswa yang cocok dengan kriteria pencarian.
                </div>
            @endforelse
        </div>
    </div>
</div>
