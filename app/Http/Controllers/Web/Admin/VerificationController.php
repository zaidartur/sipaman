<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Imports\PirtCommitmentStatusImport;
use App\Models\ImportLog;
use App\Models\Produk;
use App\Support\SystemSettings;
use App\Traits\LogsAuditTrail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;

/**
 * VerificationController
 * ----------------------
 * Akses: admin, super_admin
 *
 * Fitur:
 *   - Tampilkan semua produk dengan status verifikasi (tab filter)
 *   - Import Excel Status Pemenuhan Komitmen → otomatis sync ke verifikasi_produks
 *   - Detail status verifikasi read-only
 *   - Status verifikasi hanya berubah lewat import resmi
 */
class VerificationController extends Controller
{
    use LogsAuditTrail;

    // ── GET /admin/verifications ──────────────────────────────
    public function index(Request $request): View
    {
        $tab = $request->query('tab', 'semua');

        $query = Produk::with([
            'kecamatan',
            'verifikasi.verifikator',
            'commitmentStatus',
        ]);

        // Filter berdasarkan tab
        match ($tab) {
            'terverifikasi' => $query->where('is_verified', true),
            'belum'         => $query->where('is_verified', false)
                                     ->whereDoesntHave('verifikasi'),
            'proses'        => $query->where('is_verified', false)
                                     ->whereHas('verifikasi'),
            default         => null, // semua
        };

        if ($search = $request->query('search')) {
            $query->search($search);
        }

        $products = $query->latest()->paginate(SystemSettings::pagination())->withQueryString();

        $stats = [
            'total'         => Produk::count(),
            'terverifikasi' => Produk::where('is_verified', true)->count(),
            'belum'         => Produk::where('is_verified', false)->whereDoesntHave('verifikasi')->count(),
            'proses'        => Produk::where('is_verified', false)->whereHas('verifikasi')->count(),
        ];

        $lastImport = ImportLog::with('user')
            ->where('keterangan', 'like', '%status_komitmen%')
            ->latest()
            ->first();

        return view('admin.verifications.index', compact('products', 'stats', 'lastImport', 'tab'));
    }

    // ── POST /admin/verifications/import ─────────────────────
    public function import(Request $request): RedirectResponse
    {
        $request->validate([
            'file' => ['required', 'file', 'mimes:xlsx,xls,csv', 'max:10240'],
        ], [
            'file.required' => 'File Excel wajib dipilih.',
            'file.mimes'    => 'Format file harus xlsx, xls, atau csv.',
            'file.max'      => 'Ukuran file maksimal 10 MB.',
        ]);

        $import = new PirtCommitmentStatusImport();

        DB::beginTransaction();
        try {
            Excel::import($import, $request->file('file'));
            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()
                ->withInput()
                ->withErrors(['file' => 'Import gagal: ' . $e->getMessage()]);
        }

        $berhasil = $import->getBerhasil();
        $gagal    = $import->getGagal();
        $failures = $import->getFailureDetails();

        ImportLog::create([
            'user_id'          => auth()->id(),
            'nama_file'        => $request->file('file')->getClientOriginalName(),
            'jumlah_baris'     => $berhasil + $gagal,
            'jumlah_berhasil'  => $berhasil,
            'jumlah_gagal'     => $gagal,
            'keterangan'       => $gagal > 0
                ? "Tipe status_komitmen: {$gagal} baris gagal / tidak valid."
                : "Tipe status_komitmen: semua baris berhasil diimpor.",
        ]);

        $this->logAudit('import', 'pirt_commitment_statuses', null, null, [
            'nama_file' => $request->file('file')->getClientOriginalName(),
            'berhasil'  => $berhasil,
            'gagal'     => $gagal,
        ]);

        return back()
            ->with('success', "Import Status Pemenuhan Komitmen selesai. Berhasil: {$berhasil}, gagal: {$gagal}.")
            ->with('import_failures', array_slice($failures, 0, 5));
    }

    // ── GET /admin/verifications/{produk}/edit ────────────────
    public function edit(Produk $produk): View
    {
        $produk->load(['verifikasi.verifikator', 'commitmentStatus', 'kecamatan']);

        return view('admin.verifications.show', compact('produk'));
    }

    // ── PUT /admin/verifications/{produk} ─────────────────────
    public function update(Request $request, Produk $produk): RedirectResponse
    {
        return redirect()
            ->route('admin.verifications.index')
            ->withErrors(['verification' => 'Status verifikasi hanya diperbarui melalui import Excel Status Pemenuhan Komitmen.']);
    }
}
