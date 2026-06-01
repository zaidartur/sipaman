<?php

namespace App\Http\Requests\SuperAdmin;

use App\Support\SystemSettingCatalog;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class UpdateSystemSettingGroupRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'values' => ['nullable', 'array'],
            'values.*' => ['nullable', 'string', 'max:5000'],
            'logo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'return_anchor' => ['nullable', 'string', 'max:80'],
            'deskripsi' => ['prohibited'],
        ];
    }

    public function messages(): array
    {
        return [
            'values.array' => 'Data pengaturan tidak valid.',
            'values.*.max' => 'Nilai pengaturan maksimal 5000 karakter.',
            'logo.image' => 'Logo website harus berupa file gambar.',
            'logo.mimes' => 'Logo website harus berformat JPG, PNG, atau WebP.',
            'logo.max' => 'Ukuran logo website maksimal 2 MB.',
            'return_anchor.max' => 'Tujuan kembali halaman tidak valid.',
            'deskripsi.prohibited' => 'Keterangan fungsi pengaturan dikelola oleh sistem dan tidak dapat diedit dari form ini.',
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $groupKey = $this->groupKey();
            $definitions = $this->definitionsForGroup();

            if ($definitions === []) {
                $validator->errors()->add('values', 'Grup pengaturan tidak tersedia.');

                return;
            }

            if ($this->hasFile('logo') && $groupKey !== 'identity') {
                $validator->errors()->add('logo', 'Upload logo hanya tersedia pada grup Identitas Website.');
            }

            foreach ($this->input('values', []) as $key => $value) {
                $key = (string) $key;
                $definition = $definitions[$key] ?? null;

                if (! $definition) {
                    $validator->errors()->add("values.{$key}", 'Pengaturan ini tidak tersedia pada grup yang dipilih.');

                    continue;
                }

                if (preg_match('/(password|secret|token|api_key|private_key)/', strtolower($key))) {
                    $validator->errors()->add(
                        "values.{$key}",
                        'System Settings tidak boleh dipakai untuk menyimpan password, token, API key, atau secret.'
                    );
                }

                if (($definition['type'] ?? null) === 'email' && filled($value) && ! filter_var($value, FILTER_VALIDATE_EMAIL)) {
                    $validator->errors()->add("values.{$key}", 'Email kontak harus menggunakan format email yang benar.');
                }

                if (($definition['type'] ?? null) === 'number' && filled($value)) {
                    $this->validateNumberSetting($validator, $key, (string) $value, $definition);
                }

                if ($key === 'site_logo_path' && filled($value)) {
                    $validator->errors()->add("values.{$key}", 'Logo website harus diunggah sebagai file gambar, bukan ditulis sebagai path manual.');
                }
            }
        });
    }

    public function settingValues(): array
    {
        $definitions = $this->definitionsForGroup();

        return collect($this->input('values', []))
            ->only(array_keys($definitions))
            ->all();
    }

    public function groupKey(): string
    {
        return (string) $this->route('group');
    }

    public function returnAnchor(): string
    {
        $anchor = (string) $this->input('return_anchor', 'settings-' . $this->groupKey());

        return preg_match('/^[A-Za-z0-9_-]+$/', $anchor) ? $anchor : 'settings-' . $this->groupKey();
    }

    private function definitionsForGroup(): array
    {
        return collect(SystemSettingCatalog::definitions())
            ->filter(fn (array $definition) => ($definition['group'] ?? null) === $this->groupKey())
            ->all();
    }

    private function validateNumberSetting(Validator $validator, string $key, string $value, array $definition): void
    {
        $value = trim($value);
        $min = (int) ($definition['min'] ?? 1);
        $max = (int) ($definition['max'] ?? 100000);

        if (! ctype_digit($value)) {
            $validator->errors()->add("values.{$key}", 'Nilai pengaturan ini harus berupa angka bulat.');

            return;
        }

        $number = (int) $value;

        if ($number < $min || $number > $max) {
            $validator->errors()->add("values.{$key}", "Nilai pengaturan ini harus antara {$min} sampai {$max}.");
        }
    }
}
