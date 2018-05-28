<?php

namespace App\Tables;

use App\Database;
use \Illuminate\Database\Eloquent\Model;

class Image extends Model
{
    protected $table = 'image';

    public function serviceImage()
    {
        return $this->hasMany('App\Tables\ServiceImage');
    }


}