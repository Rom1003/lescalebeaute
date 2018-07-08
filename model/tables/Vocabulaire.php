<?php

namespace App\Tables;

use App\Database;
use \Illuminate\Database\Eloquent\Model;

class Vocabulaire extends Model
{

    CONST TELEPHONE = 1;
    CONST ADRESSE = 2;
    CONST TEXTE_MASSAGES = 3;
    CONST TEXTE_PRODUITS = 4;
    CONST HORAIRES = 5;

    CONST HORAIRES_JOURS = [
        0 => 'lundi',
        1 => 'mardi',
        2 => 'mercredi',
        3 => 'jeudi',
        4 => 'vendredi',
        5 => 'samedi',
        6 => 'dimanche',
    ];

    protected $table = 'vocabulaire';
    public $timestamps = false;

    public static function getLibelle($id){
        try {
            $vocabulaire = self::find($id)->toArray();
            return $vocabulaire['libelle'];
        } catch (\PDOException $exception){
            return false;
        }
    }

    public static function getValeur($id){
        if (empty($id))return false;
        try {
            $vocabulaire = self::find($id)->toArray();
            return $vocabulaire['valeur'];
        } catch (\PDOException $exception){
            return false;
        }
    }

}