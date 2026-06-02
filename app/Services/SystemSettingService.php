<?php

namespace App\Services;

use App\Models\SystemSetting;
use App\Support\SystemSettingCatalog;
use App\Support\SystemSettings;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class SystemSettingService
{
    private const LOGO_DIRECTORY = 'settings/site-logo';

    public function groupedSettings(): array
    {
        $settings = $this->managedSettings()->keyBy('key');
        $groups = [];

        foreach (SystemSettingCatalog::groups() as $groupKey => $group) {
            $items = [];

            foreach (SystemSettingCatalog::definitions() as $key => $definition) {
                if (($definition['group'] ?? null) !== $groupKey) {
                    continue;
                }

                $items[] = [
                    'setting' => $settings->get($key),
                    'meta' => ['key' => $key, ...$definition],
                ];
            }

            if ($items !== []) {
                $groups[$groupKey] = [
                    'label' => $group['label'],
                    'description' => $group['description'],
                    'items' => $items,
                ];
            }
        }

        return $groups;
    }

    public function managedSettings(): Collection
    {
        $this->ensureDefaults();

        $settings = SystemSetting::query()
            ->whereIn('key', SystemSettingCatalog::keys())
            ->get()
            ->keyBy('key');

        return collect(SystemSettingCatalog::keys())
            ->map(fn (string $key) => $settings->get($key))
            ->filter();
    }

    public function update(SystemSetting $setting, array $data, ?UploadedFile $logo = null): SystemSetting
    {
        $definition = SystemSettingCatalog::definition($setting->key);

        if (! $definition) {
            throw ValidationException::withMessages([
                'value' => 'Pengaturan ini tidak tersedia di halaman System Settings.',
            ]);
        }

        $oldLogoPath = $setting->key === 'site_logo_path' ? $setting->value : null;

        if ($setting->key === 'site_logo_path') {
            if ($logo instanceof UploadedFile) {
                $setting->value = $logo->store(self::LOGO_DIRECTORY, 'public');
            }
        } else {
            $setting->value = array_key_exists('value', $data)
                ? $this->normalizeValue($data['value'], $definition)
                : $setting->value;
        }

        $setting->deskripsi = $definition['description'] ?? $setting->deskripsi;
        $setting->save();

        if ($setting->key === 'site_logo_path' && $logo instanceof UploadedFile) {
            $this->deleteOldUploadedLogo($oldLogoPath, $setting->value);
        }

        SystemSettings::forget();

        return $setting->fresh();
    }

    public function updateGroup(string $groupKey, array $values, ?UploadedFile $logo = null): Collection
    {
        $definitions = $this->definitionsForGroup($groupKey);

        if ($definitions === []) {
            throw ValidationException::withMessages([
                'group' => 'Grup pengaturan tidak tersedia.',
            ]);
        }

        $this->ensureDefaults();

        $settings = SystemSetting::query()
            ->whereIn('key', array_keys($definitions))
            ->get()
            ->keyBy('key');

        $updated = collect();

        foreach ($definitions as $key => $definition) {
            $setting = $settings->get($key);

            if (! $setting instanceof SystemSetting) {
                continue;
            }

            if ($key === 'site_logo_path') {
                if ($logo instanceof UploadedFile) {
                    $updated->push($this->update($setting, [], $logo));
                }

                continue;
            }

            if (array_key_exists($key, $values)) {
                $updated->push($this->update($setting, ['value' => $values[$key]]));
            }
        }

        if ($updated->isNotEmpty()) {
            SystemSettings::forget();
        }

        return $updated;
    }

    public function ensureDefaults(): void
    {
        $changed = false;

        foreach (SystemSettingCatalog::definitions() as $key => $definition) {
            $setting = SystemSetting::firstOrNew(['key' => $key]);
            $settingChanged = false;

            if (! $setting->exists) {
                $setting->value = $this->defaultValue($key, $definition);
                $settingChanged = true;
            }

            $description = $definition['description'] ?? null;

            if ($setting->deskripsi !== $description) {
                $setting->deskripsi = $description;
                $settingChanged = true;
            }

            if ($settingChanged) {
                $setting->save();
                $changed = true;
            }
        }

        if ($changed) {
            SystemSettings::forget();
        }
    }

    private function defaultValue(string $key, array $definition): string
    {
        $legacyFallbacks = [
            'site_logo_path' => 'logo_path',
            'contact_phone' => 'contact_whatsapp',
        ];

        if (isset($legacyFallbacks[$key])) {
            $legacyValue = SystemSetting::query()
                ->where('key', $legacyFallbacks[$key])
                ->value('value');

            if (is_string($legacyValue) && trim($legacyValue) !== '') {
                return trim($legacyValue);
            }
        }

        return (string) ($definition['default'] ?? '');
    }

    public function definitionsForGroup(string $groupKey): array
    {
        $groups = SystemSettingCatalog::groups();

        if (! array_key_exists($groupKey, $groups)) {
            return [];
        }

        return collect(SystemSettingCatalog::definitions())
            ->filter(fn (array $definition) => ($definition['group'] ?? null) === $groupKey)
            ->all();
    }

    public function sectionDefinitionGroup(string $key): ?string
    {
        return SystemSettingCatalog::definition($key)['group'] ?? null;
    }

    private function normalizeValue(mixed $value, array $definition): ?string
    {
        if ($value === null) {
            return null;
        }

        $normalized = is_string($value) ? trim($value) : (string) $value;

        if (($definition['type'] ?? null) === 'number' && $normalized !== '') {
            return (string) (int) $normalized;
        }

        if (($definition['type'] ?? null) === 'boolean') {
            return in_array(strtolower($normalized), ['1', 'true', 'on', 'yes'], true) ? '1' : '0';
        }

        if (($definition['type'] ?? null) === 'days_list') {
            $days = collect(preg_split('/[\s,]+/', $normalized, -1, PREG_SPLIT_NO_EMPTY) ?: [])
                ->map(fn (string $day) => (int) $day)
                ->filter(fn (int $day) => $day >= 1 && $day <= 365)
                ->unique()
                ->sortDesc()
                ->values()
                ->all();

            return $days === [] ? null : implode(',', $days);
        }

        if (($definition['type'] ?? null) === 'time') {
            return $normalized;
        }

        return $normalized === '' ? null : $normalized;
    }

    private function deleteOldUploadedLogo(?string $oldPath, ?string $newPath): void
    {
        if (! $oldPath || $oldPath === $newPath) {
            return;
        }

        if (! str_starts_with($oldPath, self::LOGO_DIRECTORY . '/')) {
            return;
        }

        Storage::disk('public')->delete($oldPath);
    }
}
