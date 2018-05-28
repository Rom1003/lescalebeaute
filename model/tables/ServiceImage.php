<?php

namespace App\Tables;

use App\Database;
use \Illuminate\Database\Eloquent\Model;

class ServiceImage extends Model
{
    protected $table = 'service_image';
    public $timestamps = false;

    public function image()
    {
        return $this->belongsTo('App\Tables\Image');
    }

    public function service()
    {
        return $this->belongsTo('App\Tables\Service');
    }

}