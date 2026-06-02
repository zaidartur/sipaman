@csrf
@if (($method ?? 'POST') !== 'POST') @method($method) @endif

@php($isEdit = isset($user))

@if (! $isEdit)
    <input type="hidden" name="role" value="admin">
    <div class="grid gap-4 md:grid-cols-2">
        <div>
            <label class="text-sm font-semibold">Nama Admin</label>
            <input name="nama" value="{{ old('nama') }}" required autocomplete="name" class="form-input-sipaman mt-1 w-full" placeholder="Nama admin">
            @error('nama')<p class="mt-1 text-sm text-red-700">{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="text-sm font-semibold">Email Login</label>
            <input type="email" name="email" value="{{ old('email') }}" required autocomplete="email" class="form-input-sipaman mt-1 w-full" placeholder="admin@sipaman.id">
            @error('email')<p class="mt-1 text-sm text-red-700">{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="text-sm font-semibold">Password</label>
            <input type="password" name="password" required autocomplete="new-password" class="form-input-sipaman mt-1 w-full" placeholder="Minimal 8 karakter">
            @error('password')<p class="mt-1 text-sm text-red-700">{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="text-sm font-semibold">Status Akun</label>
            <select name="status_akun" class="form-select-sipaman mt-1 w-full">
                @foreach(['aktif','nonaktif','kunci'] as $status)
                    <option value="{{ $status }}" @selected(old('status_akun', 'aktif') === $status)>{{ ucfirst($status) }}</option>
                @endforeach
            </select>
            @error('status_akun')<p class="mt-1 text-sm text-red-700">{{ $message }}</p>@enderror
        </div>
    </div>
@else
    <div class="grid gap-4 md:grid-cols-2">
        <div>
            <label class="text-sm font-semibold">Nama</label>
            <div class="mt-1 rounded-lg border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm text-slate-700">{{ $user->nama }}</div>
        </div>
        <div>
            <label class="text-sm font-semibold">Role</label>
            <div class="mt-1 rounded-lg border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm text-slate-700">{{ str_replace('_', ' ', $user->role?->nama_role ?? '-') }}</div>
        </div>
        <div>
            <label class="text-sm font-semibold">Email Login</label>
            <div class="mt-1 rounded-lg border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm text-slate-700">{{ $user->email ?: '-' }}</div>
            <p class="mt-1 text-xs text-slate-500">Admin login memakai email ini.</p>
        </div>
        <div>
            <label class="text-sm font-semibold">Password Baru</label>
            <input type="password" name="password" autocomplete="new-password" class="form-input-sipaman mt-1 w-full" placeholder="Kosongkan jika tidak diganti">
            @error('password')<p class="mt-1 text-sm text-red-700">{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="text-sm font-semibold">Status Akun</label>
            <select name="status_akun" class="form-select-sipaman mt-1 w-full">
                @foreach(['aktif','nonaktif','kunci'] as $status)
                    <option value="{{ $status }}" @selected(old('status_akun', $user->status_akun ?? 'aktif') === $status)>{{ ucfirst($status) }}</option>
                @endforeach
            </select>
            @error('status_akun')<p class="mt-1 text-sm text-red-700">{{ $message }}</p>@enderror
        </div>
    </div>
@endif

<div class="mt-6 flex gap-3">
    <button class="rounded-lg bg-slate-900 px-5 py-2.5 font-semibold text-white">Simpan</button>
    <a href="{{ route('super-admin.users.index') }}" class="rounded-lg border border-slate-300 px-5 py-2.5 font-semibold">Batal</a>
</div>
