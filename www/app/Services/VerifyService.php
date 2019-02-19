<?php
namespace App\Services;


use GuzzleHttp\RequestOptions;
use HttpException;

class VerifyService
{


    private $config;

    public function __construct()
    {
        $this->config = config('subscriptions');
    }


    private function getAppleUrl() : string
    {
        if ($this->config['environment_sandbox']) {
            return 'https://sandbox.itunes.apple.com/verifyReceipt';
        }

        return 'https://buy.itunes.apple.com/verifyReceipt';
    }


    public function verifyReceipt($receipt)
    {

        if ($receipt['status'] != 200) {
            throw new \Exception('ERROR RESPONSE TO APPLE');
        }

        $responseBody = json_decode($receipt['body']);

        $status = $responseBody->status;

        if ($status != 0) {
            throw new \Exception('ERROR RESPONSE TO APPLE STATUS');
        }

        return $responseBody;

    }

    private function sendReceipt($receiptData)
    {
        $client = new \GuzzleHttp\Client();

        $body = [
            'receipt-data' => $receiptData,
            'exclude-old-transactions' => false,
            'password' => $this->config['password']

        ];

        $response = $client->post($this->getAppleUrl(),
            [RequestOptions::JSON => $body]
        );

        return [
            'status' => $response->getStatusCode(),
            'body' => $response->getBody()->getContents()
        ];
    }

    private function sortInApp($inApp) : array
    {
        $collect = collect($inApp);

        return $collect->sortBy('purchase_date_ms')->toArray();
    }


}