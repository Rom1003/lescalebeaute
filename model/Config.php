<?php



namespace App;

//require_once 'Extension.php';

use \Twig_Loader_Filesystem;
use \Twig_Environment;
use \Twig_Extension_Debug;

class Config {

    private $global = array();

    public function __construct() {
        require './config/global.php';

        if (!isset($global)){
            return false;
        }
        $this->setGlobal($global);
        return true;
    }

    public function getGlobal($index = ''){
        if ($index !== ''){
            if (isset($this->global[$index]))return $this->global[$index];
        } else {
            return $this->global;
        }
        return false;
    }

    private function setGlobal($vars){
        if (is_array($vars)){
            $this->global = array_merge($this->global, $vars);
            return true;
        } else {
            return false;
        }
    }


    public function initTwig(){
        $global = $this->global;
// Charge les templates
        $loader = new Twig_Loader_Filesystem(array($global['FILE_ROOT'].'view', $global['FILE_ROOT'].'templates'));

        $twig = new Twig_Environment($loader, array(
            'cache' => false,
            'debug' => true

        ));

//Activation du debug()
        $twig->addExtension(new Twig_Extension_Debug());
        $twig->addExtension(new My_Twig_Extension());

//Ajout des fonctions custom
//        include_once $global['FILE_ROOT'].'model/Twig/Functions.php';

//Ajout des variables globales
        $twig->addGlobal('glb', $global);
        return $twig;
    }

}