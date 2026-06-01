<?php

namespace App\Http\Requests\SuperAdmin;

use App\Support\SystemSettingCatalog;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class UpdateSystemSettingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'value' => ['nullable', 'string', 'max:5000'],
            'logo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'setting_key' => ['nullable', 'string', 'max:100'],
            'deskripsi' => ['prohibited'],
        ];
    }

    public function messages(): array
    {
        return [
            'value.max' => 'Nilai pengaturan maksimal 5000 karakter.',
            'logo.image' => 'Logo website harus berupa file gambar.',
            'logo.mimes' => 'Logo website harus berformat JPG, PNG, atau WebP.',
            'logo.max' => 'Ukuran logo website maksimal 2 MB.',
            'deskripsi.prohibited' => 'Keterangan fungsi pengaturan dikelola oleh sistem dan tidak dapat diedit dari form ini.',
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $setting = $this->route('setting');
            $key = strtolower((string) ($setting?->key ?? ''));
            $definition = SystemSettingCatalog::definition((string) ($setting?->key ?? ''));

            if (preg_match('/(password|secret|token|api_key|private_key)/', $key)) {
                $validator->errors()->add(
                    'value',
                    'System Settings tidak boleh dipakai untuk menyimpan password, token, API key, atau secret.'
                );
            }

            if (! $definition) {
                $validator->errors()->add('value', 'Pengaturan ini tidak tersedia di halaman System Settings.');

                return;
            }

            if ($this->hasFile('logo') && $key !== 'site_logo_path') {
                $validator->errors()->add('logo', 'Upload logo hanya tersedia pada pengaturan Logo Website.');
            }

            if ($key === 'site_logo_path' && $this->filled('value')) {
                $validator->errors()->add('value', 'Logo website harus diunggah sebagai file gambar, bukan ditulis sebagai path manual.');
            }

            if (($definition['type'] ?? null) === 'email' && $this->filled('value') && ! filter_var($this->input('value'), FILTER_VALIDATE_EMAIL)) {
                $validator->errors()->add('value', 'Email kontak harus menggunakan format email yang benar.');
            }

            if (($definition['type'] ?? null) === 'number' && $this->filled('value')) {
                $value = trim((string) $this->input('value'));
                $min = (int) ($definition['min'] ?? 1);
                $max = (int) ($definition['max'] ?? 100000);

                if (! ctype_digit($value)) {
                    $validator->errors()->add('value', 'Nilai pengaturan ini harus berupa angka bulat.');

                    return;
                }

                $number = (int) $value;

                if ($number < $min || $number > $max) {
                    $validator->errors()->add('value', "Nilai pengaturan ini harus antara {$min} sampai {$max}.");
                }
            }
        });
    }
}
