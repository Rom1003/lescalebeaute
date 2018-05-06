<?php

namespace App\Tables;

use App\Database;
use \Illuminate\Database\Eloquent\Model;

class Vocabulaire extends Model
{
    protected $table = 'vocabulaire';

    public static function getLibelle($id){
        try {
            $vocabulaire = self::find($id)->toArray();
            return $vocabulaire['libelle'];
        } catch (\PDOException $exception){
            return false;
        }
    }

    public static function getValeur($id){
        try {
            $vocabulaire = self::find($id)->toArray();
            return $vocabulaire['valeur'];
        } catch (\PDOException $exception){
            return false;
        }
    }

}