<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use RuntimeException;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $roles = DB::table('roles')
            ->whereIn('nama_role', ['super_admin', 'admin', 'user'])
            ->pluck('id', 'nama_role');

        if (! isset($roles['super_admin'], $roles['admin'], $roles['user'])) {
            throw new RuntimeException('Role super_admin, admin, atau user belum tersedia. Jalankan RoleSeeder dulu.');
        }

        $users = [
            [
                'nama' => 'Super Admin',
                'email' => env('SUPER_ADMIN_EMAIL', 'superadmin@pirt.go.id'),
                'password' => env('SUPER_ADMIN_PASSWORD', 'password'),
                'role_id' => $roles['super_admin'],
                'status_akun' => 'aktif',
            ],
            [
                'nama' => 'Admin',
                'email' => env('ADMIN_EMAIL', 'admin@pirt.go.id'),
                'password' => env('ADMIN_PASSWORD', 'password'),
                'role_id' => $roles['admin'],
                'status_akun' => 'aktif',
            ],
            [
                'nama' => 'Pelaku Usaha',
                'email' => null,
                'nib' => env('USER_NIB', '1234567890123'),
                'password' => env('USER_PASSWORD', 'password'),
                'role_id' => $roles['user'],
                'status_akun' => 'aktif',
            ],
        ];

        foreach ($users as $user) {
            $identity = $user['email'] ? ['email' => $user['email']] : ['nib' => $user['nib']];

            DB::table('users')->updateOrInsert(
                $identity,
                [
                    'nama' => $user['nama'],
                    'email' => $user['email'],
                    'nib' => $user['nib'] ?? null,
                    'password' => Hash::make($user['password']),
                    'role_id' => $user['role_id'],
                    'status_akun' => $user['status_akun'],
                    'updated_at' => now(),
                    'created_at' => now(),
                ]
            );
        }
    }
}
