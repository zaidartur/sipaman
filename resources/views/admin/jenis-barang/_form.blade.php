@csrf
@if (($method ?? 'POST') !== 'POST') @method($method) @endif

@php
    $aliases = old('aliases', isset($jenisBarang) ? $jenisBarang->aliases->sortBy('priority')->pluck('keyword')->implode("\n") : '');
@endphp

<div class="grid gap-4 md:grid-cols-2">
    <div>
        <label class="text-sm font-semibold text-slate-700">Nama Jenis</label>
        <input name="nama_jenis" value="{{ old('nama_jenis', $jenisBarang->nama_jenis ?? '') }}" required class="form-input-sipaman mt-1 w-full" placeholder="Contoh: Keripik Singkong">
        @error('nama_jenis')<p class="mt-2 text-sm text-red-700">{{ $message }}</p>@enderror
    </div>
    <div>
        <label class="text-sm font-semibold text-slate-700">Slug</label>
        <input name="slug" value="{{ old('slug', $jenisBarang->slug ?? '') }}" class="form-input-sipaman mt-1 w-full" placeholder="Otomatis jika dikosongkan">
        @error('slug')<p class="mt-2 text-sm text-red-700">{{ $message }}</p>@enderror
        <p class="mt-1 text-xs text-slate-500">Dipakai sebagai kode ramah URL, bukan data legal PIRT.</p>
    </div>
</div>

<div class="mt-4">
    <label class="text-sm font-semibold text-slate-700">Keterangan Jenis</label>
    <textarea name="deskripsi" rows="3" class="form-textarea-sipaman mt-1 w-full" placeholder="Jelaskan jenis pangan ini agar admin mudah mereview">{{ old('deskripsi', $jenisBarang->deskripsi ?? '') }}</textarea>
    @error('deskripsi')<p class="mt-2 text-sm text-red-700">{{ $message }}</p>@enderror
</div>

<div class="mt-4">
    <label class="text-sm font-semibold text-slate-700">Alias / Keyword Import</label>
    <textarea name="aliases" rows="8" class="form-textarea-sipaman mt-1 w-full" placeholder="Satu keyword per baris, contoh:&#10;keripik&#10;kripik&#10;snack">{{ $aliases }}</textarea>
    @error('aliases')<p class="mt-2 text-sm text-red-700">{{ $message }}</p>@enderror
    <p class="mt-1 text-xs text-slate-500">Sistem mencocokkan keyword ini dengan Jenis Pangan dari file Rekap PIRT.</p>
</div>

<label class="mt-4 inline-flex items-center gap-2 rounded-lg border border-slate-200 px-3 py-2 text-sm font-semibold text-slate-700">
    <input type="hidden" name="is_active" value="0">
    <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $jenisBarang->is_active ?? true)) class="form-checkbox-sipaman">
    Aktif untuk filter dan klasifikasi
</label>

<div class="mt-6 flex gap-3">
    <button class="rounded-lg bg-slate-900 px-5 py-2.5 font-semibold text-white">Simpan</button>
    <a href="{{ route('panel.jenis-barang.index') }}" class="rounded-lg border border-slate-300 px-5 py-2.5 font-semibold">Batal</a>
</div>
