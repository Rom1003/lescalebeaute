<?php

namespace AppController;

use \App\Config;
use \App\Database;
use \App\Tables\Categorie;
use App\Tables\Produit;
use App\Tables\Slider;
use \Illuminate\Database\Eloquent\Model;

class indexController{

    public static function indexAction(){
        $config = new Config();
        $twig = $config->initTwig();

        $menu = Categorie::getMenu();
        $slider = Slider::getSlides();
        $produits = Produit::with('image')->with('gamme')->where('actif', 1)->inRandomOrder()->limit(8)->get();
        $massages = Categorie::with('image')->where('niveau', 1)->inRandomOrder()->limit(4)->get();

        echo $twig->render('index.twig', array(
            'menu' => $menu,
            'slider' => $slider,
            'produits' => $produits,
            'massages' => $massages
        ));
    }
}


?>