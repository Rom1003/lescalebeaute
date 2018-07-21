<?php

namespace App\Tables;

use App\Database;
use \Illuminate\Database\Eloquent\Model;

class Epilation extends Model
{

    CONST FEMME = 0;
    CONST HOMME = 1;

    CONST TYPES = array(
        self::FEMME => 'Femme',
        self::HOMME => 'Homme'
    );

    protected $table = 'epilation';
    public $timestamps = false;

    public static function getListByType(){
        $epilations = self::orderBy('type')->orderBy('libelle')->get();
        if ($epilations->isEmpty()){
            return array();
        } else {
            $liste = array();
            foreach ($epilations as $epilation){
                $liste[$epilation->type][] = $epilation;
            }
            return $liste;
        }
    }

}