<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'user_id' => ['nullable', 'integer', 'exists:users,id'],
            'no_sppirt' => ['required', 'string', 'max:100', 'unique:produks,no_sppirt'],
            'nama_branding' => ['required', 'string', 'max:500'],
            'kategori_pangan' => ['nullable', 'string', 'max:500'],
            'jenis_pangan' => ['nullable', 'string', 'max:500'],
            'kemasan' => ['nullable', 'string', 'max:150'],
            'cara_penyimpanan' => ['nullable', 'string', 'max:150'],
            'wilayah' => ['nullable', 'string', 'max:500'],
            'kecamatan_id' => ['nullable', 'integer', 'exists:kecamatans,id'],
            'jenis_barang_id' => ['nullable', 'integer', 'exists:jenis_barangs,id'],
            'nama_pelaku_usaha' => ['required', 'string', 'max:150'],
            'alamat' => ['required', 'string'],
            'nib' => ['nullable', 'string', 'max:50'],
            'no_hp' => ['nullable', 'string', 'max:100'],
            'nama_toko' => ['nullable', 'string', 'max:500'],
            'alamat_toko' => ['nullable', 'string'],
            'harga' => ['nullable', 'integer', 'min:0', 'max:1000000000'],
            'deskripsi' => ['nullable', 'string', 'max:5000'],
            'tanggal_pengajuan' => ['nullable', 'date'],
            'tanggal_verifikasi' => ['nullable', 'date'],
            'masa_berlaku_pirt' => ['nullable', 'date'],
            'status_oss' => ['nullable', 'string', 'max:100'],
            'is_verified' => ['nullable', 'boolean'],
        ];
    }
}
