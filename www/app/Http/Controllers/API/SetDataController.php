<?php
namespace App\Http\Controllers\API;


use App\Http\Controllers\Controller;
use App\Http\Requests\SetDataAppsflyerRequest;
use App\Services\SetDataService;
use Illuminate\Http\Request;

class SetDataController extends Controller
{
    public function appsflyer(SetDataAppsflyerRequest $request, SetDataService $setDataService)
    {
        \Log::info('SET DATA CONTROLLER');


        $setDataService->saveAppsflyerData(
            $request->input('udid'),
            $request->input('appsflyer_id'),
            $request->input('idfa')
        );

        return [
            'status' => 'OK'
        ];
    }
}