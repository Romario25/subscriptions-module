<?php
namespace Romario25\Subscriptions\Entities;

/**
 * Class SubscriptionHistory
 * @package Romario25\Subscriptions\Entities
 *
 * @property string $id
 */
class SubscriptionHistory extends \Eloquent
{
    protected $primaryKey = 'id';

    public $incrementing = false;

    public $guarded = [];

    protected $table = 'subscriptions_history';

    public function subscription()
    {
        return $this->belongsTo(Subscription::class, 'subscription_id', 'id');
    }
}