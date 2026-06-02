<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ImportProductRequest;
use App\Services\ProductImportService;
use App\Traits\LogsAuditTrail;
use Illuminate\Http\RedirectResponse;

class ProductImportController extends Controller
{
    use LogsAuditTrail;

    public function __construct(private ProductImportService $productImportService)
    {
    }

    public function rekapPirt(ImportProductRequest $request): RedirectResponse
    {
        try {
            $result = $this->productImportService->importRekapPirt($request->file('file'));
        } catch (\Throwable $e) {
            return back()->withInput()->withErrors(['file' => 'Import gagal: ' . $e->getMessage()]);
        }

        $this->logAudit('import', 'produks', null, null, $result);

        return back()
            ->with('success', "Import Rekap Data PIRT selesai. Berhasil: {$result['berhasil']}, gagal: {$result['gagal']}, perlu review jenis pangan: {$result['warning_count']}.")
            ->with('import_failures', array_slice($result['failures'], 0, 5))
            ->with('import_warnings', array_slice($result['warnings'], 0, 5));
    }
}
