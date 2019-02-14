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

    public function update($applicationId, $deviceId, $idfa, $appsflyerUniqueId)
    {

        return ApplicationDevice::where('application_id', $applicationId)
            ->where('device_id', $deviceId)
            ->update([
                'idfa' => $idfa,
                'appsflyer_unique_id' => $appsflyerUniqueId
            ]);
    }

    public function addFacebookData($applicationId, $deviceId, $bundleVersion, $advertiserId, $advertiserTrackingEnabled, $extinfo, $bundleShortVersion, $applicationTrackingEnabled, $attribution)
    {
        return ApplicationDevice::create([
            'application_id' => $applicationId,
            'device_id' => $deviceId,
            'bundle_version' => $bundleVersion,
            'idfa' => $advertiserId,
            'extinfo' => $extinfo,
            'bundle_short_version' => $bundleShortVersion,
            'application_tracking_enabled' => $applicationTrackingEnabled,
            'advertiser_tracking_enabled' => $advertiserTrackingEnabled,
            'attribution' => $attribution
        ]);
    }

    public function updateFacebookData($applicationId, $deviceId, $bundleVersion, $advertiserId, $advertiserTrackingEnabled, $extinfo, $bundleShortVersion, $applicationTrackingEnabled, $attribution)
    {

//        $data = ApplicationDevice::where('application_id', $applicationId)
//            ->where('device_id', $deviceId)->first();
//
//        dd($data->extinfo);


        return ApplicationDevice::where('application_id', $applicationId)
            ->where('device_id', $deviceId)
            ->update([
                'bundle_version' => $bundleVersion,
                'idfa' => $advertiserId,
                'extinfo' => json_encode($extinfo),
                'bundle_short_version' => $bundleShortVersion,
                'application_tracking_enabled' => $applicationTrackingEnabled,
                'advertiser_tracking_enabled' => $advertiserTrackingEnabled,
                'attribution' => $attribution
            ]);
    }

}