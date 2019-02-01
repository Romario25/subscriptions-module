<?php
namespace App\Services;




use App\DTO\SubscriptionDto;
use App\Entities\Subscription;
use Carbon\Carbon;

class SubscriptionsService
{

    private $config;
    private $verifyService;
    private $receiptService;
    private $applicationService;

    public function __construct(ReceiptService $receiptService, VerifyService $verifyService, ApplicationService $applicationService)
    {
        $this->verifyService = $verifyService;
        $this->receiptService = $receiptService;
        $this->applicationService = $applicationService;
    }


    public function handlerAppleWebhook($data)
    {

        HandlerAppleWebhook::handler($data);
    }

    public function handlerReceipt($appId, $deviceId, $environment, $latestReceipt, $latestReceiptInfo, $pendingRenewalInfo)
    {

        $endLatestReceiptInfo = end($latestReceiptInfo);

        $type = $this->defineType($pendingRenewalInfo, $latestReceiptInfo);


        $subscriptionDTO = new SubscriptionDto(
            $appId,
            $deviceId,
            $endLatestReceiptInfo->original_transaction_id,
            $endLatestReceiptInfo->product_id,
            $environment,
            $type,
            $endLatestReceiptInfo->purchase_date_ms,
            $endLatestReceiptInfo->expires_date_ms,
            $latestReceipt
        );

        $subscription = SaveSubscriptionService::saveSubscription($subscriptionDTO);

        $diffTransaction = SaveSubscriptionService::checkReceiptHistory($latestReceiptInfo, $subscription);




        if (count($diffTransaction) == 1) {
            AppslyerService::sendEvent(
                $this->getEventBySubscription($subscription),
                '2DD5392C-ACA8-40C1-A309-2875582C3567',
                $deviceId,
                0);
        } else {
            foreach ($diffTransaction as $transaction) {
                $transactionHistory = SubscriptionHistory::where('transaction_id', $transaction)
                    ->first();



                AppslyerService::sendEvent(
                    $this->getEventBySubscription($transactionHistory),
                    '2DD5392C-ACA8-40C1-A309-2875582C3567',
                    $deviceId,
                    0);

            }
        }
//
//
//        if ($type == Subscription::TYPE_CANCEL) {
//            SaveSubscriptionService::createCancelReceiptHistory($subscription);
//
//            $event = $this->getEventBySubscription($subscription);
//
//            AppslyerService::sendEvent(
//                $event,
//                '2DD5392C-ACA8-40C1-A309-2875582C3567',
//                $deviceId,
//                0);
//        }

    }

    public function getResponseAppleReceipt($appId, $latestReceipt)
    {
        $application = $this->applicationService->getApplicationByAppId($appId);


        return $this->receiptService->sendReceipt(
            $latestReceipt,
            $application->environment,
            $application->shared_secret
        );
    }




    public function verifyReceipt($receiptToken)
    {
        try {

            $verifyData = $this->verifyService->verifyReceipt($receiptToken);

            return [
                'status' => 'OK'
            ];

        } catch (\Exception $e) {
            return [
                'status' => 'ERROR',
                'message' => $e->getMessage()
            ];
        }

    }




    private function sortLatestReceiptInfo($latestReceiptInfo) : array
    {
        $collect = collect($latestReceiptInfo);

        return $collect->sortBy('purchase_date_ms')->toArray();
    }

    private function defineType($pendingRenewalInfo, $latestReceiptInfo)
    {
        if (isset($pendingRenewalInfo->expiration_intent) && $pendingRenewalInfo->expiration_intent == 1 ) {
            return Subscription::TYPE_CANCEL;
        }

        $receiptInfo = $this->sortLatestReceiptInfo($latestReceiptInfo);

        $endReceiptInfo = end($receiptInfo);

        $countReceiptInfo = count($receiptInfo);

        if ($endReceiptInfo->is_trial_period == "true") {
            return Subscription::TYPE_TRIAL;
        }

        if ($countReceiptInfo == 1 && $endReceiptInfo->is_trial_period == "false") {
            return Subscription::TYPE_INITIAL_BUY;
        }

        if ($countReceiptInfo == 2  && !isset($pendingRenewalInfo->expiration_intent)) {

            if ($receiptInfo[0]->is_trial_period == "true") {
                return Subscription::TYPE_INITIAL_BUY;
            }
        }

        return Subscription::TYPE_RENEWAL;
    }

    public function getEventBySubscription($subscription)
    {
        $config = config('subscriptions');

        $eventDuration = $config['events_duration'];

        $subscriptionType = $subscription->type;

        $prefix = 'test_';

        $event = '';

        $key = array_search($subscription->product_id, $eventDuration);

        switch ($subscriptionType) {
            case Subscription::TYPE_TRIAL:
                $event =  $prefix . 'start_trial';
            break;
            case Subscription::TYPE_INITIAL_BUY:
                $event = $prefix . $key . '_1';
            break;
            case Subscription::TYPE_RENEWAL:
                $count = SubscriptionHistory::where('subscription_id')
                    ->where('type', Subscription::TYPE_RENEWAL)->count();
                $event = $prefix . $key . '_' . $count;
            break;
            case Subscription::TYPE_CANCEL:
                $count = SubscriptionHistory::where('subscription_id')
                    ->where('type', Subscription::TYPE_RENEWAL)->count();
                $event = $prefix . 'cancel_' . $key . '_' . $count;
            break;
        }

        return $event;
    }

    public function checkSubscription()
    {
        $now = Carbon::now()->timestamp;
      //  \Log::info('NOW : ' . $now);
        $subscriptions = Subscription::where('end_date', '<', $now * 1000)
            ->where('type', Subscription::TYPE_RENEWAL)
            ->orWhere('type', Subscription::TYPE_INITIAL_BUY)->get();
        //dd($subscriptions);
        foreach ($subscriptions as $subscription) {

            \Log::info('SUBCRIPTION ID : ' . $subscription->id);

            $responseByApple = $this->getResponseAppleReceipt($subscription->latest_receipt);

            $responseByAppleBody = json_decode($responseByApple['body']);



            $environment = $responseByAppleBody->environment;

            $this->handlerReceipt(
                $subscription->device_id,
                $environment,
                $responseByAppleBody->latest_receipt,
                $responseByAppleBody->latest_receipt_info,
                $responseByAppleBody->pending_renewal_info[0]
            );
        }
    }


}