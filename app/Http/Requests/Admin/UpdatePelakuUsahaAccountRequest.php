<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class UpdatePelakuUsahaAccountRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nama' => ['prohibited'],
            'email' => ['prohibited'],
            'nib' => ['prohibited'],
            'role' => ['prohibited'],
            'password' => ['nullable', 'confirmed', Password::min(8)],
            'status_akun' => ['required', 'in:aktif,nonaktif,kunci'],
        ];
    }

    public function messages(): array
    {
        return [
            'nama.prohibited' => 'Nama pelaku usaha mengikuti data PIRT dan tidak dapat diubah dari halaman ini.',
            'email.prohibited' => 'Akun pelaku usaha tidak memakai email sebagai identitas login.',
            'nib.prohibited' => 'NIB tidak dapat diubah dari halaman ini.',
            'role.prohibited' => 'Role akun pelaku usaha tidak dapat diubah dari halaman admin.',
            'password.confirmed' => 'Konfirmasi password tidak cocok.',
            'password.min' => 'Password minimal 8 karakter.',
            'status_akun.required' => 'Status akun wajib dipilih.',
            'status_akun.in' => 'Status akun tidak valid.',
        ];
    }
}
