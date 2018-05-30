<?php

namespace App\Tables;

use App\Database;
use \Illuminate\Database\Eloquent\Model;

class Slider extends Model
{
    protected $table = 'slider';
    public $timestamps = false;

    public function image()
    {
        return $this->belongsTo('App\Tables\Image');
    }

    public static function getSlide($ordre){
        return self::where('ordre', $ordre)->where('actif', 1)->get()->first();
    }

    public static function getSlides($actif = true){
        if ($actif === true || $actif == '1'){
            $actif = true;
        } else {
            $actif = false;
        }
        return self::with('image')->where('actif', $actif)->orderBy('ordre')->get();
    }

}