<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\JenisBarang;
use App\Models\Kecamatan;
use App\Models\Produk;
use App\Support\SystemSettings;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class ProdukController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $request->validate([
            'search' => 'nullable|string|max:100',
            'kecamatan_id' => 'nullable|integer|exists:kecamatans,id',
            'jenis_barang_id' => 'nullable|integer|exists:jenis_barangs,id',
            'per_page' => 'nullable|integer|min:3|max:100',
        ]);

        $produks = $this->catalogQuery($request)
            ->orderBy('nama_branding')
            ->paginate(SystemSettings::pagination($request->query('per_page')))
            ->through(fn (Produk $produk) => $this->formatPublicProduct($produk));

        return response()->json($produks);
    }

    public function filter(Request $request): JsonResponse
    {
        $request->validate([
            'kecamatan_id' => 'nullable|integer|exists:kecamatans,id',
            'jenis_barang_id' => 'nullable|integer|exists:jenis_barangs,id',
            'search' => 'nullable|string|max:100',
            'per_page' => 'nullable|integer|min:3|max:100',
        ]);

        $produks = $this->catalogQuery($request)
            ->orderBy('nama_branding')
            ->paginate(SystemSettings::pagination($request->query('per_page')))
            ->through(fn (Produk $produk) => $this->formatPublicProduct($produk));

        $kecamatans = Cache::remember('kecamatans_all', 3600, function () {
            return Kecamatan::orderBy('nama_kecamatan')->get(['id', 'nama_kecamatan', 'kab_kota']);
        });

        $jenisBarangs = Cache::remember('jenis_barangs_all', 3600, function () {
            return JenisBarang::query()
                ->active()
                ->orderBy('nama_jenis')
                ->get(['id', 'nama_jenis']);
        });

        return response()->json([
            'data' => $produks,
            'kecamatans' => $kecamatans,
            'jenis_barangs' => $jenisBarangs,
        ]);
    }

    public function show(Produk $produk): JsonResponse
    {
        if (! $produk->is_verified) {
            abort(404);
        }

        $produk->load(['kecamatan', 'jenisBarang', 'gambarProduks', 'commitmentStatus']);

        $produkLain = Produk::with(['kecamatan', 'jenisBarang', 'gambarUtama'])
            ->verified()
            ->where('id', '!=', $produk->id)
            ->when(
                $produk->user_id,
                fn ($query) => $query->where('user_id', $produk->user_id),
                fn ($query) => $query->where('nama_pelaku_usaha', $produk->nama_pelaku_usaha)
            )
            ->orderBy('nama_branding')
            ->limit(6)
            ->get()
            ->map(fn (Produk $item) => $this->formatPublicProduct($item));

        return response()->json([
            'data' => $this->formatPublicProduct($produk, true),
            'produk_lain_pelaku_usaha_sama' => $produkLain,
        ]);
    }

    private function catalogQuery(Request $request)
    {
        return Produk::with(['kecamatan', 'jenisBarang', 'gambarUtama'])
            ->verified()
            ->byKecamatan($request->query('kecamatan_id'))
            ->byJenisBarang($request->query('jenis_barang_id'))
            ->search($request->query('search'));
    }

    private function formatPublicProduct(Produk $produk, bool $includeDetail = false): array
    {
        $data = [
            'id' => $produk->id,
            'no_sppirt' => $produk->no_sppirt,
            'nama_branding' => $produk->nama_branding,
            'kategori_pangan' => $produk->kategori_pangan,
            'jenis_pangan' => $produk->jenis_pangan,
            'kemasan' => $produk->kemasan,
            'cara_penyimpanan' => $produk->cara_penyimpanan,
            'wilayah' => $produk->wilayah,
            'nama_toko' => $produk->nama_toko,
            'nama_pelaku_usaha' => $produk->nama_pelaku_usaha,
            'alamat_toko' => $produk->alamat_toko ?? $produk->alamat,
            'harga' => $produk->harga,
            'deskripsi' => $produk->deskripsi,
            'is_verified' => $produk->is_verified,
            'status_verifikasi' => $produk->is_verified ? 'terverifikasi' : 'belum_terverifikasi',
            'kecamatan' => $produk->kecamatan,
            'jenis_barang' => $produk->jenisBarang,
            'gambar_utama' => $produk->gambarUtama,
        ];

        if ($includeDetail) {
            $data['gambar_produk'] = $produk->gambarProduks;
            $data['status_pemenuhan_komitmen'] = $produk->commitmentStatus;
        }

        return $data;
    }
}
