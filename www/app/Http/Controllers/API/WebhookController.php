<?php
namespace App\Http\Controllers\API;


use App\Entities\Subscription;
use App\Http\Controllers\Controller;
use App\Services\ApplicationService;
use App\Services\SubscriptionsService;
use Illuminate\Http\Request;

class WebhookController extends Controller
{
    public function apple(Request $request, $applicationID, SubscriptionsService $subscriptionsService, ApplicationService $applicationService)
    {


        \Log::info('APPLE REQUEST DATA', [
            'data' => $request->all()
        ]);

        $latestReceipt = $request->input('latest_receipt_info');

        $originalTransactionId = $latestReceipt['original_transaction_id'];

        $subscription = Subscription::where('original_transaction_id', $originalTransactionId)->first();

        if (!is_null($subscription)) {
            $responseByApple = $subscriptionsService->getResponseAppleReceipt(
                $applicationID,
                $subscription->latest_receipt
            );

            $verifiedReceived = $subscriptionsService->verifyReceipt($responseByApple);


            if ($verifiedReceived['status'] == 'OK') {
                $responseByAppleBody = json_decode($responseByApple['body']);

                $environment = $responseByAppleBody->environment;

                if (isset($responseByAppleBody->latest_receipt_info)) {
                    $subscriptionsService->handlerReceipt(
                        $applicationID,
                        $subscription->device_id,
                        $subscription->screen_trial,
                        $environment,
                        (isset($responseByAppleBody->latest_receipt)) ? $responseByAppleBody->latest_receipt : null,
                        $responseByAppleBody->latest_receipt_info,
                        $responseByAppleBody->pending_renewal_info[0]
                    );
                }
            }
        }


        return [
            'status' => 'OK'
        ];

    }


}