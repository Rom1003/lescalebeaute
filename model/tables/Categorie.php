<?php

namespace App\Tables;

use App\Database;
use \Illuminate\Database\Eloquent\Model;

class Categorie extends Model
{
    protected $table = 'categorie';


    public static function getMenu()
    {

        try {
            $categories = self::orderBy('niveau')->orderBy('categorie_id')->orderBy('ordre')->get()->toArray();
        } catch (\PDOException $exception){
            \AppController\errorController::error500();
            exit;
        }


//        var_dump($categories);

        //Création du tableau pour le menu
        $menu = array();
        $sous_menu = array();
        foreach ($categories as $category) {
            switch ($category['niveau']) {
                case 0:
                    $menu[$category['id']] = $category;
                    break;
                case 1:
                    $sous_menu[$category['id']] = $category;
                    break;
                case 2:
                    $sous_menu[$category['categorie_id']]['sub'][$category['ordre']] = $category;
            }
        }
        foreach ($sous_menu as $sm) {
            $menu[$sm['categorie_id']]['sub'][$sm['ordre']] = $sm;
        }

        return $menu;
    }



}