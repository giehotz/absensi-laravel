<?php

namespace App\Http\Controllers\Siswa;

use App\Http\Controllers\Controller;
use App\Models\LeaveRequest;
use App\Services\ImageUploadService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LeaveRequestController extends Controller
{
    public function __construct(
        protected ImageUploadService $imageUploadService
    ) {}

    /**
     * Simpan pengajuan izin atau sakit dari siswa.
     */
    public function store(Request $request): RedirectResponse
    {
        $user = Auth::user();
        $student = $user->student;

        if (! $student) {
            return back()->with('error', 'Data profil siswa tidak ditemukan.');
        }

        $validated = $request->validate([
            'type' => ['required', 'in:sakit,izin'],
            'date_from' => ['required', 'date'],
            'date_to' => ['required', 'date', 'after_or_equal:date_from'],
            'reason' => ['required', 'string', 'max:500'],
            'attachment' => ['nullable', 'file', 'mimes:jpeg,png,jpg,webp,pdf', 'max:3072'],
        ], [
            'type.required' => 'Silakan pilih jenis permohonan (Sakit atau Izin).',
            'type.in' => 'Jenis permohonan harus berupa Sakit atau Izin.',
            'date_from.required' => 'Tanggal mulai izin/sakit wajib diisi.',
            'date_to.required' => 'Tanggal selesai izin/sakit wajib diisi.',
            'date_to.after_or_equal' => 'Tanggal selesai tidak boleh mendahului tanggal mulai.',
            'reason.required' => 'Alasan atau keterangan permohonan wajib diisi.',
            'reason.max' => 'Keterangan permohonan maksimal 500 karakter.',
            'attachment.mimes' => 'Format file bukti harus berupa JPG, PNG, WEBP, atau PDF.',
            'attachment.max' => 'Ukuran file bukti maksimal 3MB.',
        ]);

        $attachmentPath = null;
        if ($request->hasFile('attachment')) {
            $attachmentPath = $this->imageUploadService->uploadAsWebp(
                $request->file('attachment'),
                'leave-attachments',
                quality: 80,
                maxWidth: 1920,
                maxHeight: 1920
            );
        }

        LeaveRequest::create([
            'student_id' => $student->id,
            'requested_by' => $user->id,
            'type' => $validated['type'],
            'reason' => $validated['reason'],
            'attachment_path' => $attachmentPath,
            'date_from' => $validated['date_from'],
            'date_to' => $validated['date_to'],
            'status' => 'pending',
        ]);

        $label = ucfirst($validated['type']);

        return redirect()->route('siswa.dashboard', ['tab' => 'izin'])
            ->with('success', "Permohonan {$label} berhasil diajukan dan sedang menunggu persetujuan wali kelas.");
    }
}
