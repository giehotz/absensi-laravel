<!-- TAB 1: Direktori Siswa & Kontak Orang Tua -->
<div id="tabContent-direktori" class="tab-pane space-y-4">
    <div class="bg-white neo-box overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-slate-100 border-b-2 border-black text-xs font-black uppercase text-black">
                    <tr>
                        <th class="p-3 w-12 text-center border-r-2 border-black">No</th>
                        <th class="p-3 border-r-2 border-black">Siswa</th>
                        <th class="p-3 w-14 text-center border-r-2 border-black">L/P</th>
                        <th class="p-3 text-center border-r-2 border-black min-w-[140px]">Status Hari Ini</th>
                        <th class="p-3 border-r-2 border-black min-w-[200px]">Kontak Siswa</th>
                        <th class="p-3 border-r-2 border-black min-w-[240px]">Orang Tua / Wali</th>
                        <th class="p-3 text-center w-36">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y-2 divide-black">
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
                        <tr class="hover:bg-slate-50 transition-colors">
                            <td class="p-3 text-center font-mono font-bold text-xs border-r-2 border-black">
                                {{ $index + 1 }}
                            </td>
                            <td class="p-3 border-r-2 border-black">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-12 rounded-sm border-2 border-black overflow-hidden shrink-0 bg-slate-100 flex items-center justify-center shadow-[1.5px_1.5px_0px_#000]">
                                        @if($stu->photo)
                                            <img src="{{ $stu->photo_url }}" alt="{{ $stu->user->name ?? 'Foto Siswa' }}" class="w-full h-full object-cover">
                                        @else
                                            <div class="w-full h-full bg-[#5294FF] text-white font-black text-xs flex items-center justify-center">
                                                {{ strtoupper(substr($stu->user->name ?? 'S', 0, 2)) }}
                                            </div>
                                        @endif
                                    </div>
                                    <div>
                                        <div class="font-black text-black text-sm">{{ $stu->user->name ?? '-' }}</div>
                                        <div class="flex items-center gap-2 mt-0.5 text-xs text-slate-500 font-mono">
                                            <span>NIS: <b>{{ $stu->nis }}</b></span>
                                            <span>•</span>
                                            <span>NISN: {{ $stu->nisn ?? '-' }}</span>
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="p-3 text-center font-bold text-xs border-r-2 border-black">
                                <span class="inline-block px-1.5 py-0.5 rounded border border-black {{ $stu->gender == 'L' ? 'bg-blue-50 text-blue-900' : 'bg-pink-50 text-pink-900' }}">
                                    {{ $stu->gender }}
                                </span>
                            </td>
                            <td class="p-3 text-center border-r-2 border-black">
                                @if($todayAtt)
                                    <span class="neo-badge text-[11px]
                                        @if($todayAtt->status === 'hadir') bg-[#20C997] text-white 
                                        @elseif($todayAtt->status === 'terlambat') bg-[#339AF0] text-white 
                                        @elseif($todayAtt->status === 'izin') bg-[#868E96] text-white 
                                        @elseif($todayAtt->status === 'sakit') bg-[#FFD43B] text-black 
                                        @else bg-[#FF6B6B] text-white @endif">
                                        {{ strtoupper($todayAtt->status) }}
                                    </span>
                                @else
                                    <span class="neo-badge bg-slate-200 text-slate-700 text-[11px]">
                                        BELUM HADIR
                                    </span>
                                @endif
                            </td>
                            <td class="p-3 border-r-2 border-black font-mono text-xs">
                                @if($stu->phone)
                                    <div class="font-bold text-slate-800">{{ $stu->phone }}</div>
                                @else
                                    <span class="text-slate-400 italic">Tidak ada kontak</span>
                                @endif
                            </td>
                            <td class="p-3 border-r-2 border-black">
                                @if($firstParent)
                                    <div class="space-y-1">
                                        <div class="font-bold text-black text-xs flex items-center gap-1.5">
                                            <span>{{ $parentName }}</span>
                                            <span class="text-[10px] bg-slate-100 border border-slate-300 px-1 py-0.2 rounded text-slate-600 font-semibold">
                                                {{ $parentRelation }}
                                            </span>
                                        </div>
                                        <div class="flex items-center gap-2">
                                            <span class="font-mono text-xs text-slate-600">{{ $parentPhone ?: '-' }}</span>
                                            @if($waUrl)
                                                <a href="{{ $waUrl }}" target="_blank" 
                                                   title="Kirim pesan WhatsApp ke Wali Murid"
                                                   class="neo-btn bg-[#20C997] text-white text-[10px] font-bold px-2 py-0.5 inline-flex items-center gap-1 hover:bg-emerald-400">
                                                    <span>💬</span> WhatsApp
                                                </a>
                                            @endif
                                        </div>
                                    </div>
                                @else
                                    <span class="text-xs text-slate-400 italic">Data ortu belum ditautkan</span>
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
                                            class="neo-btn bg-white hover:bg-slate-100 text-black p-1.5 text-xs cursor-pointer group relative shadow-[1.5px_1.5px_0px_#000]"
                                            title="Lihat Detail Profil Siswa" aria-label="Lihat Detail Profil Siswa">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                        </svg>
                                        <span class="absolute -top-8 left-1/2 -translate-x-1/2 bg-black text-white text-[10px] font-black uppercase px-2 py-0.5 pointer-events-none opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap border border-black shadow-[2px_2px_0px_0px_#FFD43B] z-50">
                                            Detail
                                        </span>
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
                                            class="neo-btn bg-[#FFD43B] hover:bg-yellow-400 text-black p-1.5 text-xs cursor-pointer group relative shadow-[1.5px_1.5px_0px_#000]"
                                            title="Edit Data Siswa" aria-label="Edit Data Siswa">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                        </svg>
                                        <span class="absolute -top-8 left-1/2 -translate-x-1/2 bg-black text-white text-[10px] font-black uppercase px-2 py-0.5 pointer-events-none opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap border border-black shadow-[2px_2px_0px_0px_#FFD43B] z-50">
                                            Edit
                                        </span>
                                    </button>

                                    <!-- 3. Reset Password Akun Siswa -->
                                    @php
                                        $resetPassLabel = !empty($stu->nisn) ? 'NISN: '.$stu->nisn : (!empty($stu->nis) ? 'NIS: '.$stu->nis : 'default');
                                    @endphp
                                    <form action="{{ route('guru.classes.binaan.students.reset-password', $stu) }}" method="POST" class="inline">
                                        @csrf
                                        <button type="button" 
                                                onclick="confirmResetPasswordHomeroom(this, {{ json_encode($stu->user->name ?? 'Siswa') }}, {{ json_encode($resetPassLabel) }})"
                                                class="neo-btn bg-[#5294FF] hover:bg-blue-600 text-white p-1.5 text-xs cursor-pointer group relative shadow-[1.5px_1.5px_0px_#000]"
                                                title="Reset Password ({{ $resetPassLabel }})" aria-label="Reset Password">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
                                            </svg>
                                            <span class="absolute -top-8 left-1/2 -translate-x-1/2 bg-black text-white text-[10px] font-black uppercase px-2 py-0.5 pointer-events-none opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap border border-black shadow-[2px_2px_0px_0px_#FFD43B] z-50">
                                                Reset Password
                                            </span>
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
    </div>
</div>
