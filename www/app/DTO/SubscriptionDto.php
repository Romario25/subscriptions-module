<?php
namespace App\DTO;


class SubscriptionDto
{
    public $appId;

    public $deviceId;

    public $screenTrial;

    public $originalTransactionId;

    public $productId;

    public $environment;

    public $type;

    public $startDate;

    public $endDate;

    public $latestReceipt;

    /**
     * SubscriptionDto constructor.
     * @param $appId
     * @param $deviceId
     * @param $screenTrial
     * @param $originalTransactionId
     * @param $productId
     * @param $environment
     * @param $type
     * @param $startDate
     * @param $endDate
     * @param $latestReceipt
     */
    public function __construct($appId, $deviceId, $screenTrial, $originalTransactionId, $productId, $environment, $type, $startDate, $endDate, $latestReceipt)
    {
        $this->appId = $appId;
        $this->deviceId = $deviceId;

        $this->originalTransactionId = $originalTransactionId;
        $this->productId = $productId;
        $this->environment = $environment;
        $this->type = $type;
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->latestReceipt = $latestReceipt;
        $this->screenTrial = $screenTrial;
    }


}