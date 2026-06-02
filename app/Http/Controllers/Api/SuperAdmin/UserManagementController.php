<?php

namespace App\Http\Controllers\Api\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Http\Requests\SuperAdmin\StoreUserRequest;
use App\Http\Requests\SuperAdmin\UpdateUserRequest;
use App\Models\Role;
use App\Models\User;
use App\Traits\LogsAuditTrail;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserManagementController extends Controller
{
    use LogsAuditTrail;

    public function index(Request $request): JsonResponse
    {
        $users = User::with('role')
            ->whereHas('role', fn ($query) => $query->where('nama_role', 'admin'))
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->query('search');
                $query->where(fn ($q) => $q
                    ->where('nama', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%"));
            })
            ->orderBy('nama')
            ->paginate($request->query('per_page', 20));

        return response()->json($users);
    }

    public function store(StoreUserRequest $request): JsonResponse
    {
        $data = $request->validated();
        $role = Role::where('nama_role', 'admin')->firstOrFail();

        $user = User::create([
            'nama' => $data['nama'],
            'email' => $data['email'],
            'nib' => null,
            'password' => Hash::make($data['password']),
            'role_id' => $role->id,
            'status_akun' => $data['status_akun'] ?? 'aktif',
        ]);

        $this->logAudit('create', 'users', $user->id, null, $user->load('role')->toArray());

        return response()->json(['message' => 'Admin berhasil dibuat.', 'data' => $user->load('role')], 201);
    }

    public function show(User $user): JsonResponse
    {
        if (! $this->isManagedAdmin($user)) {
            return response()->json(['message' => 'Endpoint ini hanya untuk melihat akun admin.'], 403);
        }

        return response()->json(['data' => $user->load('role')]);
    }

    public function update(UpdateUserRequest $request, User $user): JsonResponse
    {
        if ($user->id === auth()->id() || ! $this->isManagedAdmin($user)) {
            return response()->json(['message' => 'Akun ini tidak boleh diubah dari endpoint user management.'], 403);
        }

        $before = $user->load('role')->toArray();
        $data = $request->validated();
        $updateData = [];

        if (! empty($data['password'])) {
            $updateData['password'] = Hash::make($data['password']);
        }

        if (array_key_exists('status_akun', $data)) {
            $updateData['status_akun'] = $data['status_akun'];
        }

        if ($updateData === []) {
            return response()->json(['message' => 'Tidak ada perubahan yang disimpan.', 'data' => $user->load('role')]);
        }

        $user->update($updateData);
        $this->logAudit('update', 'users', $user->id, $before, $user->fresh('role')->toArray());

        return response()->json(['message' => 'Credential/status akun berhasil diperbarui.', 'data' => $user->fresh('role')]);
    }

    public function destroy(User $user): JsonResponse
    {
        if ($user->id === auth()->id() || ($user->role->nama_role ?? null) === 'super_admin') {
            return response()->json(['message' => 'Akun ini tidak boleh dihapus.'], 403);
        }

        if (($user->role->nama_role ?? null) !== 'admin') {
            return response()->json(['message' => 'Akun pelaku usaha tidak boleh dihapus. Nonaktifkan atau kunci akun jika perlu.'], 403);
        }

        $before = $user->load('role')->toArray();
        $user->delete();
        $this->logAudit('delete', 'users', $before['id'], $before, null);

        return response()->json(['message' => 'Admin berhasil dihapus.']);
    }

    private function isManagedAdmin(User $user): bool
    {
        return ($user->loadMissing('role')->role->nama_role ?? null) === 'admin';
    }
}
