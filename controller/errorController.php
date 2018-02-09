<?php

namespace AppController;

use \App;

class errorController {

    public static function error404(){
        $config = new App\Config();
        $twig = $config->initTwig();
        echo $twig->render('error.twig', array(
            'error' => '404',
            'message' => 'Page introuvable'
        ));
    }
}


?>