<?php
namespace App\Entities;

use Illuminate\Database\Eloquent\Model;

/**
 * Class Subscription
 * @package Romario25\Subscriptions\Entities
 *
 * @property string $id
 * @property integer $user_id
 * @property string $device_id
 * @property string $product_id
 * @property string $environment
 * @property string $original_transaction_id
 * @property string $type
 * @property integer $start_date
 * @property integer $end_date
 * @property string $latest_receipt
 */
class Subscription extends Model
{

    const TYPE_TRIAL = 'TRIAL';
    const TYPE_INITIAL_BUY = 'INITIAL_BUY';
    const TYPE_RENEWAL = 'RENEWAL';
    const TYPE_CANCEL = 'CANCEL';




    protected $primaryKey = 'id';

    public $incrementing = false;

    public $guarded = [];

    protected $table = 'subscriptions';
}