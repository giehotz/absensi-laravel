    <!-- Table Card -->
    <div class="bg-white neo-box overflow-hidden">
        <!-- Table Toolbar -->
        <div class="p-3.5 border-b-2 border-black bg-[#FFF9DB]/40 flex flex-wrap items-center justify-between gap-3">
            <div class="flex items-center gap-2">
                <span class="text-xs font-black uppercase tracking-wider font-heading text-black flex items-center gap-1.5">
                    <span>📋</span> Tabel Siswa
                </span>
                <span class="neo-badge bg-white text-black text-[11px] font-mono font-bold">
                    {{ $students->total() }} Data
                </span>
            </div>

            <!-- Filter Jumlah Tampilan (25, 50, 100, Semua) -->
            <form method="GET" action="{{ route('admin.students.index') }}" class="flex items-center gap-2">
                @if(!empty($selectedClassId))
                    <input type="hidden" name="class_id" value="{{ $selectedClassId }}">
                @endif
                <div class="flex items-center gap-1 bg-white border-2 border-black px-2.5 py-1.5 shadow-[2px_2px_0px_#000]">
                    <span class="text-[10px] font-black uppercase text-slate-600 font-heading">Tampil:</span>
                    <select name="per_page" onchange="this.form.submit()" class="bg-transparent text-xs font-bold text-black focus:outline-none cursor-pointer">
                        <option value="25" {{ $perPage == '25' ? 'selected' : '' }}>25</option>
                        <option value="50" {{ $perPage == '50' ? 'selected' : '' }}>50</option>
                        <option value="100" {{ $perPage == '100' ? 'selected' : '' }}>100</option>
                        <option value="semua" {{ $perPage == 'semua' || $perPage == 'all' ? 'selected' : '' }}>Semua</option>
                    </select>
                </div>
            </form>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-[#FFF9DB] border-b-2 border-black text-xs font-black uppercase tracking-wider">
                    <tr>
                        <th class="p-3.5 border-r border-black">No</th>
                        <th class="p-3.5 border-r border-black">NIS / NISN</th>
                        <th class="p-3.5 border-r border-black">Nama Siswa & Email</th>
                        <th class="p-3.5 border-r border-black">Kelas</th>
                        <th class="p-3.5 border-r border-black">L/P</th>
                        <th class="p-3.5 border-r border-black">QR Identifier</th>
                        <th class="p-3.5 text-center w-48">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y-2 divide-black">
                    @forelse($students as $index => $student)
                    <tr class="hover:bg-slate-50 font-medium">
                        <td class="p-3.5 font-bold text-center border-r border-black">
                            {{ $students->firstItem() + $index }}
                        </td>
                        <td class="p-3.5 border-r border-black font-mono text-xs">
                            <span class="font-bold text-black">{{ $student->nis }}</span>
                            @if($student->nisn)
                                <span class="text-slate-400 block text-[10px]">NISN: {{ $student->nisn }}</span>
                            @endif
                        </td>
                        <td class="p-3.5 border-r border-black">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 border-2 border-black overflow-hidden shrink-0 bg-slate-100 flex items-center justify-center font-bold text-xs shadow-[1.5px_1.5px_0px_#000]">
                                    @if($student->photo)
                                        <img src="{{ $student->photo_url }}" alt="{{ $student->user->name ?? 'Siswa' }}" class="w-full h-full object-cover">
                                    @else
                                        <span class="text-slate-500 font-mono text-xs">{{ strtoupper(substr($student->user->name ?? 'S', 0, 2)) }}</span>
                                    @endif
                                </div>
                                <div>
                                    <div class="font-bold text-black">{{ $student->user->name ?? '-' }}</div>
                                    <div class="text-xs text-slate-500 font-mono">{{ $student->user->email ?? '-' }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="p-3.5 border-r border-black">
                            <span class="neo-badge bg-[#E7F5FF] text-blue-900 text-[10px]">
                                {{ $student->schoolClass->name ?? '-' }}
                            </span>
                        </td>
                        <td class="p-3.5 border-r border-black font-bold text-center">
                            <span class="px-2 py-0.5 border border-black text-xs {{ $student->gender == 'L' ? 'bg-[#D0EBFF]' : 'bg-[#FCC2D7]' }}">
                                {{ $student->gender }}
                            </span>
                        </td>
                        <td class="p-3.5 border-r border-black">
                            <div class="flex items-center gap-2.5">
                                <button type="button" 
                                        onclick="previewQr({{ json_encode([
                                            'id' => $student->id,
                                            'name' => $student->user->name ?? '',
                                            'nis' => $student->nis,
                                            'nisn' => $student->nisn ?? '-',
                                            'class_name' => $student->schoolClass->name ?? '-',
                                            'birth_date' => $student->birth_date ? $student->birth_date->format('d/m/Y') : '-',
                                            'gender' => $student->gender == 'L' ? 'LAKI-LAKI' : 'PEREMPUAN',
                                            'qr' => $student->qr_code_identifier,
                                            'qr_image' => $student->qr_data_uri,
                                            'photo_url' => $student->photo ? $student->photo_url : null,
                                        ]) }})" 
                                        class="p-1 bg-white border-2 border-black shadow-[1.5px_1.5px_0px_#000] hover:bg-[#FFF9DB] shrink-0 cursor-pointer transition-transform hover:scale-105"
                                        title="Klik untuk melihat pratinjau kartu & QR">
                                    <img src="{{ $student->qr_data_uri }}" alt="QR {{ $student->nis }}" class="w-8 h-8 object-contain">
                                </button>
                                <div>
                                    <span class="font-mono font-bold text-xs text-black block">{{ $student->qr_code_identifier }}</span>
                                    <span class="text-[10px] text-slate-500 font-semibold flex items-center gap-1">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 inline-block"></span> Siap Scan
                                    </span>
                                </div>
                            </div>
                        </td>
                        <td class="p-3.5 text-center">
                            <div class="flex items-center justify-center gap-1.5">
                                <!-- 1. Kartu & QR (🪪) -->
                                <button type="button" 
                                        onclick="previewQr({{ json_encode([
                                            'id' => $student->id,
                                            'name' => $student->user->name ?? '',
                                            'nis' => $student->nis,
                                            'nisn' => $student->nisn ?? '-',
                                            'class_name' => $student->schoolClass->name ?? '-',
                                            'birth_date' => $student->birth_date ? $student->birth_date->format('d/m/Y') : '-',
                                            'gender' => $student->gender == 'L' ? 'LAKI-LAKI' : 'PEREMPUAN',
                                            'qr' => $student->qr_code_identifier,
                                            'qr_image' => $student->qr_data_uri,
                                            'photo_url' => $student->photo ? $student->photo_url : null,
                                        ]) }})" 
                                        class="neo-btn bg-[#20C997] hover:bg-[#12b886] text-black p-1.5 text-xs cursor-pointer group relative shadow-[1.5px_1.5px_0px_#000]"
                                        title="Kartu & QR" aria-label="Lihat Kartu & QR">
                                    <span class="text-sm leading-none block">🪪</span>
                                    <span class="absolute -top-8 left-1/2 -translate-x-1/2 bg-black text-white text-[10px] font-black uppercase px-2 py-0.5 pointer-events-none opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap border border-black shadow-[2px_2px_0px_0px_#FFD43B] z-50">
                                        Kartu & QR
                                    </span>
                                </button>

                                <!-- 2. Reset Password (Default NISN) -->
                                @php
                                    $resetPassDefault = !empty($student->nisn) ? $student->nisn : (!empty($student->nis) ? $student->nis : 'password');
                                    $resetPassLabel = !empty($student->nisn) ? 'NISN' : (!empty($student->nis) ? 'NIS' : 'Default');
                                @endphp
                                <form action="{{ route('admin.students.reset-password', $student) }}" method="POST">
                                    @csrf
                                    <button type="button" 
                                            onclick="confirmResetPassword(this, {{ json_encode($student->user->name ?? 'Siswa') }}, {{ json_encode($resetPassDefault) }}, {{ json_encode($resetPassLabel) }})"
                                            class="neo-btn bg-[#5294FF] hover:bg-blue-600 text-white p-1.5 text-xs cursor-pointer group relative shadow-[1.5px_1.5px_0px_#000]"
                                            title="Reset Password ({{ $resetPassLabel }})" aria-label="Reset Password">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
                                        </svg>
                                        <span class="absolute -top-8 left-1/2 -translate-x-1/2 bg-black text-white text-[10px] font-black uppercase px-2 py-0.5 pointer-events-none opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap border border-black shadow-[2px_2px_0px_0px_#FFD43B] z-50">
                                            Reset Password ({{ $resetPassLabel }})
                                        </span>
                                    </button>
                                </form>

                                <!-- 3. Edit Siswa -->
                                <button type="button" 
                                        onclick="editStudent({{ json_encode([
                                            'id' => $student->id,
                                            'name' => $student->user->name ?? '',
                                            'email' => $student->user->email ?? '',
                                            'nis' => $student->nis,
                                            'nisn' => $student->nisn ?? '',
                                            'school_class_id' => $student->school_class_id,
                                            'gender' => $student->gender,
                                            'birth_place' => $student->birth_place ?? '',
                                            'birth_date' => $student->birth_date ? $student->birth_date->format('Y-m-d') : '',
                                            'religion' => $student->religion ?? '',
                                            'phone' => $student->phone ?? '',
                                            'address' => $student->address ?? '',
                                            'family_status' => $student->family_status ?? '',
                                            'child_number' => $student->child_number ?? '',
                                            'previous_school' => $student->previous_school ?? '',
                                            'admission_date' => $student->admission_date ? $student->admission_date->format('Y-m-d') : '',
                                            'entry_grade' => $student->entry_grade ?? '',
                                            'father_name' => $student->father_name ?? '',
                                            'father_job' => $student->father_job ?? '',
                                            'mother_name' => $student->mother_name ?? '',
                                            'mother_job' => $student->mother_job ?? '',
                                            'parent_address' => $student->parent_address ?? '',
                                            'guardian_name' => $student->guardian_name ?? '',
                                            'guardian_job' => $student->guardian_job ?? '',
                                            'guardian_address' => $student->guardian_address ?? '',
                                            'photo_url' => $student->photo ? $student->photo_url : null,
                                        ]) }})" 
                                        class="neo-btn bg-[#FFD43B] hover:bg-yellow-400 text-black p-1.5 text-xs cursor-pointer group relative shadow-[1.5px_1.5px_0px_#000]"
                                        title="Edit Siswa" aria-label="Edit Siswa">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                    </svg>
                                    <span class="absolute -top-8 left-1/2 -translate-x-1/2 bg-black text-white text-[10px] font-black uppercase px-2 py-0.5 pointer-events-none opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap border border-black shadow-[2px_2px_0px_0px_#FFD43B] z-50">
                                        Edit
                                    </span>
                                </button>

                                <!-- 4. Hapus Siswa -->
                                <form action="{{ route('admin.students.destroy', $student) }}" method="POST">
                                    @csrf
                                    @method('DELETE')
                                    <button type="button" 
                                            onclick="confirmDeleteStudent(this, {{ json_encode($student->user->name ?? 'Siswa') }})"
                                            class="neo-btn bg-[#FF6B6B] hover:bg-red-600 text-white p-1.5 text-xs cursor-pointer group relative shadow-[1.5px_1.5px_0px_#000]"
                                            title="Hapus Siswa" aria-label="Hapus Siswa">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
                                        <span class="absolute -top-8 left-1/2 -translate-x-1/2 bg-black text-white text-[10px] font-black uppercase px-2 py-0.5 pointer-events-none opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap border border-black shadow-[2px_2px_0px_0px_#FFD43B] z-50">
                                            Hapus
                                        </span>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="p-8 text-center text-slate-500 font-semibold">
                            Belum ada data siswa ditemukan.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Table Footer / Pagination Info & Links -->
        <div class="p-4 border-t-2 border-black bg-slate-50 flex flex-col sm:flex-row items-center justify-between gap-3">
            <div class="text-xs font-semibold text-slate-700">
                Menampilkan <span class="font-mono font-bold text-black">{{ $students->firstItem() ?? 0 }}</span> - <span class="font-mono font-bold text-black">{{ $students->lastItem() ?? 0 }}</span> dari <span class="font-mono font-bold text-black">{{ $students->total() }}</span> siswa
                @if(!empty($selectedClass))
                    <span class="text-slate-500 font-normal">(Kelas {{ $selectedClass->name }} • Total Semua Kelas: {{ $totalStudentsCount }})</span>
                @else
                    <span class="text-slate-500 font-normal">(Total Semua Kelas: {{ $totalStudentsCount }})</span>
                @endif
            </div>

            @if($students->hasPages())
                <div>
                    {{ $students->links() }}
                </div>
            @endif
        </div>
    </div>
