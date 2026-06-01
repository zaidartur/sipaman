<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Imports\PirtCommitmentStatusImport;
use App\Imports\ProdukImport;
use App\Models\GambarProduk;
use App\Models\ImportLog;
use App\Models\Produk;
use App\Models\User;
use App\Services\ProductImageService;
use App\Support\SystemSettings;
use App\Traits\LogsAuditTrail;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;

/**
 * ProdukAdminController
 * ----------------------
 * Akses: admin, super_admin
 * Fitur:
 *   - Produk admin aktif bersifat read-only + import; create/update/delete manual tidak dipakai.
 *   - Import Excel
 *   - Import Excel
 *   - Upload & hapus gambar (hanya produk terverifikasi)
 */
class ProdukAdminController extends Controller
{
    use LogsAuditTrail;

    // ── GET /api/admin/produk ─────────────────────────────────
    public function index(Request $request): JsonResponse
    {
        $query = Produk::with(['kecamatan', 'jenisBarang', 'gambarUtama', 'verifikasi.verifikator', 'commitmentStatus']);

        if ($s = $request->query('search')) {
            $query->search($s);
        }

        if ($request->has('is_verified')) {
            $query->where('is_verified', filter_var($request->is_verified, FILTER_VALIDATE_BOOLEAN));
        }

        if ($kec = $request->query('kecamatan_id')) {
            $query->where('kecamatan_id', $kec);
        }

        if ($jb = $request->query('jenis_barang_id')) {
            $query->where('jenis_barang_id', $jb);
        }

        return response()->json(
            $query->orderByDesc('created_at')->paginate(SystemSettings::pagination($request->query('per_page')))
        );
    }

    // ── GET /api/admin/produk/{produk} ────────────────────────
    public function show(Produk $produk): JsonResponse
    {
        $produk->load(['kecamatan', 'jenisBarang', 'gambarProduks', 'verifikasi.verifikator', 'commitmentStatus']);

        return response()->json(['data' => $produk]);
    }

    // ── POST /api/admin/produk ────────────────────────────────
    public function store(Request $request): JsonResponse
    {
        return $this->manualProductMutationDisabled();

        $data = $request->validate([
            'user_id' => 'nullable|integer|exists:users,id',
            'no_sppirt' => 'required|string|max:100|unique:produks,no_sppirt',
            'nama_branding' => 'required|string|max:150',
            'kategori_pangan' => 'nullable|string|max:150',
            'jenis_pangan' => 'nullable|string|max:150',
            'kemasan' => 'nullable|string|max:150',
            'cara_penyimpanan' => 'nullable|string|max:150',
            'wilayah' => 'nullable|string|max:150',
            'kecamatan_id' => 'nullable|integer|exists:kecamatans,id',
            'jenis_barang_id' => 'nullable|integer|exists:jenis_barangs,id',
            'nama_pelaku_usaha' => 'required|string|max:150',
            'alamat' => 'required|string',
            'nib' => 'nullable|string|max:50',
            'no_hp' => 'nullable|string|max:20',
            'nama_toko' => 'nullable|string|max:150',
            'alamat_toko' => 'nullable|string|max:1000',
            'harga' => 'nullable|integer|min:0|max:1000000000',
            'deskripsi' => 'nullable|string|max:2000',
            'tanggal_pengajuan' => 'nullable|date',
            'tanggal_verifikasi' => 'nullable|date',
            'masa_berlaku_pirt' => 'nullable|date',
            'status_oss' => 'nullable|string|max:100',
            'is_verified' => 'nullable|boolean',
        ]);

        if (! $this->isPelakuUsahaUser($data['user_id'] ?? null)) {
            return response()->json([
                'message' => 'Produk hanya boleh dihubungkan ke akun dengan role user/pelaku usaha.',
            ], 422);
        }

        $produk = Produk::create($data);

        $this->logAudit('create', 'produks', $produk->id, null, $data);

        return response()->json([
            'message' => 'Produk berhasil ditambahkan.',
            'data' => $produk->load(['kecamatan', 'jenisBarang']),
        ], 201);
    }

    // ── PUT /api/admin/produk/{produk} ────────────────────────
    public function update(Request $request, Produk $produk): JsonResponse
    {
        return $this->manualProductMutationDisabled();

        $data = $request->validate([
            'user_id' => 'sometimes|nullable|integer|exists:users,id',
            'no_sppirt' => "sometimes|string|max:100|unique:produks,no_sppirt,{$produk->id}",
            'nama_branding' => 'sometimes|string|max:150',
            'kategori_pangan' => 'nullable|string|max:150',
            'jenis_pangan' => 'nullable|string|max:150',
            'kemasan' => 'nullable|string|max:150',
            'cara_penyimpanan' => 'nullable|string|max:150',
            'wilayah' => 'nullable|string|max:150',
            'kecamatan_id' => 'sometimes|nullable|integer|exists:kecamatans,id',
            'jenis_barang_id' => 'sometimes|nullable|integer|exists:jenis_barangs,id',
            'nama_pelaku_usaha' => 'sometimes|string|max:150',
            'alamat' => 'sometimes|string',
            'nib' => 'nullable|string|max:50',
            'no_hp' => 'nullable|string|max:20',
            'nama_toko' => 'nullable|string|max:150',
            'alamat_toko' => 'nullable|string|max:1000',
            'harga' => 'nullable|integer|min:0|max:1000000000',
            'deskripsi' => 'nullable|string|max:2000',
            'tanggal_pengajuan' => 'nullable|date',
            'tanggal_verifikasi' => 'nullable|date',
            'masa_berlaku_pirt' => 'nullable|date',
            'status_oss' => 'nullable|string|max:100',
            'is_verified' => 'nullable|boolean',
        ]);

        if (array_key_exists('user_id', $data) && ! $this->isPelakuUsahaUser($data['user_id'])) {
            return response()->json([
                'message' => 'Produk hanya boleh dihubungkan ke akun dengan role user/pelaku usaha.',
            ], 422);
        }

        $sebelum = $produk->toArray();
        $produk->update($data);
        $this->logAudit('update', 'produks', $produk->id, $sebelum, $produk->fresh()->toArray());

        return response()->json([
            'message' => 'Produk berhasil diperbarui.',
            'data' => $produk->fresh()->load(['kecamatan', 'jenisBarang']),
        ]);
    }

    // ── DELETE /api/admin/produk/{produk} ─────────────────────
    public function destroy(Produk $produk): JsonResponse
    {
        return $this->manualProductMutationDisabled();

        $sebelum = $produk->toArray();

        // Hapus file gambar dari storage
        foreach ($produk->gambarProduks as $g) {
            Storage::disk('public')->delete($g->url_gambar);
        }

        $produk->delete();
        $this->logAudit('delete', 'produks', $produk->id, $sebelum, null);

        return response()->json(['message' => 'Produk berhasil dihapus.']);
    }

    // ── POST /api/admin/produk/import ─────────────────────────
    public function import(Request $request): JsonResponse
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv|max:10240',
            'tipe_file' => 'nullable|in:rekap_pirt,status_komitmen',
        ]);

        $tipeFile = $request->input('tipe_file', 'rekap_pirt');

        return $this->runImport($request, $tipeFile);
    }

    // ── POST /api/admin/produk/import/rekap-pirt ──────────────
    public function importRekapPirt(Request $request): JsonResponse
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv|max:10240',
        ]);

        return $this->runImport($request, 'rekap_pirt');
    }

    // ── POST /api/admin/produk/import/status-komitmen ─────────
    public function importStatusKomitmen(Request $request): JsonResponse
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv|max:10240',
        ]);

        return $this->runImport($request, 'status_komitmen');
    }

    private function runImport(Request $request, string $tipeFile): JsonResponse
    {
        $import = $tipeFile === 'status_komitmen'
            ? new PirtCommitmentStatusImport
            : new ProdukImport;

        DB::beginTransaction();
        try {
            Excel::import($import, $request->file('file'));
            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();

            return response()->json([
                'message' => 'Import gagal: '.$e->getMessage(),
            ], 422);
        }

        $berhasil = $import->getBerhasil();
        $gagal = $import->getGagal();

        ImportLog::create([
            'user_id' => auth()->id(),
            'nama_file' => $request->file('file')->getClientOriginalName(),
            'jumlah_baris' => $berhasil + $gagal,
            'jumlah_berhasil' => $berhasil,
            'jumlah_gagal' => $gagal,
            'keterangan' => $gagal > 0
                ? "Tipe {$tipeFile}: {$gagal} baris gagal / tidak valid."
                : "Tipe {$tipeFile}: semua baris berhasil diimpor.",
        ]);

        $this->logAudit('import', $tipeFile === 'status_komitmen' ? 'pirt_commitment_statuses' : 'produks', null, null, [
            'tipe_file' => $tipeFile,
            'berhasil' => $berhasil,
            'gagal' => $gagal,
        ]);

        return response()->json([
            'message' => "Import {$tipeFile} selesai. Berhasil: {$berhasil}, Gagal: {$gagal}.",
            'tipe_file' => $tipeFile,
            'berhasil' => $berhasil,
            'gagal' => $gagal,
            'failures' => $import->getFailureDetails(),
        ]);
    }

    // ── POST /api/admin/produk/{produk}/verify ─────────────────
    public function verify(Request $request, Produk $produk): JsonResponse
    {
        return response()->json([
            'message' => 'Status verifikasi hanya diperbarui melalui import Excel Status Pemenuhan Komitmen.',
        ], 403);
    }

    // ── POST /api/admin/produk/{produk}/reject ─────────────────
    public function reject(Request $request, Produk $produk): JsonResponse
    {
        return response()->json([
            'message' => 'Status verifikasi hanya diperbarui melalui import Excel Status Pemenuhan Komitmen.',
        ], 403);
    }

    // ── POST /api/admin/produk/{produk}/images ─────────────────
    public function uploadImages(Request $request, Produk $produk): JsonResponse
    {
        if (! $produk->is_verified) {
            return response()->json([
                'message' => ProductImageService::VERIFIED_ONLY_MESSAGE,
            ], 422);
        }

        $request->validate([
            'gambar' => 'required|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        $before = $produk->gambarUtama?->toArray();
        $gambar = app(ProductImageService::class)->replaceOne($produk, $request->file('gambar'));

        $this->logAudit('update', 'gambar_produks', $produk->id, $before, $gambar->toArray());

        return response()->json([
            'message' => 'Gambar produk berhasil diganti.',
            'data' => $gambar,
        ], 201);
    }

    // ── DELETE /api/admin/produk/{produk}/images ───────────────
    public function deleteImage(Request $request, Produk $produk): JsonResponse
    {
        $request->validate([
            'gambar_id' => 'required|integer|exists:gambar_produks,id',
        ]);

        $gambar = GambarProduk::where('id', $request->gambar_id)
            ->where('produk_id', $produk->id)
            ->firstOrFail();

        app(ProductImageService::class)->delete($gambar);

        $this->logAudit('delete', 'gambar_produks', $gambar->id, [
            'url_gambar' => $gambar->url_gambar,
        ], null);

        return response()->json(['message' => 'Gambar berhasil dihapus.']);
    }

    private function isPelakuUsahaUser(?int $userId): bool
    {
        if ($userId === null) {
            return true;
        }

        return User::whereKey($userId)
            ->whereHas('role', fn ($query) => $query->where('nama_role', 'user'))
            ->exists();
    }

    private function manualProductMutationDisabled(): JsonResponse
    {
        return response()->json([
            'message' => 'Data produk resmi hanya diperbarui melalui import Rekap PIRT.',
        ], 403);
    }
}
