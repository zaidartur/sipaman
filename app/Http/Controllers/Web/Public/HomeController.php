<?php

namespace App\Http\Controllers\Web\Public;

use App\Http\Controllers\Controller;
use App\Models\Kecamatan;
use App\Models\Produk;
use App\Services\LandingPageContentService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function __construct(private LandingPageContentService $landingPageContentService)
    {
    }

    public function index(): View
    {
        $contents = Schema::hasTable('landing_page_contents')
            ? $this->landingPageContentService->managedSections(false)->keyBy('section_key')
            : collect();

        $featuredProducts = Schema::hasTable('produks')
            ? Produk::verified()->with(['kecamatan', 'gambarUtama'])->latest()->limit(6)->get()
            : new Collection();

        $kecamatans = Schema::hasTable('kecamatans')
            ? Kecamatan::orderBy('nama_kecamatan')->get()
            : new Collection();

        $homeStats = Schema::hasTable('produks')
            ? [
                'verified_products' => Produk::verified()->count(),
                'districts' => $kecamatans->count(),
                'umkm' => Produk::verified()
                    ->whereNotNull('nama_pelaku_usaha')
                    ->distinct('nama_pelaku_usaha')
                    ->count('nama_pelaku_usaha'),
            ]
            : [
                'verified_products' => 0,
                'districts' => $kecamatans->count(),
                'umkm' => 0,
            ];

        return view('public.home', compact('contents', 'featuredProducts', 'homeStats', 'kecamatans'));
    }
}
