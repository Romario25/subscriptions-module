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



        $endLatestReceiptInfo = end($latestReceiptInfo);

        $type = $this->defineType($pendingRenewalInfo, $latestReceiptInfo);



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

        $subscription = SaveSubscriptionService::saveSubscription($subscriptionDTO);

        $diffTransaction = SaveSubscriptionService::checkReceiptHistory($latestReceiptInfo, $subscription);


        /** @var ApplicationDevice $applicationDevices */
        $applicationDevices = $this->applicationService->getApplicationDeviceInfo($subscription->application->id, $subscription->device_id);

        $startDate = Carbon::now()->startOfDay()->timestamp;




        if (count($diffTransaction) == 1  && $subscription->start_date < $startDate * 1000) {

            $event = $this->getEventBySubscription($subscription);

            AppslyerService::sendEvent(
                $subscription->application->appsflyer_dev_key,
                $event['event_name'],
                $subscription->application->app_id,
                (!is_null($applicationDevices)) ? $applicationDevices->idfa : null,
                $subscription->application->bundle_id,
                $deviceId,
                (!is_null($applicationDevices)) ? $applicationDevices->appsflyer_unique_id : null,
                $event['price']
            );

            FacebookService::sendEvent($applicationDevices, $event['event_name'], $event['event_name']);

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

                FacebookService::sendEvent($applicationDevices, $event['event_name'], $event['event_name']);
            }

            if (!empty($event['event_screen'])) {
                AppslyerService::sendEvent(
                    $subscription->application->appsflyer_dev_key,
                    $event['event_screen'],
                    $subscription->application->app_id,
                    (!is_null($applicationDevices)) ? $applicationDevices->idfa : null,
                    $subscription->application->bundle_id,
                    $deviceId,
                    (!is_null($applicationDevices)) ? $applicationDevices->appsflyer_unique_id : null,
                    $event['price']);

                FacebookService::sendEvent($applicationDevices, $event['event_name'], $event['event_name']);
            }
        } else {
            if (count($diffTransaction) > 0) {

                $endDiffTransaction = end($diffTransaction);

                \Log::info('END DIFF TRANSACTION', ['data' => $endDiffTransaction]);


                if (isset($endDiffTransaction->start_date) && $endDiffTransaction->start_date > $startDate * 1000) {
                    $transactionHistory = SubscriptionHistory::where('transaction_id', $endDiffTransaction->transaction_id)
                        ->first();

                    $event = $this->getEventBySubscription($transactionHistory);

                    AppslyerService::sendEvent(
                        $subscription->application->appsflyer_dev_key,
                        $event['event_name'],
                        $subscription->application->app_id,
                        (!is_null($applicationDevices)) ? $applicationDevices->idfa : null,
                        $subscription->application->bundle_id,
                        $deviceId,
                        (!is_null($applicationDevices)) ? $applicationDevices->appsflyer_unique_id : null,
                        $event['price']);

                    FacebookService::sendEvent($applicationDevices, $event['event_name'], $event['event_name']);

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

                        FacebookService::sendEvent($applicationDevices, $event['event_name'], $event['event_name']);
                    }

                    if (!empty($event['event_screen'])) {
                        AppslyerService::sendEvent(
                            $subscription->application->appsflyer_dev_key,
                            $event['event_screen'],
                            $subscription->application->app_id,
                            (!is_null($applicationDevices)) ? $applicationDevices->idfa : null,
                            $subscription->application->bundle_id,
                            $deviceId,
                            (!is_null($applicationDevices)) ? $applicationDevices->appsflyer_unique_id : null,
                            $event['price']);

                        FacebookService::sendEvent($applicationDevices, $event['event_name'], $event['event_name']);
                    }
                }

            }


        }


        \Log::info("TYPE ", [
            "data" => $type
        ]);

        if ( count($diffTransaction) == 0 && $type == Subscription::TYPE_CANCEL ) {

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
                    $event['price']);
            }

        }

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


    /**
     * @param object $pendingRenewalInfo
     * @param array $latestReceiptInfo
     * @return string
     */
    private function defineType($pendingRenewalInfo, $latestReceiptInfo)
    {

        $receiptInfo = $this->sortLatestReceiptInfo($latestReceiptInfo);

        $endReceiptInfo = end($receiptInfo);

        $countReceiptInfo = count($receiptInfo);


        if (isset($endReceiptInfo->expires_date_ms) && (isset($pendingRenewalInfo->expiration_intent)) && $pendingRenewalInfo->expiration_intent == 1 ) {
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
    public function getEventBySubscription($subscription) : array
    {

        $eventDuration = ApplicationProduct::where('application_id', $subscription->application_id)
            ->get()->keyBy('product_name')->toArray();


        $subscriptionType = $subscription->type;


        $prefix = '';

        $event_screen = '';

        $keyEventDuration = array_keys($eventDuration);

        $keySearch = array_search($subscription->product_id, array_keys($eventDuration));

        $applicationProduct = $eventDuration[$keyEventDuration[$keySearch]];

        $price = 0;

        $screen = '';

        switch ($subscriptionType) {
            case Subscription::TYPE_TRIAL:
                $event =  $prefix . 'start_trial';
                $eventName = (empty($subscription->screen_trial) || is_null($subscription->screen_trial)) ? 'none' :  $subscription->screen_trial;
                $event_screen = $prefix . 'start_trial_' . $eventName;
            break;
            case Subscription::TYPE_INITIAL_BUY:
                $event = $prefix . $applicationProduct['event_name'] . '_1';
                $price = $applicationProduct['price'];
            break;
            case Subscription::TYPE_RENEWAL:
                $count = SubscriptionHistory::where('subscription_id', $subscription->id)
                    ->where('type', Subscription::TYPE_RENEWAL)->count();
                $event = $prefix . $applicationProduct['event_name'] . '_' . $count;

                $price = $applicationProduct['price'];
            break;
            case Subscription::TYPE_CANCEL:
                $count = SubscriptionHistory::where('subscription_id', $subscription->id)
                    ->where('type', Subscription::TYPE_RENEWAL)->count();
                $event = $prefix . 'cancel_' . $applicationProduct['event_name'] . '_' . $count;
            break;
            case Subscription::TYPE_LIFETIME:
                $price = $applicationProduct['price'];
                $event = $prefix . $applicationProduct['event_name'];
                break;
        }

        return [
            'event_name' => $event,
            'price' => $price,
            'event_screen' => $event_screen
        ];
    }

    public function checkSubscription()
    {
        $now = Carbon::now()->timestamp;

        //$environment = 'Production';

        $environment = 'Sandbox';

        $subscriptions = Subscription::where('end_date', '<', $now * 1000)
            ->where('environment', $environment)
            ->whereIn('type', [Subscription::TYPE_TRIAL, Subscription::TYPE_INITIAL_BUY, Subscription::TYPE_RENEWAL])
            ->get();


        foreach ($subscriptions as $subscription) {

            /** @var Subscription $subscription */

            \Log::info('SUBCRIPTION ID : ' . $subscription->id);

            $responseByApple = $this->getResponseAppleReceipt(
                $subscription->application->app_id,
                $subscription->latest_receipt
            );

            $responseByAppleBody = json_decode($responseByApple['body']);



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


}