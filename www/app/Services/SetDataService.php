<?php
namespace App\Services;


use App\Entities\Application;
use App\Entities\ApplicationDevice;

class SetDataService
{
    private $applicationService;

    private $applicationDeviceService;


    /**
     * SetDataService constructor.
     * @param ApplicationService $applicationService
     * @param ApplicationDeviceService $applicationDeviceService
     */
    public function __construct(ApplicationService $applicationService, ApplicationDeviceService $applicationDeviceService)
    {
        $this->applicationService = $applicationService;
        $this->applicationDeviceService = $applicationDeviceService;
    }


    public function saveAppsflyerData($udid, $appId, $idfa, $appsflyerUniqueId)
    {
        $application = $this->applicationService->getApplicationByAppId($appId);

        $applicationDevice = $this->applicationDeviceService->getDevice($application->id, $udid);

        if (is_null($applicationDevice)) {
            $this->applicationDeviceService->add($application->id, $udid, $idfa, $appsflyerUniqueId);
        } else {
            $this->applicationDeviceService->update( $application->id, $udid, $idfa, $appsflyerUniqueId);
        }

    }
}