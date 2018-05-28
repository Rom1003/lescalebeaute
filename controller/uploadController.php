<?php

namespace AppController;

use \App\Config;
use \App\Database;
use \App\Tables\Categorie;
use \Illuminate\Database\Eloquent\Model;

class uploadController{

    public static function removeAction(){
        echo 'test';
        if (!isset($_POST['name']) || empty($_POST['name']))exit(__LINE__);
        $name = $_POST['name'];
        if (preg_match('/([\D|\d]+)\[([\d])\]/', $name, $matches) == 1){
            //Il s'agit d'un upload multiple on récupère l'index
            $name = $matches[1];
            $index = $matches[2];
        }

        if (!isset($_FILES[$name]) || empty($_FILES[$name]))exit(__LINE__);
        if (isset($index)){
            if (!isset($_FILES[$name][$index]['tmp_name']))exit(__LINE__);
            unset($_FILES[$name]['name'][$index]);
            unset($_FILES[$name]['type'][$index]);
            unset($_FILES[$name]['tmp_name'][$index]);
            unset($_FILES[$name]['error'][$index]);
            unset($_FILES[$name]['size'][$index]);
            $path = $_FILES[$name][$index]['tmp_name'];
        } else {
            if (!isset($_FILES[$name]['tmp_name']))exit(__LINE__);
            unset($_FILES[$name]);
            $path = $_FILES[$name]['tmp_name'];
        }
        @unlink($path);
        exit('OK');
    }
}


?>