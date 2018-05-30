<?php

namespace AppController;

use \App\Config;
use \App\Database;
use \App\Tables\Categorie;
use App\Tables\Slider;
use \Illuminate\Database\Eloquent\Model;

class indexController{

    public static function indexAction(){
        $config = new Config();
        $twig = $config->initTwig();

        $menu = Categorie::getMenu();
        $slider = Slider::getSlides();

        echo $twig->render('index.twig', array(
            'menu' => $menu,
            'slider' => $slider
        ));
    }
}


?>