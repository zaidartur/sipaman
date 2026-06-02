<?php

namespace App\Services;

use App\Models\Produk;
use App\Support\SystemSettings;

class PirtExpiryMessageRenderer
{
    public function render(Produk $produk, int $warningDays, ?string $template = null): string
    {
        $template ??= SystemSettings::pirtExpiryMessageTemplate();

        $values = [
            '{nama_pelaku_usaha}' => $produk->nama_pelaku_usaha,
            '{nama_produk}' => $produk->nama_branding,
            '{no_sppirt}' => $produk->no_sppirt,
            '{masa_berlaku_pirt}' => $produk->masa_berlaku_pirt?->format('d/m/Y') ?? '-',
            '{warning_days}' => (string) $warningDays,
            '{nib}' => $produk->nib ?: '-',
        ];

        return strtr($template, $values);
    }
}
