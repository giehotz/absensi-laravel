<?php

namespace App\Http\Requests\Supervisi;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class DeleteSupervisiJadwalRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() === true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'confirm_token' => ['required', 'digits:6'],
            'selected_ids' => ['nullable', 'array'],
            'selected_ids.*' => ['integer', 'exists:supervisi_jadwal,id'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'confirm_token.required' => 'Masukkan token konfirmasi 6 digit untuk menghapus jadwal.',
            'confirm_token.digits' => 'Token konfirmasi harus terdiri dari tepat 6 digit angka.',
        ];
    }

    /**
     * @return array<int, callable(Validator): void>
     */
    public function after(): array
    {
        return [
            function (Validator $validator): void {
                $expectedToken = (string) $this->session()->get('supervisi_admin_delete_token', '');
                $providedToken = trim((string) $this->input('confirm_token', ''));

                if ($expectedToken === '' || ! hash_equals($expectedToken, $providedToken)) {
                    $validator->errors()->add(
                        'confirm_token',
                        'Token konfirmasi 6 digit tidak sesuai atau sudah kedaluwarsa. Muat ulang halaman lalu coba kembali.'
                    );
                }
            },
        ];
    }
}
