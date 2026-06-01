<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Models\ImportLog;
use App\Models\JenisBarang;
use App\Models\Kecamatan;
use App\Models\Produk;
use App\Support\SystemSettings;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function index(Request $request): View
    {
        $products = Produk::with(['kecamatan', 'jenisBarang', 'gambarUtama', 'commitmentStatus'])
            ->search($request->query('search'))
            ->byKecamatan($request->query('kecamatan_id'))
            ->byJenisBarang($request->query('jenis_barang_id'))
            ->when($request->query('status') === 'verified', fn ($query) => $query->where('is_verified', true))
            ->when($request->query('status') === 'unverified', fn ($query) => $query->where('is_verified', false))
            ->latest()
            ->paginate(SystemSettings::pagination())
            ->withQueryString();

        $stats = [
            'total' => Produk::count(),
            'verified' => Produk::where('is_verified', true)->count(),
            'unverified' => Produk::where('is_verified', false)->count(),
        ];

        $lastImport = ImportLog::with('user')
            ->where(function ($query) {
                $query->where('tipe_file', 'rekap_pirt')
                    ->orWhere('keterangan', 'like', '%rekap_pirt%');
            })
            ->latest('imported_at')
            ->first();

        $kecamatans = Kecamatan::orderBy('nama_kecamatan')->get();
        $jenisBarangs = JenisBarang::active()->orderBy('nama_jenis')->get();

        return view('admin.products.index', compact('products', 'stats', 'lastImport', 'kecamatans', 'jenisBarangs'));
    }

    public function create(): View
    {
        abort(404);
    }

    public function store(Request $request): RedirectResponse
    {
        abort(404);
    }

    public function show(Produk $produk): View
    {
        $produk->load(['kecamatan', 'jenisBarang', 'gambarProduks', 'gambarUtama', 'verifikasi.verifikator', 'commitmentStatus']);

        return view('admin.products.show', compact('produk'));
    }

    public function edit(Produk $produk): View
    {
        abort(404);
    }

    public function update(Request $request, Produk $produk): RedirectResponse
    {
        abort(404);
    }

    public function destroy(Produk $produk): RedirectResponse
    {
        abort(404);
    }
}
