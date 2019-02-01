<?php
namespace App\DTO;


use Illuminate\Support\Str;



/**
 * Class SubscriptionHistoryDto
 * @package Romario25\Subscriptions\DTO
 *
 */
class SubscriptionHistoryDto
{

    public $id;

    public $subscriptionId;

    public $transactionId;

    public $productId;

    public $environment;

    public $startDate;

    public $endDate;

    public $type;

    public $count;

    /**
     * SubscriptionHistoryDto constructor.
     * @param $id
     * @param $subscriptionId
     * @param $transactionId
     * @param $productId
     * @param $environment
     * @param $startDate
     * @param $endDate
     * @param $type
     */
    public function __construct($subscriptionId, $transactionId, $productId, $environment, $startDate, $endDate, $type, $count)
    {
        $this->id = Str::uuid();
        $this->subscriptionId = $subscriptionId;
        $this->transactionId = $transactionId;
        $this->productId = $productId;
        $this->environment = $environment;
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->type = $type;
        $this->count = $count;

    }


}

