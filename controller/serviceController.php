<?php

namespace AppController;

use \App\Config;
use \App\Database;
use \App\Tables\Categorie;
use \App\Tables\Service;
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
        var_dump($service->toArray());
        if (!$service || empty($service)){
            \AppController\errorController::error404();
            exit;
        }

        echo $twig->render('service.twig', array(
            'menu' => $menu,
            'service' => $service
        ));
    }
}


?>