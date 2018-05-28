<?php

namespace App\Tables;

use App\Database;
use \Illuminate\Database\Eloquent\Model;

class ServicePrix extends Model
{
    protected $table = 'service_prix';
    public $timestamps = false;

    public function service()
    {
        return $this->belongsTo('App\Tables\Service');
    }

}