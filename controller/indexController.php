<?php

namespace AppController;

use \App\Config;
use \App\Database;
use \App\Tables\Categorie;
use \Illuminate\Database\Eloquent\Model;

class indexController{

    public static function indexAction(){
        $config = new Config();
        $twig = $config->initTwig();

        $menu = Categorie::getMenu();


        echo $twig->render('index.twig', array(
            'menu' => $menu
        ));
    }
}


?>