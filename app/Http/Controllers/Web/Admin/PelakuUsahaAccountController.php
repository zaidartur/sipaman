<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdatePelakuUsahaAccountRequest;
use App\Models\User;
use App\Traits\LogsAuditTrail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class PelakuUsahaAccountController extends Controller
{
    use LogsAuditTrail;

    public function index(Request $request): View
    {
        $users = User::with('role')
            ->withCount('produks')
            ->whereHas('role', fn ($query) => $query->where('nama_role', 'user'))
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->query('search');
                $query->where(fn ($q) => $q
                    ->where('nama', 'like', "%{$search}%")
                    ->orWhere('nib', 'like', "%{$search}%"));
            })
            ->when($request->filled('status_akun'), fn ($query) => $query->where('status_akun', $request->query('status_akun')))
            ->when($request->query('password_status') === 'needs_setup', fn ($query) => $query->whereNull('password'))
            ->when($request->query('password_status') === 'ready', fn ($query) => $query->whereNotNull('password'))
            ->orderBy('nama')
            ->paginate(15)
            ->withQueryString();

        return view('admin.pelaku-usaha.index', compact('users'));
    }

    public function edit(User $user): View
    {
        $this->ensurePelakuUsaha($user);

        $user->load('role')->loadCount('produks');

        return view('admin.pelaku-usaha.edit', compact('user'));
    }

    public function update(UpdatePelakuUsahaAccountRequest $request, User $user): RedirectResponse
    {
        $this->ensurePelakuUsaha($user);

        $before = [
            'status_akun' => $user->status_akun,
            'has_password' => ! $user->needsPasswordSetup(),
        ];

        $data = $request->validated();
        $updateData = ['status_akun' => $data['status_akun']];

        if (! empty($data['password'])) {
            $updateData['password'] = Hash::make($data['password']);
        }

        $user->update($updateData);

        $this->logAudit('update', 'users', $user->id, $before, [
            'status_akun' => $user->status_akun,
            'has_password' => ! $user->needsPasswordSetup(),
        ]);

        return redirect()->route('admin.pelaku-usaha.index')->with('success', 'Akun pelaku usaha berhasil diperbarui.');
    }

    private function ensurePelakuUsaha(User $user): void
    {
        abort_unless(($user->loadMissing('role')->role->nama_role ?? null) === 'user', 403, 'Admin hanya boleh mengelola akun pelaku usaha.');
    }
}
