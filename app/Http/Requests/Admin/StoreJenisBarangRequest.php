<?php

namespace App\Http\Requests\Admin;

use App\Http\Requests\Admin\Concerns\ValidatesJenisBarangFields;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StoreJenisBarangRequest extends FormRequest
{
    use ValidatesJenisBarangFields;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nama_jenis' => ['required', 'string', 'max:100', 'unique:jenis_barangs,nama_jenis'],
            'slug' => ['nullable', 'string', 'max:160'],
            'deskripsi' => ['nullable', 'string', 'max:1000'],
            'is_active' => ['sometimes', 'boolean'],
            'aliases' => ['nullable', 'string', 'max:5000'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $this->validateJenisBarangFields($validator);
    }

    public function messages(): array
    {
        return [
            'nama_jenis.required' => 'Nama jenis barang wajib diisi.',
            'nama_jenis.max' => 'Nama jenis barang maksimal 100 karakter.',
            'nama_jenis.unique' => 'Nama jenis barang sudah digunakan.',
            'slug.unique' => 'Slug jenis barang sudah digunakan.',
            'deskripsi.max' => 'Deskripsi maksimal 1000 karakter.',
            'aliases.max' => 'Daftar alias terlalu panjang.',
        ];
    }
}
