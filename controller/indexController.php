<?php

namespace AppController;

use \App;

class indexController {

    public static function indexAction(){
        $config = new App\Config();
        $twig = $config->initTwig();
        echo $twig->render('index.twig', array(

        ));
    }
}


?>