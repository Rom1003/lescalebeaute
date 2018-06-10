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

    public function categorie()
    {
        return $this->hasMany('App\Tables\Categorie');
    }

    public function categorieSlide()
    {
        return $this->hasMany('App\Tables\Categorie', 'slide_image_id');
    }

    public function slider()
    {
        return $this->hasMany('App\Tables\Slider');
    }

}