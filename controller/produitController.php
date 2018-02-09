<?php

namespace AppController;

use \App;

class produitController {

    public static function indexAction(){
        $config = new App\Config();
        $twig = $config->initTwig();
        echo $twig->render('produit.twig', array(

        ));
    }
}


?>