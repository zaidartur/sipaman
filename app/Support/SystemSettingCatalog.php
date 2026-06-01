<?php

namespace App\Support;

class SystemSettingCatalog
{
    public const GROUPS = [
        'identity' => [
            'label' => 'Identitas Website',
            'description' => 'Atur logo, nama, dan tagline yang tampil di bagian atas website publik.',
        ],
        'navigation' => [
            'label' => 'Navigasi Website',
            'description' => 'Tujuan menu dikunci oleh sistem. Anda hanya mengubah nama menu.',
        ],
        'contact_footer' => [
            'label' => 'Kontak & Footer',
            'description' => 'Atur informasi kontak dan teks bagian bawah website publik.',
        ],
        'data_display' => [
            'label' => 'Tampilan Data',
            'description' => 'Atur jumlah data yang ditampilkan pada daftar produk dan halaman terkait.',
        ],
        'system' => [
            'label' => 'Pengaturan Sistem',
            'description' => 'Konfigurasi global yang aman dan bukan konten halaman depan.',
        ],
    ];

    public const DEFINITIONS = [
        'site_logo_path' => [
            'group' => 'identity',
            'label' => 'Logo Website',
            'type' => 'image',
            'default' => '',
            'description' => 'Logo persegi yang tampil di navbar dan footer website publik. Gunakan JPG/PNG/WebP maksimal 2 MB, rekomendasi rasio 1:1.',
        ],
        'site_name' => [
            'group' => 'identity',
            'label' => 'Nama Website',
            'type' => 'text',
            'default' => 'SIPAMAN',
            'description' => 'Nama utama website yang tampil di navbar, footer, dan judul halaman.',
        ],
        'site_tagline' => [
            'group' => 'identity',
            'label' => 'Tagline Website',
            'type' => 'text',
            'default' => 'Sistem Informasi Pangan Aman',
            'description' => 'Teks kecil di bawah nama website.',
        ],
        'nav_home_label' => [
            'group' => 'navigation',
            'label' => 'Label Menu Home',
            'type' => 'text',
            'default' => 'Home',
            'description' => 'Nama menu menuju beranda. Link tetap dikunci ke halaman beranda.',
        ],
        'nav_products_label' => [
            'group' => 'navigation',
            'label' => 'Label Menu Produk',
            'type' => 'text',
            'default' => 'Produk',
            'description' => 'Nama menu menuju katalog produk. Link tetap dikunci ke halaman produk.',
        ],
        'nav_umkm_label' => [
            'group' => 'navigation',
            'label' => 'Label Menu UMKM',
            'type' => 'text',
            'default' => 'UMKM',
            'description' => 'Nama menu menuju daftar UMKM/pelaku usaha. Link tetap dikunci ke halaman UMKM.',
        ],
        'contact_email' => [
            'group' => 'contact_footer',
            'label' => 'Email Kontak',
            'type' => 'email',
            'default' => 'dinkes@karanganyarkab.go.id',
            'description' => 'Email kontak publik yang tampil di footer.',
        ],
        'contact_phone' => [
            'group' => 'contact_footer',
            'label' => 'Nomor Kontak/WhatsApp',
            'type' => 'text',
            'default' => '',
            'description' => 'Nomor kontak publik. Jangan isi dengan data rahasia.',
        ],
        'office_address' => [
            'group' => 'contact_footer',
            'label' => 'Alamat Kantor',
            'type' => 'textarea',
            'default' => 'Jl. Lawu No. 385, Karanganyar, Jawa Tengah 57711',
            'description' => 'Alamat kantor yang tampil di footer website publik.',
        ],
        'office_hours' => [
            'group' => 'contact_footer',
            'label' => 'Jam Operasional',
            'type' => 'text',
            'default' => 'Senin - Jumat, 08.00 - 16.00 WIB',
            'description' => 'Jam layanan publik yang tampil di footer.',
        ],
        'footer_copyright' => [
            'group' => 'contact_footer',
            'label' => 'Teks Footer',
            'type' => 'text',
            'default' => '© 2026 SIPAMAN Kabupaten Karanganyar.',
            'description' => 'Teks hak cipta yang tampil di footer website publik.',
        ],
        'footer_verified_text' => [
            'group' => 'contact_footer',
            'label' => 'Teks Verifikasi Footer',
            'type' => 'text',
            'default' => 'Verified by DISKOMINFO',
            'description' => 'Teks kecil di sisi kanan bawah footer.',
        ],
        'default_pagination' => [
            'group' => 'data_display',
            'label' => 'Jumlah Data per Halaman',
            'type' => 'number',
            'default' => '12',
            'min' => 3,
            'max' => 100,
            'description' => 'Jumlah data default per halaman untuk daftar produk dan halaman terkait. Nilai aman 3 sampai 100.',
        ],
        'import_max_file_size_kb' => [
            'group' => 'system',
            'label' => 'Batas Ukuran File Import',
            'type' => 'number',
            'min' => 1,
            'max' => 51200,
            'default' => '10240',
            'description' => 'Batas maksimal upload file import dalam KB.',
        ],
    ];

    public static function keys(): array
    {
        return array_keys(self::DEFINITIONS);
    }

    public static function groups(): array
    {
        return self::GROUPS;
    }

    public static function definitions(): array
    {
        return self::DEFINITIONS;
    }

    public static function definition(string $key): ?array
    {
        return self::DEFINITIONS[$key] ?? null;
    }

    public static function isManaged(string $key): bool
    {
        return array_key_exists($key, self::DEFINITIONS);
    }
}
