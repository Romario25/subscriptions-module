<?php
namespace App\Entities;


use Illuminate\Database\Eloquent\Model;


/**
 * Class Application
 * @package App\Entities
 *
 * @property string appsflyer_dev_key
 * @property integer send_stat_appsflyer
 * @property integer send_stat_facebook
 */
class Application extends Model
{
    public $guarded = [];

    protected $table = 'applications';

    public function setAppsflyerDevKey($key)
    {
        $this->appsflyer_dev_key = $key;
        $this->save();
    }

    public function onSendStatAppsflyer()
    {
        $this->send_stat_appsflyer = 1;
        $this->save();
    }

    public function offSendStatAppsflyer()
    {
        $this->send_stat_appsflyer = 0;
        $this->save();
    }

    public function onSendStatFacebook()
    {
        $this->send_stat_facebook = 1;
        $this->save();
    }

    public function offSendStatFacebook()
    {
        $this->send_stat_facebook = 0;
        $this->save();
    }
}