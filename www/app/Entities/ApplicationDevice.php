<?php
namespace App\Entities;


use Illuminate\Database\Eloquent\Model;

class ApplicationDevice extends Model
{
    public $guarded = [];

    protected $table = 'application_devices';
}