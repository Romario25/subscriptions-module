<?php
namespace App\Entities;


use Illuminate\Database\Eloquent\Model;

/**
 * Class ApplicationDevice
 * @package App\Entities
 *
 * @property $id
 * @property $application_id
 * @property $created_at
 * @property $updated_at
 * @property $device_id
 * @property $idfa
 * @property $appsflyer_unique_id
 * @property $bundle_version
 * @property $extinfo
 * @property $bundle_short_version
 * @property $application_tracking_enabled
 * @property $advertiser_tracking_enabled
 * @property $attribution
 *
 * @property Application $application
 *
 */
class ApplicationDevice extends Model
{
    public $guarded = [];

    protected $table = 'application_devices';

    protected $casts = [
        'extinfo' => 'array'
    ];

    public function application()
    {
        return $this->belongsTo(Application::class, 'application_id', 'id');
    }
}