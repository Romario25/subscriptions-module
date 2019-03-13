<?php
namespace App\Entities;

use Illuminate\Database\Eloquent\Model;

/**
 * Class Subscription
 * @package Romario25\Subscriptions\Entities
 *
 * @property string $id
 * @property integer $application_id
 * @property integer $user_id
 * @property string $device_id
 * @property string $product_id
 * @property string $environment
 * @property string $original_transaction_id
 * @property string $type
 * @property integer $start_date
 * @property integer $end_date
 * @property string $latest_receipt
 * @property string $screen_trial
 */
class Subscription extends Model
{

    const TYPE_TRIAL = 'TRIAL';
    const TYPE_INITIAL_BUY = 'INITIAL_BUY';
    const TYPE_RENEWAL = 'RENEWAL';
    const TYPE_CANCEL = 'CANCEL';
    const TYPE_LIFETIME = "LIFETIME";




    protected $primaryKey = 'id';

    public $incrementing = false;

    public $guarded = [];

    protected $table = 'subscriptions';


    public function application()
    {
        return $this->belongsTo(Application::class, 'application_id', 'id');
    }

    public function isPremium()
    {
        $arr = [self::TYPE_RENEWAL, self::TYPE_LIFETIME, self::TYPE_INITIAL_BUY, self::TYPE_TRIAL];

        return in_array($this->type, $arr);
    }
}
