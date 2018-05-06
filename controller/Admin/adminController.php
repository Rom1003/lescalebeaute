<?php

namespace AppController\Admin;

use \App\Config;
use \App\Database;
use \App\Tables\Categorie;
use \Illuminate\Database\Eloquent\Model;

class adminController{

    public static function indexAction(){
        $config = new Config();
        $twig = $config->initTwig();

        echo $twig->render('admin.twig', array(

        ));
    }
}


?>