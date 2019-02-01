<?php
namespace App\Services;


use GuzzleHttp\RequestOptions;

class ReceiptService
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


    public function sendReceipt($receiptData)
    {
        $client = new \GuzzleHttp\Client();

        $body = [
            'receipt-data' => $receiptData,
            'exlude-old-transactions' => $this->config['exlude-old-transactions'],
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
}