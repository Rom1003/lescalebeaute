<?php

namespace App;

use \App\Config;
use \Twig_Extension;
use App\Tables\Vocabulaire;
use \Illuminate\Database\Eloquent\Model;

class My_Twig_Extension extends Twig_Extension {

    public function getFunctions()
    {
        $functions = array();

        $functions[] = new \Twig_Function('titre', array($this, 'titre'));
        $functions[] = new \Twig_Function('url', array($this, 'url'));
        $functions[] = new \Twig_Function('image', array($this, 'image'));
        $functions[] = new \Twig_Function('getVocabulaire', array($this, 'getVocabulaire'));

        return $functions;
    }

    public function getFilters()
    {
        $filters = array();

        $filters[] = new \Twig_Filter('phone', array($this, 'phone'));

        return $filters;

    }


    public function url($name) {
        $config = new Config();
        if (!$config)return false;
        $url = $config->getRoutes($name);
        if (!$url)return false;
        return $url;
    }

    public function image($name) {
        $config = new Config();
        if (!$config)return false;
        $url = $config->getGlobal('IMG_ROOT');
        if (!$url)return false;
        return $url.$name;
    }

    public function getVocabulaire($id, $vocabulaires = array()){
        if (empty($vocabulaires)){
            $valeur = Vocabulaire::getValeur($id);
            if ($valeur === false){
                return '';
            } else {
                return $valeur;
            }
        } else {
            foreach ($vocabulaires as $vocabulaire){
                if ($vocabulaire['id'] == $id)return $vocabulaire;
            }
            return false;
        }

    }

    public function phone($number, $space = ' '){
        return trim( chunk_split($number, 2, $space), $space);
    }

}