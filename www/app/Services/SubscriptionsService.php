<?php
namespace App\Services;




use App\DTO\SubscriptionDto;
use App\Entities\ApplicationDevice;
use App\Entities\ApplicationProduct;
use App\Entities\Subscription;
use App\Entities\SubscriptionHistory;
use Carbon\Carbon;

class SubscriptionsService
{

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

    /**
     * @param $appId
     * @param $deviceId
     * @param $screen
     * @param $environment
     * @param $latestReceipt
     * @param array $latestReceiptInfo
     * @param object $pendingRenewalInfo
     */
    public function handlerReceipt($appId, $deviceId, $screen, $environment, $latestReceipt, $latestReceiptInfo, $pendingRenewalInfo)
    {

        $exists = 0;
        $trial = 0;


        $endLatestReceiptInfo = end($latestReceiptInfo);

        $type = $this->defineType($pendingRenewalInfo, $latestReceiptInfo);

        if ($type == Subscription::TYPE_TRIAL) {
            $trial = 1;
        }

        $subscriptionDTO = new SubscriptionDto(
            $appId,
            $deviceId,
            $screen,
            $endLatestReceiptInfo->original_transaction_id,
            $endLatestReceiptInfo->product_id,
            $environment,
            $type,
            $endLatestReceiptInfo->purchase_date_ms,
            (isset($endLatestReceiptInfo->expires_date_ms)) ? $endLatestReceiptInfo->expires_date_ms : 0,
            $latestReceipt
        );

        if (!is_null(SaveSubscriptionService::issetSubscription($subscriptionDTO->deviceId, $subscriptionDTO->originalTransactionId))) {
            $exists = 1;
        }

        /** @var Subscription $subscription */
        $subscription = SaveSubscriptionService::saveSubscription($subscriptionDTO);


        $diffTransaction = SaveSubscriptionService::checkReceiptHistory($latestReceiptInfo, $subscription);



        /** @var ApplicationDevice $applicationDevices */
        $applicationDevices = $this->applicationService->getApplicationDeviceInfo($subscription->application->id, $subscription->device_id);

        $startDate = Carbon::now()->startOfDay()->timestamp;


        \Log::info('SUBSCRIPTION TYPE : ' . $subscription->type);

        \Log::info('DIFF TRANSACTION', ['data' => $diffTransaction]);
        \Log::info('COUNT DIFF TRANSACTION', ['data' => count($diffTransaction)]);


        if (count($diffTransaction) == 1  && $diffTransaction[0]['purchase_date_ms'] > $startDate * 1000) {


            $event = $this->getEventBySubscription($subscription);



            if ($subscription->type == Subscription::TYPE_TRIAL) {


                \Log::info('BLOCK_TRIAL_1');

                AppslyerService::sendEvent(
                    $subscription->application->appsflyer_dev_key,
                    $event['event_name'],
                    $subscription->application->app_id,
                    (!is_null($applicationDevices)) ? $applicationDevices->idfa : null,
                    $subscription->application->bundle_id,
                    $deviceId,
                    (!is_null($applicationDevices)) ? $applicationDevices->appsflyer_unique_id : null,
                    0
                );

                FacebookService::sendEvent($applicationDevices, 'StartTrial', 0);

                if (!empty($event['event_screen'])) {
                    AppslyerService::sendEvent(
                        $subscription->application->appsflyer_dev_key,
                        $event['event_screen'],
                        $subscription->application->app_id,
                        (!is_null($applicationDevices)) ? $applicationDevices->idfa : null,
                        $subscription->application->bundle_id,
                        $deviceId,
                        (!is_null($applicationDevices)) ? $applicationDevices->appsflyer_unique_id : null,
                        0);

                    FacebookService::sendEvent($applicationDevices, $event['event_screen'], 0);
                }

            } else {

                \Log::info('BLOCK_TRIAL_2');

                if ($event['price'] > 0) {
                    AppslyerService::sendEvent(
                        $subscription->application->appsflyer_dev_key,
                        'af_purchase',
                        $subscription->application->app_id,
                        (!is_null($applicationDevices)) ? $applicationDevices->idfa : null,
                        $subscription->application->bundle_id,
                        $deviceId,
                        (!is_null($applicationDevices)) ? $applicationDevices->appsflyer_unique_id : null,
                        $event['price']);

                    AppslyerService::sendEvent(
                        $subscription->application->appsflyer_dev_key,
                        $event['event_name'],
                        $subscription->application->app_id,
                        (!is_null($applicationDevices)) ? $applicationDevices->idfa : null,
                        $subscription->application->bundle_id,
                        $deviceId,
                        (!is_null($applicationDevices)) ? $applicationDevices->appsflyer_unique_id : null,
                        0);

                    FacebookService::sendEvent($applicationDevices, $event['event_name'], 0);
                    FacebookService::sendEvent($applicationDevices, 'fb_mobile_purchase', $event['price']);
                }
            }



        } else {
            if (count($diffTransaction) > 1) {

                $endDiffTransaction = end($diffTransaction);

                \Log::info('END DIFF TRANSACTION', ['data' => $endDiffTransaction]);

                if (isset($endDiffTransaction['purchase_date_ms']) && $endDiffTransaction['purchase_date_ms'] > $startDate * 1000) {

                    \Log::info('BLOCK_TRIAL_3');

                    $transactionHistory = SubscriptionHistory::where('transaction_id', $endDiffTransaction['transaction_id'])
                        ->first();



                    $event = $this->getEventBySubscription($transactionHistory->subscription);

                    AppslyerService::sendEvent(
                        $subscription->application->appsflyer_dev_key,
                        $event['event_name'],
                        $subscription->application->app_id,
                        (!is_null($applicationDevices)) ? $applicationDevices->idfa : null,
                        $subscription->application->bundle_id,
                        $deviceId,
                        (!is_null($applicationDevices)) ? $applicationDevices->appsflyer_unique_id : null,
                        0);

                    FacebookService::sendEvent($applicationDevices, $event['event_name'], 0);

                    if ($event['price'] > 0) {
                        AppslyerService::sendEvent(
                            $subscription->application->appsflyer_dev_key,
                            'af_purchase',
                            $subscription->application->app_id,
                            (!is_null($applicationDevices)) ? $applicationDevices->idfa : null,
                            $subscription->application->bundle_id,
                            $deviceId,
                            (!is_null($applicationDevices)) ? $applicationDevices->appsflyer_unique_id : null,
                            $event['price']);

                        FacebookService::sendEvent($applicationDevices, 'fb_mobile_purchase', $event['price']);

                    }

                }

            }


        }


        \Log::info("TYPE ", [
            "data" => $type
        ]);

        if ( count($diffTransaction) == 0 && $type == Subscription::TYPE_CANCEL ) {

            \Log::info('BLOCK_TRIAL_4');

            $latestRecordSubscriptionHistory = SubscriptionHistory::where('subscription_id', $subscription->id)
                ->orderBy('start_date', 'DESC')->limit(1)->first();



            if ($latestRecordSubscriptionHistory->type != Subscription::TYPE_CANCEL) {
                SaveSubscriptionService::createCancelReceiptHistory($subscription, $latestRecordSubscriptionHistory);

                $event = $this->getEventBySubscription($subscription);

                AppslyerService::sendEvent(
                    $subscription->application->appsflyer_dev_key,
                    $event['event_name'],
                    $subscription->application->app_id,
                    (!is_null($applicationDevices)) ? $applicationDevices->idfa : null,
                    $subscription->application->bundle_id,
                    $deviceId,
                    (!is_null($applicationDevices)) ? $applicationDevices->appsflyer_unique_id : null,
                    0);

                FacebookService::sendEvent($applicationDevices, $event['event_name'], 0);
            }

        }

        $obj = new \stdClass();
        $obj->trial = $trial;
        $obj->exists = $exists;
        $obj->is_premium = 0;

        if ($subscription->isPremium()) {
            $obj->is_premium = 1;
        }

        return $obj;

    }

    public function getResponseAppleReceipt($appId, $latestReceipt, $environment = null)
    {
        $application = $this->applicationService->getApplicationByAppId($appId);


        return $this->receiptService->sendReceipt(
            $latestReceipt,
            (is_null($environment)) ? $application->environment : $environment,
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


    /**
     * @param object $pendingRenewalInfo
     * @param array $latestReceiptInfo
     * @return string
     */
    private function defineType($pendingRenewalInfo, $latestReceiptInfo)
    {

        \Log::info("LATEST RECEIPT INFO SORT", [
            'data' => $latestReceiptInfo
        ]);


        $receiptInfo = $this->sortLatestReceiptInfo($latestReceiptInfo);

        \Log::info("RECEIPT INFO SORT", [
           'data' => $receiptInfo
        ]);

        $endReceiptInfo = end($receiptInfo);

        $countReceiptInfo = count($receiptInfo);


        if (isset($endReceiptInfo->expires_date_ms) && (isset($pendingRenewalInfo->expiration_intent)) && $pendingRenewalInfo->expiration_intent >= 1 ) {
            return Subscription::TYPE_CANCEL;
        }


        if (!isset($endReceiptInfo->expires_date_ms) && (isset($pendingRenewalInfo->expiration_intent)) && $pendingRenewalInfo->expiration_intent == 1) {
            return Subscription::TYPE_LIFETIME;
        }

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

    /**
     * @param $subscription
     * @return array
     */
    public function getEventBySubscription(Subscription $subscription) : array
    {

        \Log::info('Subscription', [
           'data' => $subscription
        ]);


        $price = 0;

        $screen = '';

        $prefix = '';

        $event_screen = '';

        $subscriptionType = $subscription->type;

        $eventDuration = ApplicationProduct::where('application_id', $subscription->application_id)
            ->get();

        \Log::info('EVENT DURATION', [
            'data' => $eventDuration
        ]);


        $filteredEventDuration = $eventDuration->filter(function ($item) use ($subscription) {
            return $subscription->product_id == $item->product_name;
        })->values()->toArray();


        if (count($filteredEventDuration) > 0) {
            throw new \DomainException("Product name not found");
        }

        $applicationProduct = $filteredEventDuration;


        switch ($subscriptionType) {
            case Subscription::TYPE_TRIAL:
                $event =  $prefix . 'start_trial';
                $event_facebook = 'StartTrial';
                $eventName = (empty($subscription->screen_trial) || is_null($subscription->screen_trial)) ? 'none' :  $subscription->screen_trial;
                $event_screen = $prefix . 'start_trial_' . $eventName;
            break;
            case Subscription::TYPE_INITIAL_BUY:
                $event = $prefix . $applicationProduct['event_name'] . '_1';
                $event_facebook = 'fb_mobile_purchase';
                $price = $applicationProduct['price'];
            break;
            case Subscription::TYPE_RENEWAL:
                $count = SubscriptionHistory::where('subscription_id', $subscription->id)
                    ->where('type', Subscription::TYPE_RENEWAL)->count();
                $event = $prefix . $applicationProduct['event_name'] . '_' . $count;
                $event_facebook = 'fb_mobile_purchase';
                $price = $applicationProduct['price'];
            break;
            case Subscription::TYPE_CANCEL:
                $count = SubscriptionHistory::where('subscription_id', $subscription->id)
                    ->where('type', Subscription::TYPE_RENEWAL)->count();
                //$event = $prefix . 'cancel_' . $applicationProduct['event_name'] . '_' . $count;
                $event = 'cancel_' . $applicationProduct['event_name'] . '_' . $count;
                $event_facebook = 'cancel_' . $applicationProduct['event_name'] . '_' . $count;
            break;
            case Subscription::TYPE_LIFETIME:
                $price = $applicationProduct['price'];
                $event = $prefix . $applicationProduct['event_name'];
                $event_facebook = 'fb_mobile_purchase';
                break;
        }

        return [
            'event_name' => $event,
            'price' => $price,
            'event_screen' => $event_screen,
            'event_facebook' => $event_facebook
        ];
    }

    public function checkSubscription()
    {
        $now = Carbon::now()->timestamp;

       // $environment = 'Production';

      //  $environment = 'Sandbox';

        $subscriptions = Subscription::where('end_date', '<', $now * 1000)
            //->where('environment', $environment)
            ->whereIn('type', [Subscription::TYPE_TRIAL, Subscription::TYPE_INITIAL_BUY, Subscription::TYPE_RENEWAL])
            ->get();


        foreach ($subscriptions as $subscription) {

            /** @var Subscription $subscription */

            \Log::info('SUBCRIPTION ID : ' . $subscription->id);

            \Log::info('SUBCRIPTION ENV : ' . $subscription->environment);

            $responseByApple = $this->getResponseAppleReceipt(
                $subscription->application->app_id,
                $subscription->latest_receipt,
                $subscription->environment
            );

            $responseByAppleBody = json_decode($responseByApple['body']);

            \Log::info('RESPONSE BY APPLE BODY', [
               'data' => $responseByApple
            ]);

            $environment = $responseByAppleBody->environment;

            if (isset($responseByAppleBody->latest_receipt_info)) {
                $this->handlerReceipt(
                    $subscription->application->app_id,
                    $subscription->device_id,
                    $subscription->screen_trial,
                    $environment,
                    $responseByAppleBody->latest_receipt,
                    $responseByAppleBody->latest_receipt_info,
                    $responseByAppleBody->pending_renewal_info[0]
                );
            }

        }
    }

    public function isPremium($appId, $deviceId): bool
    {
        /** @var Subscription $subscription */
        $subscription = Subscription::where('application_id', $appId)
            ->where('device_id', $deviceId)
            ->first();

        if (is_null($subscription)) {
            return false;
        }

        return $subscription->isPremium();
    }


}
