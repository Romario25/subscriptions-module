<?php
namespace App\Services;


use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;

class AppslyerService
{

    public static function sendEvent($devToken, $eventName, $appId, $idfa, $bundleId, $deviceId, $price, $currency = 'USD')
    {

        $config = config('subscriptions');

        $body = [
            'appsflyer_id' => $appId,
            'eventName' => $eventName,
            'af_events_api' => "true",
            'bundle_id' => $bundleId,
            'eventCurrency' => $currency,
            'customer_user_id' => $deviceId,
            'device_id' => $deviceId
        ];

        if (!is_null($idfa)) {
            $body['idfa'] = $idfa;
        }


        $eventValue = [];

        if ($price > 0) {
            $eventValue = [
                'af_revenue' => (string) $price
            ];
        }


        $body['eventValue'] = json_encode($eventValue);


        $client = new Client([
            'timeout'  => 30.0,
        ]);

        try {

            \Log::info('SEND EVENT BODY ', [
                'data' => $body
            ]);


//            $response = $client->request('POST', 'https://api2.appsflyer.com/inappevent/id' . $appId, [
//                RequestOptions::JSON => $body,
//                'headers' => [
//                    'authentication' => $devToken,
//                ]
//            ]);
//
//            $body = $response->getBody();
//
//
//            $phrase = $response->getReasonPhrase();
//
//            if ($phrase != 'OK') {
//                \Log::error('Bad response from apps flyer analytics:'.PHP_EOL.$response->getBody());
//                return;
//            }

        } catch (\Exception $e) {
            \Log::error('SEND EVENT APPSFLYER : :'. $e->getMessage());
            return;
        }
    }

}