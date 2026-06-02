@extends('layouts.admin')

@section('title', 'System Settings')
@section('page-title', 'System Settings')

@section('content')
    <div id="system-settings" class="space-y-6">
        @if (session('success'))
            <x-alert type="success">{{ session('success') }}</x-alert>
        @endif

        @if ($errors->any())
            <x-alert type="danger">
                <ul class="list-disc space-y-1 pl-5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </x-alert>
        @endif

        <x-alert type="info">
            System Settings dipakai untuk konfigurasi global website seperti identitas, navigasi, kontak, footer, tampilan data, dan pengaturan sistem yang aman. Konten halaman depan dikelola dari menu Landing Page, bukan dari halaman ini.
        </x-alert>

        @forelse($groupedSettings as $groupKey => $group)
            @php
                $hasFileUpload = collect($group['items'])->contains(fn ($item) => ($item['meta']['type'] ?? null) === 'image');
            @endphp

            <section id="settings-{{ $groupKey }}" class="scroll-mt-24 rounded-xl border border-slate-200 bg-white shadow-sm">
                <form
                    action="{{ route('super-admin.settings.update-group', $groupKey) }}"
                    method="POST"
                    @if ($hasFileUpload) enctype="multipart/form-data" @endif
                >
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="return_anchor" value="settings-{{ $groupKey }}">

                    <div class="border-b border-slate-200 px-6 py-5">
                        <p class="text-xs font-bold uppercase tracking-wide text-slate-500">Konfigurasi Global</p>
                        <h2 class="font-display mt-1 text-xl font-bold text-slate-900">{{ $group['label'] }}</h2>
                        <p class="mt-2 max-w-3xl text-sm leading-6 text-slate-600">{{ $group['description'] }}</p>

                        @if ($groupKey === 'navigation')
                            <div class="mt-3 rounded-lg border border-blue-100 bg-blue-50 px-4 py-3 text-sm text-blue-800">
                                Tujuan menu dikunci oleh sistem. Anda hanya mengubah nama menu.
                            </div>
                        @endif
                    </div>

                    <div class="grid gap-4 p-6 lg:grid-cols-2">
                        @foreach($group['items'] as $item)
                            @php
                                $setting = $item['setting'];
                                $meta = $item['meta'];
                                $inputType = $meta['type'] ?? 'text';
                                $fieldName = "values[{$setting->key}]";
                                $errorKey = "values.{$setting->key}";
                                $value = old("values.{$setting->key}", $setting->value);
                                $logoUrl = null;

                                if ($setting->key === 'site_logo_path' && $setting->value) {
                                    $logoUrl = \Illuminate\Support\Facades\Storage::disk('public')->url($setting->value);
                                }
                            @endphp

                            <div class="rounded-lg border border-slate-200 bg-slate-50 p-4">
                                <label for="setting-{{ $setting->id }}" class="block text-sm font-bold text-slate-900">
                                    {{ $meta['label'] }}
                                </label>

                                @if ($inputType === 'image')
                                    <div class="mt-3 flex flex-col gap-4 sm:flex-row sm:items-center">
                                        @if ($logoUrl)
                                            <img src="{{ $logoUrl }}" alt="Logo website saat ini" class="h-16 w-16 rounded-lg border border-slate-200 bg-white object-cover">
                                        @else
                                            <span class="flex h-16 w-16 items-center justify-center rounded-lg border border-dashed border-slate-300 bg-white text-slate-500">
                                                <span class="material-symbols-outlined text-[28px]">image</span>
                                            </span>
                                        @endif
                                        <input
                                            id="setting-{{ $setting->id }}"
                                            type="file"
                                            name="logo"
                                            accept=".jpg,.jpeg,.png,.webp"
                                            class="block w-full rounded-lg border border-slate-300 bg-white text-sm file:mr-4 file:border-0 file:bg-slate-100 file:px-4 file:py-2 file:font-semibold file:text-slate-700 hover:file:bg-slate-200"
                                        >
                                    </div>
                                    @error('logo')<p class="mt-1 text-sm text-red-700">{{ $message }}</p>@enderror
                                @elseif ($inputType === 'boolean')
                                    <input type="hidden" name="{{ $fieldName }}" value="0">
                                    <label class="mt-3 inline-flex items-center gap-3 rounded-lg border border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-700">
                                        <input
                                            id="setting-{{ $setting->id }}"
                                            type="checkbox"
                                            name="{{ $fieldName }}"
                                            value="1"
                                            @checked((string) $value === '1')
                                            class="rounded border-slate-300 text-slate-900 focus:ring-slate-900"
                                        >
                                        Aktif
                                    </label>
                                    @error($errorKey)<p class="mt-1 text-sm text-red-700">{{ $message }}</p>@enderror
                                @elseif ($inputType === 'textarea')
                                    <textarea
                                        id="setting-{{ $setting->id }}"
                                        name="{{ $fieldName }}"
                                        rows="3"
                                        class="mt-2 w-full rounded-lg border-slate-300 bg-white text-sm"
                                    >{{ $value }}</textarea>
                                    @error($errorKey)<p class="mt-1 text-sm text-red-700">{{ $message }}</p>@enderror
                                @else
                                    <input
                                        id="setting-{{ $setting->id }}"
                                        name="{{ $fieldName }}"
                                        type="{{ $inputType === 'number' ? 'number' : ($inputType === 'email' ? 'email' : ($inputType === 'time' ? 'time' : 'text')) }}"
                                        value="{{ $value }}"
                                        @if ($inputType === 'number') min="{{ $meta['min'] ?? 1 }}" max="{{ $meta['max'] ?? 100000 }}" step="1" @endif
                                        @if ($inputType === 'days_list') placeholder="30,14,7" @endif
                                        class="mt-2 w-full rounded-lg border-slate-300 bg-white text-sm"
                                    >
                                    @error($errorKey)<p class="mt-1 text-sm text-red-700">{{ $message }}</p>@enderror
                                @endif

                                <p class="mt-2 text-xs leading-5 text-slate-500">{{ $setting->deskripsi ?: 'Keterangan pengaturan dikelola oleh sistem.' }}</p>
                                <p class="mt-1 text-[11px] font-semibold uppercase tracking-wide text-slate-400">Kode internal: {{ $setting->key }}</p>
                            </div>
                        @endforeach
                    </div>

                    <div class="flex justify-end border-t border-slate-200 px-6 py-4">
                        <button class="rounded-lg bg-slate-900 px-4 py-2 text-sm font-semibold text-white">
                            Simpan {{ $group['label'] }}
                        </button>
                    </div>
                </form>
            </section>
        @empty
            <x-alert type="info">Belum ada pengaturan sistem. Jalankan seeder default agar super admin dapat mengelola konfigurasi global.</x-alert>
        @endforelse
    </div>
@endsection
