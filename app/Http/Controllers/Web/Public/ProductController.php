<?php

namespace App\Http\Controllers\Web\Public;

use App\Http\Controllers\Controller;
use App\Models\JenisBarang;
use App\Models\Kecamatan;
use App\Models\Produk;
use App\Support\SystemSettings;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function index(Request $request): View
    {
        $products = Produk::verified()
            ->with(['kecamatan', 'jenisBarang', 'gambarUtama'])
            ->search($request->query('search'))
            ->byKecamatan($request->query('kecamatan_id'))
            ->byJenisBarang($request->query('jenis_barang_id'))
            ->when($request->filled('kecamatan'), function ($query) use ($request) {
                $keyword = $request->query('kecamatan');
                $query->where(function ($q) use ($keyword) {
                    $q->where('wilayah', 'like', "%{$keyword}%")
                        ->orWhereHas('kecamatan', fn ($k) => $k->where('nama_kecamatan', 'like', "%{$keyword}%"));
                });
            })
            ->latest()
            ->paginate(SystemSettings::pagination())
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
