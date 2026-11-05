<?php

namespace App\Services\Ujian;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class SignatureAssetService
{
    /**
     * Simpan unggahan berkas gambar (PNG transparan) tanda tangan atau stempel.
     */
    public function storeUploadedAsset(UploadedFile $file, string $type = 'signature'): string
    {
        $folder = "ujian/{$type}s";
        $fileName = Str::uuid().'.'.$file->getClientOriginalExtension();

        return $file->storeAs($folder, $fileName, 'public');
    }

    /**
     * Simpan base64 data URI dari HTML5 canvas digital signature pad sebagai PNG transparan.
     */
    public function storeCanvasBase64(string $base64Data, string $type = 'signature'): ?string
    {
        if (empty($base64Data) || ! str_contains($base64Data, 'base64,')) {
            return null;
        }

        @[$meta, $data] = explode('base64,', $base64Data);
        $decoded = base64_decode($data);
        if ($decoded === false) {
            return null;
        }

        $folder = "ujian/{$type}s";
        $fileName = Str::uuid().'.png';
        $path = "{$folder}/{$fileName}";

        Storage::disk('public')->put($path, $decoded);

        return $path;
    }

    /**
     * Hapus berkas lama jika ada di storage.
     */
    public function deleteOldAsset(?string $path): void
    {
        if ($path && Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }
    }
}
