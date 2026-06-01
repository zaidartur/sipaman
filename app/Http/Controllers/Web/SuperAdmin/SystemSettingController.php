<?php

namespace App\Http\Controllers\Web\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Http\Requests\SuperAdmin\UpdateSystemSettingGroupRequest;
use App\Http\Requests\SuperAdmin\UpdateSystemSettingRequest;
use App\Models\SystemSetting;
use App\Services\SystemSettingService;
use App\Traits\LogsAuditTrail;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class SystemSettingController extends Controller
{
    use LogsAuditTrail;

    public function __construct(private SystemSettingService $systemSettingService)
    {
    }

    public function index(): View
    {
        $groupedSettings = $this->systemSettingService->groupedSettings();

        return view('super-admin.settings.index', compact('groupedSettings'));
    }

    public function update(UpdateSystemSettingRequest $request, SystemSetting $setting): RedirectResponse
    {
        $before = $setting->toArray();
        $updated = $this->systemSettingService->update($setting, $request->validated(), $request->file('logo'));
        $this->logAudit('update', 'system_settings', $setting->id, $before, $updated->toArray());

        $groupKey = $this->systemSettingService->sectionDefinitionGroup($setting->key);
        $anchor = $request->input('return_anchor', $groupKey ? 'settings-' . $groupKey : 'system-settings');

        return $this->redirectToSettingsAnchor($anchor)->with('success', 'Pengaturan sistem berhasil diperbarui.');
    }

    public function updateGroup(UpdateSystemSettingGroupRequest $request, string $group): RedirectResponse
    {
        $before = SystemSetting::query()
            ->whereIn('key', array_keys($this->systemSettingService->definitionsForGroup($group)))
            ->get()
            ->keyBy('key');

        $updatedSettings = $this->systemSettingService->updateGroup($group, $request->settingValues(), $request->file('logo'));

        foreach ($updatedSettings as $updated) {
            $old = $before->get($updated->key);
            $this->logAudit(
                'update',
                'system_settings',
                $updated->id,
                $old?->toArray(),
                $updated->toArray()
            );
        }

        return $this->redirectToSettingsAnchor($request->returnAnchor())->with('success', 'Pengaturan sistem berhasil diperbarui.');
    }

    private function redirectToSettingsAnchor(string $anchor): RedirectResponse
    {
        $anchor = preg_match('/^[A-Za-z0-9_-]+$/', $anchor) ? $anchor : 'system-settings';

        return redirect()->to(route('super-admin.settings.index') . '#' . $anchor);
    }
}
