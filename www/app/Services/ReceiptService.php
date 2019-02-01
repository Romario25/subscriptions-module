<?php
namespace App\Services;


use App\Entities\Application;
use GuzzleHttp\RequestOptions;

class ReceiptService
{

    private $config;

    public function __construct()
    {
        $this->config = config('subscriptions');
    }




    private function getAppleUrl($environment) : string
    {
        if ($environment == Application::ENV_SANDBOX) {
            return 'https://sandbox.itunes.apple.com/verifyReceipt';
        }

        return 'https://buy.itunes.apple.com/verifyReceipt';
    }


    public function sendReceipt($receiptData, $environment, $shareSecret)
    {
        $client = new \GuzzleHttp\Client();

        $body = [
            'receipt-data' => $receiptData,
            'exlude-old-transactions' => true,
            'password' => $shareSecret

        ];

        $response = $client->post($this->getAppleUrl($environment),
            [RequestOptions::JSON => $body]
        );

        return [
            'status' => $response->getStatusCode(),
            'body' => $response->getBody()->getContents()
        ];
    }
}