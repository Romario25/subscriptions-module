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

    public function add($applicationId, $deviceId, $idfa)
    {
        return ApplicationDevice::create([
            'application_id' => $applicationId,
            'device_id' => $deviceId,
            'idfa' => $idfa
        ]);
    }

    public function update($id, $application_id, $deviceId, $idfa)
    {
        $applicationDevice = ApplicationDevice::findOrFail($id);

        return $applicationDevice->update([
            'application_id' => $application_id,
            'device_id' => $deviceId,
            'idfa' => $idfa
        ]);
    }
}