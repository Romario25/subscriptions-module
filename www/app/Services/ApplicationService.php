<?php
namespace App\Services;


use App\Entities\Application;
use App\Entities\ApplicationDevice;

class ApplicationService
{
    public function getApplicationById($id)
    {
        return Application::findOrFail($id);
    }

    public function getApplicationByAppId($appId) : ?Application
    {
        return Application::where('app_id', $appId)
            ->firstOrFail();
    }

    public function add($bundlerId, $appId, $name, $environment)
    {
        return Application::create([
            'bundle_id' => $bundlerId,
            'app_id' => $appId,
            'name' => $name,
            'environment' => $environment
        ]);
    }

    public function getApplicationDeviceInfo($applicationId, $deviceId)
    {
        return  ApplicationDevice::where('application_id', $applicationId)
            ->where('device_id', $deviceId)->first();
    }


}
