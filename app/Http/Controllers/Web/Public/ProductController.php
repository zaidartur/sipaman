<?php

namespace App\Http\Controllers\Web\Public;

use App\Http\Controllers\Controller;
use App\Models\JenisBarang;
use App\Models\Kecamatan;
use App\Models\Produk;
use App\Services\PublicProductCatalogService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function __construct(private PublicProductCatalogService $catalogService)
    {
    }

    public function index(Request $request): View
    {
        $products = $this->catalogService
            ->paginate($request->only(['search', 'kecamatan_id', 'jenis_barang_id', 'kecamatan']))
            ->withQueryString();

        $kecamatans = Kecamatan::orderBy('nama_kecamatan')->get();
        $jenisBarangs = JenisBarang::active()->orderBy('nama_jenis')->get();

        return view('public.products.index', compact('products', 'kecamatans', 'jenisBarangs'));
    }

    public function show(Produk $produk): View
    {
        abort_unless($produk->is_verified, 404);

        $produk->load(['kecamatan', 'jenisBarang', 'gambarProduks', 'gambarUtama']);

        return view('public.products.show', compact('produk'));
    }
}
