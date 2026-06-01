<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Models\ImportLog;
use App\Models\Produk;
use App\Support\SystemSettings;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProductPageController extends Controller
{
    public function index(Request $request): View
    {
        $products = Produk::with(['kecamatan', 'jenisBarang', 'commitmentStatus'])
            ->when($request->filled('search'), function ($query) use ($request) {
                $query->search($request->query('search'));
            })
            ->when($request->filled('status'), function ($query) use ($request) {
                if ($request->query('status') === 'verified') {
                    $query->where('is_verified', true);
                }

                if ($request->query('status') === 'unverified') {
                    $query->where('is_verified', false);
                }
            })
            ->latest()
            ->paginate(SystemSettings::pagination())
            ->withQueryString();

        $stats = [
            'total' => Produk::count(),
            'verified' => Produk::where('is_verified', true)->count(),
            'unverified' => Produk::where('is_verified', false)->count(),
        ];

        $lastImport = ImportLog::with('user')
            ->latest('imported_at')
            ->first();

        return view('admin.products.index', compact('products', 'stats', 'lastImport'));
    }
}
