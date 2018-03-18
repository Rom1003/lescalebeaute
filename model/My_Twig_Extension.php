<?php

namespace App;

use \App;
use \Twig_Extension;

class My_Twig_Extension extends Twig_Extension {

    public function getFunctions()
    {
        $functions = array();

        $functions[] = new \Twig_Function('titre', array($this, 'titre'));
        $functions[] = new \Twig_Function('url', array($this, 'url'));
        $functions[] = new \Twig_Function('image', array($this, 'image'));

        return $functions;
    }


    public function titre(){
        return 'test';
    }

    public function url($name) {
        $config = new App\Config();
        if (!$config)return false;
        $url = $config->getRoutes($name);
        if (!$url)return false;
        return $url;
    }

    public function image($name) {
        $config = new App\Config();
        if (!$config)return false;
        $url = $config->getGlobal('IMG_ROOT');
        if (!$url)return false;
        return $url.$name;
    }

}