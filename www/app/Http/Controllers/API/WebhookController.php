<?php
namespace App\Http\Controllers\API;


use App\Http\Controllers\Controller;
use App\Services\SubscriptionsService;
use Illuminate\Http\Request;

class WebhookController extends Controller
{
    public function apple(Request $request, $applicationID, SubscriptionsService $subscriptionsService)
    {


        \Log::info('REQUEST DATA', [
            'data' => $request->all()
        ]);




//        $subscriptionsService->handlerReceipt(
//            $subscription->application->app_id,
//            $subscription->device_id,
//            $subscription->screen_trial,
//            $environment,
//            $responseByAppleBody->latest_receipt,
//            $responseByAppleBody->latest_receipt_info,
//            $responseByAppleBody->pending_renewal_info[0]
//        );


//
//        dump($applicationID);
//        dd($request->all());

        return [
            'status' => 'OK'
        ];

    }


}