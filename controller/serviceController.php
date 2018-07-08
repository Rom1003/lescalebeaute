<?php

namespace AppController;

use \App\Config;
use \App\Database;
use \App\Tables\Categorie;
use \App\Tables\Service;
use \App\Tables\Produit;
use \Illuminate\Database\Eloquent\Model;

class serviceController{

    public static function detailAction($id){
        $config = new Config();
        $twig = $config->initTwig();

        if (empty($id)){
            \AppController\errorController::error500();
            exit;
        }

        $menu = Categorie::getMenu();
        $service = Service::detail($id);
        if (!$service || empty($service)){
            \AppController\errorController::error404();
            exit;
        }
        $produits = Produit::with('image')->with('gamme')->where('actif', 1)->inRandomOrder()->limit(8)->get();

        echo $twig->render('service.twig', array(
            'menu' => $menu,
            'service' => $service,
            'produits' => $produits
        ));
    }
}


?>