<?php
namespace App\Http\Controllers\Api;


use App\Entities\Application;
use App\Http\Controllers\Controller;
use App\Http\Requests\IsPremiumRequest;
use App\Http\Requests\VerifyReceiptRequest;
use App\Services\ApplicationService;
use App\Services\SubscriptionsService;
use Illuminate\Http\Request;

class SubscriptionsController extends Controller
{

    public function verifiedReceipt(VerifyReceiptRequest $request, SubscriptionsService $subscriptionsService)
    {


        $responseByApple = $subscriptionsService->getResponseAppleReceipt(
            $request->input('app_id'),
            $request->input('receipt-data'),
            Application::ENV_PROD
        );

        $verifiedReceived = $subscriptionsService->verifyReceipt($responseByApple);

        // костыль для валидации apple
        if ($verifiedReceived['status'] == 'ERROR') {

            $responseByApple = $subscriptionsService->getResponseAppleReceipt(
                $request->input('app_id'),
                $request->input('receipt-data'),
                Application::ENV_SANDBOX
            );

            $verifiedReceived = $subscriptionsService->verifyReceipt($responseByApple);
        }



        $res = null;


        if ($verifiedReceived['status'] == 'OK') {
            $responseByAppleBody = json_decode($responseByApple['body']);

            $environment = $responseByAppleBody->environment;

            if (isset($responseByAppleBody->latest_receipt_info)) {
                $res = $subscriptionsService->handlerReceipt(
                    $request->input('app_id'),
                    $request->input('udid'),
                    $request->input('screen'),
                    $environment,
                    (isset($responseByAppleBody->latest_receipt)) ? $responseByAppleBody->latest_receipt : null,
                    $responseByAppleBody->latest_receipt_info,
                    $responseByAppleBody->pending_renewal_info[0]
                );
            }

        }

        if (is_null($res)) {
            $obj = new \stdClass();
            $obj->trial = 0;
            $obj->exists = 0;
            $obj->is_premium = 0;

            $res = $obj;
        }

\Log::info('RESPONSE', ['response' => $res]);


        return ['data' => $res];

        
    }

    public function getIsPremium(
        IsPremiumRequest $request,
        ApplicationService $applicationService,
        SubscriptionsService $subscriptionsService
    )
    {
        try {

            \Log::info('BUNDLE_ID', [
                'data' => $request->get('bundle_id')
            ]);

            \Log::info('DEVICE_ID CHECK IS PREMIUM', [
                'data' => $request->get('udid')
            ]);



            $application = $applicationService->getApplicationByBundleId($request->get('bundle_id'));


            \Log::info('APPLICATION', [
                'data' => $application
            ]);

            \Log::info('IS PREMIUM', [
                'data' => $subscriptionsService->isPremium($application->id, $request->get('udid'))
            ]);

            return ['data' => $subscriptionsService->isPremium($application->id, $request->get('udid'))];

        } catch (\Exception $e) {

            \Log::info('IS PREMIUM', [
                'data' => false
            ]);

            return ['data' => false];
        }
    }
}
