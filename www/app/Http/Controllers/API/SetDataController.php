<?php
namespace App\Http\Controllers\API;


use App\Http\Controllers\Controller;
use App\Http\Requests\SetDataAppsflyerRequest;
use App\Http\Requests\SetDataFacebookRequest;
use App\Services\SetDataService;
use Illuminate\Http\Request;

class SetDataController extends Controller
{
    public function appsflyer(SetDataAppsflyerRequest $request, SetDataService $setDataService)
    {
        \Log::info('SET DATA CONTROLLER', [
            'data' => $request->all()
        ]);


        $setDataService->saveAppsflyerData(
            $request->input('udid'),
            $request->input('appsflyer_id'),
            $request->input('idfa'),
            $request->input('unique_id')
        );

        return [
            'status' => 'OK'
        ];
    }

    public function facebook(SetDataFacebookRequest $request, SetDataService $setDataService)
    {
//        \Log::info('SET DATA FACEBOOK', [
//            'data' => $request->all()
//        ]);


        $setDataService->saveFacebookData(
            $request->input('bundle_id'),
            $request->input('udid'),
            $request->input('bundle_version'),
            $request->input('advertiser_id'),
            $request->input('advertiser_tracking_enabled'),
            $request->input('extinfo'),
            $request->input('bundle_short_version'),
            $request->input('application_tracking_enabled'),
            $request->input('attribution')
        );


        return [
            'status' => 'OK'
        ];
    }
}
