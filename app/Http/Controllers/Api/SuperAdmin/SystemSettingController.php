<?php

namespace App\Http\Controllers\Api\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Http\Requests\SuperAdmin\UpdateSystemSettingRequest;
use App\Models\SystemSetting;
use App\Services\SystemSettingService;
use App\Traits\LogsAuditTrail;
use Illuminate\Http\JsonResponse;

class SystemSettingController extends Controller
{
    use LogsAuditTrail;

    public function __construct(private SystemSettingService $systemSettingService)
    {
    }

    public function index(): JsonResponse
    {
        return response()->json([
            'data' => $this->systemSettingService->managedSettings()->values(),
            'groups' => $this->systemSettingService->groupedSettings(),
        ]);
    }

    public function update(UpdateSystemSettingRequest $request, SystemSetting $setting): JsonResponse
    {
        $before = $setting->toArray();
        $updated = $this->systemSettingService->update($setting, $request->validated(), $request->file('logo'));
        $this->logAudit('update', 'system_settings', $setting->id, $before, $updated->toArray());

        return response()->json(['message' => 'Pengaturan sistem berhasil diperbarui.', 'data' => $updated]);
    }
}
