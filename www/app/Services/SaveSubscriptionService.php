<?php
namespace App\Services;

use App\DTO\SubscriptionDto;
use App\DTO\SubscriptionHistoryDto;
use App\Entities\Subscription;
use App\Entities\SubscriptionHistory;
use Carbon\Carbon;
use Illuminate\Support\Str;


class SaveSubscriptionService
{

    public static function issetSubscription($deviceId, $originalTransactionId)
    {
        return Subscription::where('device_id', $deviceId)
            ->where('original_transaction_id', $originalTransactionId)->first();
    }





    public static function saveSubscription(SubscriptionDto $subscriptionDto)
    {


        $appId = $subscriptionDto->appId;

        /** @var Subscription $subscription */

        $subscription = Subscription::where('device_id', $subscriptionDto->deviceId)
            ->whereHas('application', function($query) use ($appId) {
                $query->where('app_id', $appId);
            })
            ->where('original_transaction_id', $subscriptionDto->originalTransactionId)->first();

        if (is_null($subscription)) {

            $applicationService = new ApplicationService();

            $application = $applicationService->getApplicationByAppId($subscriptionDto->appId);



            $subscription = Subscription::create([
                'id' => Str::uuid(),
                'application_id' => $application->id,
                'device_id' => $subscriptionDto->deviceId,
                'product_id' => $subscriptionDto->productId,
                'environment' => $subscriptionDto->environment,
                'original_transaction_id' => $subscriptionDto->originalTransactionId,
                'type' => $subscriptionDto->type,
                'start_date' => $subscriptionDto->startDate,
                'end_date' => $subscriptionDto->endDate,
                'latest_receipt' => $subscriptionDto->latestReceipt
            ]);
        } else {
            $subscription->update([
                'device_id' => $subscriptionDto->deviceId,
                'product_id' => $subscriptionDto->productId,
                'environment' => $subscriptionDto->environment,
                'original_transaction_id' => $subscriptionDto->originalTransactionId,
                'type' => $subscriptionDto->type,
                'start_date' => $subscriptionDto->startDate,
                'end_date' => $subscriptionDto->endDate,
                'latest_receipt' => $subscriptionDto->latestReceipt
            ]);
        }

        return $subscription;
    }



    public static function saveSubscriptionHistory(SubscriptionHistoryDto $subscriptionHistoryDto)
    {
        $subscriptionHistory = SubscriptionHistory::where('transaction_id', $subscriptionHistoryDto->transactionId)
            ->first();

        if (is_null($subscriptionHistory)) {
            $subscriptionHistory = SubscriptionHistory::create([
                'id' => $subscriptionHistoryDto->id,
                'subscription_id' => $subscriptionHistoryDto->subscriptionId,
                'product_id' => $subscriptionHistoryDto->productId,
                'environment' => $subscriptionHistoryDto->environment,
                'start_date' => $subscriptionHistoryDto->startDate,
                'end_date' => $subscriptionHistoryDto->endDate,
                'type' => $subscriptionHistoryDto->type,
                'transaction_id' => $subscriptionHistoryDto->transactionId,
                'count' => $subscriptionHistoryDto->count
            ]);

            $count = SubscriptionHistory::where('product_id', $subscriptionHistoryDto->productId)
                ->where('subscription_id', $subscriptionHistoryDto->subscriptionId)->count();

            SubscriptionHistory::where('id', $subscriptionHistory->id)
                ->update([
                    'count' => $count
                ]);

        }
    }

    public static function checkReceiptHistory(array $latestReceiptInfo, $subscription)
    {
        $collect = collect($latestReceiptInfo)
            ->keyBy('transaction_id')->toArray();



        $lastCollect = end($collect);

        $arrayTransactionId = array_keys($collect);

        $deviceId = $subscription->device_id;

        $savedAlreadyTransactionId = SubscriptionHistory::whereHas('subscription', function($query) use ($deviceId) {
            $query->where('device_id', $deviceId);
        })->pluck('transaction_id')->toArray();

        $arrayDiffTransactionId = array_diff($arrayTransactionId, $savedAlreadyTransactionId);


        if (count($arrayDiffTransactionId) > 0) {
            foreach ($arrayDiffTransactionId as $transactionId) {
                $subscriptionHistoryDTO = new SubscriptionHistoryDto(
                    $subscription->id,
                    $transactionId,
                    $collect[$transactionId]->product_id,
                    $subscription->environment,
                    $collect[$transactionId]->purchase_date_ms,
                    $collect[$transactionId]->expires_date_ms,
                    ($collect[$transactionId]->is_trial_period == "true") ? Subscription::TYPE_TRIAL : Subscription::TYPE_RENEWAL,
                    0
                );

                SaveSubscriptionService::saveSubscriptionHistory($subscriptionHistoryDTO);
            }

            return collect($latestReceiptInfo)
                ->whereIn('transaction_id', $arrayDiffTransactionId)->toArray();
        }


        return null;


    }


    public static function createCancelReceiptHistory($subscription)
    {

        $latestRecordSubscriptionHistory = SubscriptionHistory::where('subscription_id', $subscription->id)
            ->orderBy('created_at', 'DESC')->limit(1)->first();


        $subscriptionHistoryDTO = new SubscriptionHistoryDto(
            $subscription->id,
            $latestRecordSubscriptionHistory->transaction_id,
            $latestRecordSubscriptionHistory->product_id,
            $subscription->environment,
            Carbon::now()->timestamp * 1000,
            Carbon::now()->timestamp * 1000,
            Subscription::TYPE_CANCEL,
            0
        );

        SaveSubscriptionService::saveSubscriptionHistory($subscriptionHistoryDTO);
    }
}