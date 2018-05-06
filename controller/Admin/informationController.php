<?php

namespace AppController\Admin;

use \App\Config;
use \App\Database;
use \App\Tables\Categorie;
use App\Tables\Vocabulaire;
use \Illuminate\Database\Eloquent\Model;

class informationController{

    public static function indexAction(){
        $config = new Config();
        $twig = $config->initTwig();

        try {
            $vocabulaire = Vocabulaire::get()->toArray();
        } catch (\PDOException $exception){
            \AppController\errorController::error500();
            exit;
        }

        echo $twig->render('Admin/information.twig', array(
            'vocabulaire' => $vocabulaire
        ));
    }
}


?>