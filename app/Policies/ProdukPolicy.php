<?php

namespace App\Policies;

use App\Models\Produk;
use App\Models\User;

class ProdukPolicy
{
    public function viewAny(?User $user): bool
    {
        return true;
    }

    public function view(?User $user, Produk $produk): bool
    {
        return $produk->is_verified || $user?->hasRole('admin', 'super_admin') || $produk->user_id === $user?->id;
    }

    public function create(User $user): bool
    {
        return false;
    }

    public function update(User $user, Produk $produk): bool
    {
        return false;
    }

    public function delete(User $user, Produk $produk): bool
    {
        return false;
    }
}
