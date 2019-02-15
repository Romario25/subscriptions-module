<?php
namespace App\Services;


use App\Entities\ApplicationDevice;
use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;

class FacebookService
{
    /**
     * @param ApplicationDevice $applicationDevice
     * @param $eventName
     * @param null $eventValue
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public static function sendEvent(ApplicationDevice $applicationDevice, $eventName, $eventValue = null)
    {

        $body = new \stdClass();

        $body->event = 'CUSTOM_APP_EVENTS';
        $body->custom_events = [];

        $customEventObj = new \stdClass();

        $customEventObj->_eventName = $eventName;

        if (!is_null($eventValue)) {
            $customEventObj->_valueToSum = $eventValue;
            $customEventObj->fb_currency = 'USD';
        }

        array_push($body->custom_events, $customEventObj);


        $body->udid = $applicationDevice->id;
        $body->bundle_version = $applicationDevice->bundle_version;
        $body->advertiser_id = $applicationDevice->device_id;
        $body->advertiser_tracking_enabled = $applicationDevice->advertiser_tracking_enabled;
        $body->bundle_id = $applicationDevice->application->bundle_id;

        $extInfo = [];

        array_push($extInfo, $applicationDevice->extinfo['ext_info_ver']);
        array_push($extInfo, $applicationDevice->extinfo['app_pkg_name']);
        array_push($extInfo, $applicationDevice->extinfo['pkg_ver_code']);
        array_push($extInfo, $applicationDevice->extinfo['pkg_info_ver_name']);
        array_push($extInfo, $applicationDevice->extinfo['os_ver']);
        array_push($extInfo, $applicationDevice->extinfo['dev_model_name']);
        array_push($extInfo, $applicationDevice->extinfo['locale']);
        array_push($extInfo, $applicationDevice->extinfo['dev_timezone_abv']);
        array_push($extInfo, $applicationDevice->extinfo['carrier_name']);
        array_push($extInfo, $applicationDevice->extinfo['screen_width']);
        array_push($extInfo, $applicationDevice->extinfo['screen_height']);
        array_push($extInfo, $applicationDevice->extinfo['screen_density']);
        array_push($extInfo, $applicationDevice->extinfo['cpu_cores']);
        array_push($extInfo, $applicationDevice->extinfo['ext_storage_size']);
        array_push($extInfo, $applicationDevice->extinfo['avl_storage_size']);
        array_push($extInfo, $applicationDevice->extinfo['dev_timezone']);

        $body->extinfo = $extInfo;
        $body->bundle_short_version = $applicationDevice->bundle_short_version;
        $body->application_tracking_enabled = $applicationDevice->application_tracking_enabled;
        $body->attribution = $applicationDevice->attribution;


        try {

            $client = new Client([
                'timeout'  => 30.0,
            ]);

            $facebookAppId = $applicationDevice->application->facebook_app_id;

            \Log::info('SEND FACEBOOK EVENT BODY ', [
                'data' => $body
            ]);


            $response = $client->request('POST', 'https://graph.facebook.com/' . $facebookAppId . '/activities', [
                RequestOptions::JSON => $body,

            ]);

            $body = $response->getBody();

            \Log::info('FACEBOOK EVENT RESPONSE', [
                'data' => $body->getContents()
            ]);

            $phrase = $response->getReasonPhrase();

            if ($phrase != 'OK') {
                \Log::error('Bad response from apps flyer analytics:'.PHP_EOL.$response->getBody());
                return;
            }

        } catch (Guzzle $e) {
            \Log::error('SEND EVENT APPSFLYER : :'. $e->getMessage());
            return;
        }
    }
}