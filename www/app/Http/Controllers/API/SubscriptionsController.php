<?php
namespace App\Http\Controllers\Api;


use App\Http\Controllers\Controller;
use App\Http\Requests\VerifyReceiptRequest;
use App\Services\SubscriptionsService;
use Illuminate\Http\Request;

class SubscriptionsController extends Controller
{

    public function verifiedReceipt(VerifyReceiptRequest $request, SubscriptionsService $subscriptionsService)
    {


        $responseByApple = $subscriptionsService->getResponseAppleReceipt(
            $request->input('app_id'),
            $request->input('receipt-data')
        );



        $verifiedReceived = $subscriptionsService->verifyReceipt($responseByApple);


        if ($verifiedReceived['status'] == 'OK') {
            $responseByAppleBody = json_decode($responseByApple['body']);

            $environment = $responseByAppleBody->environment;

            $subscriptionsService->handlerReceipt(
                $request->input('app_id'),
                $request->input('udid'),
                $request->input('screen'),
                $environment,
                (isset($responseByAppleBody->latest_receipt)) ? $responseByAppleBody->latest_receipt : null,
                $responseByAppleBody->latest_receipt_info,
                $responseByAppleBody->pending_renewal_info[0]
            );
        }


        return $verifiedReceived;

        
    }
}