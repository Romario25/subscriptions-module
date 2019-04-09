<?php
namespace App\Entities;


use Illuminate\Database\Eloquent\Model;

class ApplicationProduct extends Model
{
    protected $primaryKey = null;

    public $incrementing = false;

    public $guarded = [];

    protected $table = 'application_products';

    public function application()
    {
        return $this->belongsTo(Application::class, 'application_id', 'id');
    }
}