<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ImportCommitmentStatusRequest;
use App\Models\ImportLog;
use App\Models\Produk;
use App\Services\ProductImportService;
use App\Services\ProductVerificationQueryService;
use App\Support\SystemSettings;
use App\Traits\LogsAuditTrail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProductVerificationController extends Controller
{
    use LogsAuditTrail;

    public function __construct(
        private ProductImportService $productImportService,
        private ProductVerificationQueryService $verificationQueryService,
    ) {}

    public function index(Request $request): View
    {
        $filters = $request->query();
        $tab = $this->verificationQueryService->resolveTab($filters);
        $trackingFilters = $this->verificationQueryService->resolveTrackingFilters($filters, $tab);

        $products = $this->verificationQueryService
            ->query($tab, $trackingFilters)
            ->search($request->query('search'))
            ->latest()
            ->paginate(SystemSettings::pagination())
            ->withQueryString();

        $stats = $this->verificationQueryService->stats();

        $lastImport = ImportLog::with('user')
            ->where(function ($query) {
                $query->where('tipe_file', 'status_komitmen')
                    ->orWhere('keterangan', 'like', '%status_komitmen%');
            })
            ->latest('imported_at')
            ->first();

        return view('admin.verifications.index', [
            'products' => $products,
            'stats' => $stats,
            'lastImport' => $lastImport,
            'tab' => $tab,
            'trackingFilters' => $trackingFilters,
            'trackingFilterLabels' => $this->verificationQueryService->trackingFilterLabels(),
        ]);
    }

    public function import(ImportCommitmentStatusRequest $request): RedirectResponse
    {
        try {
            $result = $this->productImportService->importCommitmentStatus($request->file('file'));
        } catch (\Throwable $e) {
            return back()->withInput()->withErrors(['file' => 'Import gagal: ' . $e->getMessage()]);
        }

        $this->logAudit('import', 'pirt_commitment_statuses', null, null, $result);

        return back()
            ->with('success', "Import Status Pemenuhan Komitmen selesai. Berhasil: {$result['berhasil']}, gagal: {$result['gagal']}, user baru dibuat: {$result['user_baru_dibuat']}.")
            ->with('import_failures', array_slice($result['failures'], 0, 5));
    }

    public function show(Produk $produk): View
    {
        $produk->load(['verifikasi.verifikator', 'commitmentStatus', 'kecamatan']);

        return view('admin.verifications.show', compact('produk'));
    }
}
