<?php

namespace Database\Seeders;

use App\Models\SystemSetting;
use App\Support\SystemSettingCatalog;
use App\Support\SystemSettings;
use Illuminate\Database\Seeder;

class SystemSettingSeeder extends Seeder
{
    public function run(): void
    {
        foreach (SystemSettingCatalog::definitions() as $key => $definition) {
            $setting = SystemSetting::firstOrNew(['key' => $key]);

            if (! $setting->exists) {
                $setting->value = $definition['default'] ?? '';
            }

            $setting->deskripsi = $definition['description'] ?? null;
            $setting->save();
        }

        SystemSettings::forget();
    }
}
