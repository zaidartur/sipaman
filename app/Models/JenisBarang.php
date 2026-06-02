<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class JenisBarang extends Model
{
    protected $fillable = ['nama_jenis', 'slug', 'deskripsi', 'is_active'];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function produks(): HasMany
    {
        return $this->hasMany(Produk::class);
    }

    public function aliases(): HasMany
    {
        return $this->hasMany(JenisBarangAlias::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
