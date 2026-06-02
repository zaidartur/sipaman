<?php

namespace Database\Factories;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    protected static ?string $password;

    public function definition(): array
    {
        return [
            'nama' => fake()->name(),
            'email' => null,
            'nib' => fake()->unique()->numerify('#############'),
            'password' => static::$password ??= Hash::make('password'),
            'role_id' => Role::where('nama_role', 'user')->value('id') ?? 1,
            'status_akun' => 'aktif',
            'remember_token' => Str::random(10),
        ];
    }

    public function admin(): static
    {
        return $this->state(fn () => [
            'email' => fake()->unique()->safeEmail(),
            'nib' => null,
            'role_id' => Role::where('nama_role', 'admin')->value('id') ?? 1,
        ]);
    }

    public function superAdmin(): static
    {
        return $this->state(fn () => [
            'email' => fake()->unique()->safeEmail(),
            'nib' => null,
            'role_id' => Role::where('nama_role', 'super_admin')->value('id') ?? 1,
        ]);
    }
}
