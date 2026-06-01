<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Produk;
use App\Support\SystemSettings;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'kecamatan_id' => ['nullable', 'integer', 'exists:kecamatans,id'],
            'jenis_barang_id' => ['nullable', 'integer', 'exists:jenis_barangs,id'],
            'status' => ['nullable', 'in:verified,unverified'],
            'is_verified' => ['nullable', 'boolean'],
            'per_page' => ['nullable', 'integer', 'min:3', 'max:100'],
        ]);

        $products = Produk::with(['kecamatan', 'jenisBarang', 'gambarUtama', 'verifikasi.verifikator', 'commitmentStatus'])
            ->search($request->query('search'))
            ->byKecamatan($request->query('kecamatan_id'))
            ->byJenisBarang($request->query('jenis_barang_id'))
            ->when($request->query('status') === 'verified', fn ($query) => $query->where('is_verified', true))
            ->when($request->query('status') === 'unverified', fn ($query) => $query->where('is_verified', false))
            ->when($request->has('is_verified'), fn ($query) => $query->where('is_verified', filter_var($request->query('is_verified'), FILTER_VALIDATE_BOOLEAN)))
            ->latest()
            ->paginate(SystemSettings::pagination($request->query('per_page')));

        return response()->json($products);
    }

    public function store(Request $request): JsonResponse
    {
        abort(404);
    }

    public function show(Produk $produk): JsonResponse
    {
        return response()->json(['data' => $produk->load(['kecamatan', 'jenisBarang', 'gambarProduks', 'verifikasi.verifikator', 'commitmentStatus'])]);
    }

    public function update(Request $request, Produk $produk): JsonResponse
    {
        abort(404);
    }

    public function destroy(Produk $produk): JsonResponse
    {
        abort(404);
    }
}
