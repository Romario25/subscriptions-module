<?php
namespace App\Services;


use App\Entities\ApplicationDevice;

class ApplicationDeviceService
{
    public function getDevice($applicationId, $deviceId)
    {
        return ApplicationDevice::where('application_id', $applicationId)
            ->where('device_id', $deviceId)->first();
    }

    public function add($applicationId, $deviceId, $idfa, $appsflyerUniqueId)
    {
        return ApplicationDevice::create([
            'application_id' => $applicationId,
            'device_id' => $deviceId,
            'idfa' => $idfa,
            'appsflyer_unique_id' => $appsflyerUniqueId
        ]);
    }

    public function update($application_id, $deviceId, $idfa, $appsflyerUniqueId)
    {

        return ApplicationDevice::where('application_id', $application_id)
            ->where('device_id', $deviceId)
            ->update([
                'idfa' => $idfa,
                'appsflyer_unique_id' => $appsflyerUniqueId
            ]);
    }

}