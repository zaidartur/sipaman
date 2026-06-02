<?php

namespace App\Services;

use App\Imports\PirtCommitmentStatusImport;
use App\Imports\ProdukImport;
use App\Models\ImportLog;
use App\Support\Imports\SpreadsheetFileResolver;
use App\Support\Imports\SpreadsheetTemplateValidator;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel as ExcelFacade;

class ProductImportService
{
    public function __construct(private SpreadsheetTemplateValidator $templateValidator)
    {
    }

    public function importRekapPirt(UploadedFile $file): array
    {
        return $this->runImport(
            file: $file,
            tipeFile: 'rekap_pirt',
            import: new ProdukImport(),
            tabel: 'produks',
            schemaValidator: fn (string $readerType) => $this->templateValidator->assertRekapPirt($file, $readerType)
        );
    }

    public function importCommitmentStatus(UploadedFile $file): array
    {
        return $this->runImport(
            file: $file,
            tipeFile: 'status_komitmen',
            import: new PirtCommitmentStatusImport(auth()->id()),
            tabel: 'pirt_commitment_statuses',
            schemaValidator: fn (string $readerType) => $this->templateValidator->assertCommitmentStatus($file, $readerType)
        );
    }

    private function runImport(UploadedFile $file, string $tipeFile, object $import, string $tabel, callable $schemaValidator): array
    {
        $readerType = SpreadsheetFileResolver::resolveReaderType($file);

        try {
            $schemaValidator($readerType);

            DB::transaction(function () use ($file, $import, $readerType) {
                ExcelFacade::import($import, $file, null, $readerType);
            });
        } catch (\Throwable $e) {
            $this->createImportLog(
                file: $file,
                tipeFile: $tipeFile,
                berhasil: 0,
                gagal: 0,
                keterangan: "Tipe {$tipeFile}: import gagal. {$e->getMessage()}"
            );

            throw $e;
        }

        $berhasil = method_exists($import, 'getBerhasil') ? $import->getBerhasil() : 0;
        $gagal = method_exists($import, 'getGagal') ? $import->getGagal() : 0;
        $failures = method_exists($import, 'getFailureDetails') ? $import->getFailureDetails() : [];
        $warningCount = method_exists($import, 'getWarningCount') ? $import->getWarningCount() : 0;
        $warnings = method_exists($import, 'getWarningDetails') ? $import->getWarningDetails() : [];
        $userBaruDibuat = method_exists($import, 'getUserBaruDibuat') ? $import->getUserBaruDibuat() : 0;

        $this->createImportLog(
            file: $file,
            tipeFile: $tipeFile,
            berhasil: $berhasil,
            gagal: $gagal,
            keterangan: $this->importDescription($tipeFile, $gagal, $warningCount)
        );

        return [
            'tipe_file' => $tipeFile,
            'tabel' => $tabel,
            'nama_file' => $file->getClientOriginalName(),
            'reader_type' => $readerType,
            'berhasil' => $berhasil,
            'gagal' => $gagal,
            'user_baru_dibuat' => $userBaruDibuat,
            'warning_count' => $warningCount,
            'warnings' => $warnings,
            'failures' => $failures,
        ];
    }

    private function importDescription(string $tipeFile, int $gagal, int $warningCount): string
    {
        $messages = [];

        if ($gagal > 0) {
            $messages[] = "{$gagal} baris gagal / tidak valid";
        }

        if ($warningCount > 0) {
            $messages[] = "{$warningCount} baris perlu review jenis pangan";
        }

        if ($messages === []) {
            return "Tipe {$tipeFile}: semua baris berhasil diimpor.";
        }

        return "Tipe {$tipeFile}: ".implode(', ', $messages).'.';
    }

    private function createImportLog(
        UploadedFile $file,
        string $tipeFile,
        int $berhasil,
        int $gagal,
        string $keterangan
    ): void {
        ImportLog::create([
            'user_id' => auth()->id(),
            'tipe_file' => $tipeFile,
            'nama_file' => $file->getClientOriginalName(),
            'jumlah_baris' => $berhasil + $gagal,
            'jumlah_berhasil' => $berhasil,
            'jumlah_gagal' => $gagal,
            'keterangan' => $keterangan,
            'imported_at' => now(),
        ]);
    }
}
