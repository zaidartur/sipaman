<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class UpdateProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $user = $this->user();

        if ($user?->hasRole('user')) {
            return [
                'nama' => ['prohibited'],
                'email' => ['prohibited'],
                'password' => ['sometimes', 'confirmed', Password::min(8)],
            ];
        }

        return [
            'nama' => ['sometimes', 'string', 'max:150'],
            'email' => ['sometimes', 'required', 'email', 'max:150', 'unique:users,email,'.$user?->id],
            'password' => ['sometimes', 'confirmed', Password::min(8)],
        ];
    }

    public function messages(): array
    {
        return [
            'nama.prohibited' => 'Nama pelaku usaha mengikuti data PIRT dan tidak dapat diubah dari akun user.',
            'email.prohibited' => 'Akun pelaku usaha login memakai NIB, bukan email.',
            'email.email' => 'Format email tidak valid.',
            'email.unique' => 'Email sudah digunakan akun lain.',
            'password.confirmed' => 'Konfirmasi password tidak cocok.',
        ];
    }
}
