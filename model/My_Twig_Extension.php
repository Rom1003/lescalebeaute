<?php

namespace App;

use \App\Config;
use \Twig_Extension;
use \DateTime;
use App\Tables\Vocabulaire;
use \Illuminate\Database\Eloquent\Model;
use PHPRouter\Router;

class My_Twig_Extension extends Twig_Extension {

    public function getFunctions()
    {
        $functions = array();

        $functions[] = new \Twig_Function('titre', array($this, 'titre'));
        $functions[] = new \Twig_Function('url', array($this, 'url'));
        $functions[] = new \Twig_Function('image', array($this, 'image'));
        $functions[] = new \Twig_Function('showAlert', array($this, 'showAlert'));
        $functions[] = new \Twig_Function('getVocabulaire', array($this, 'getVocabulaire'));
        $functions[] = new \Twig_Function('getRouteUrl', array($this, 'getRouteUrl'));
        $functions[] = new \Twig_Function('toAscii', array($this, 'convertToAscii'));
        $functions[] = new \Twig_Function('isOpen', array($this, 'isOpen'));

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

    public function getVocabulaire($id, $vocabulaires = array(), $isArray = false){
        if (empty($vocabulaires)){
            $valeur = Vocabulaire::getValeur($id);
            if ($valeur === false){
                return '';
            } else {
                if (isJson($valeur))return json_decode($valeur);
                return $valeur;
            }
        } else {
            foreach ($vocabulaires as $vocabulaire){
                if ($vocabulaire['id'] == $id){
                    if (isJson($vocabulaire['valeur']))$vocabulaire['valeur'] = json_decode($vocabulaire['valeur']);
                    return $vocabulaire;
                }
            }
            return false;
        }

    }

    public function showAlert($type, $message, $titre){
        switch ($type){
            case 'conf':
                $class = 'success';
                break;
            case 'err':
                $class = 'alert';
                break;
            case 'warn':
                $class = 'warning';
                break;
            case 'info':
                $class = 'primary';
                break;
            default:
                return '';
        }

        $html = '
        <div class="callout '.$class.'" data-closable>
            <h5>'.$titre.'</h5>
            <p>'.$message.'</p>
            <button class="close-button" aria-label="Fermer" type="button" data-close>
                <span aria-hidden="true">&times;</span>
            </button>
        </div>';
        return $html;
    }

    public function phone($number, $space = ' '){
        return trim( chunk_split($number, 2, $space), $space);
    }

    public function getRouteUrl($routeName, $params = array()){
/*        $config = \PHPRouter\Config::loadFromFile(dirname(dirname(__FILE__)).'/config/routes.yml');
        $router = Router::parseConfig($config);*/

        try {
            require dirname(dirname(__FILE__)).'/config/routes.php';
            $url = $router->generate($routeName, $params);
        } catch (\Exception $exception){
            $url = sprintf(
                "%s://%s%s",
                isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off' ? 'https' : 'http',
                $_SERVER['SERVER_NAME'],
                $_SERVER['REQUEST_URI']
            );
        }

        return $url;

    }

    public function convertToAscii($string){
        return toAscii($string);
    }

    public function isOpen(){
        $horaires = $this->getVocabulaire(Vocabulaire::HORAIRES);
        $jour = date('N') - 1;

        if (!isset($horaires[$jour]))return false;
        $heures = explode('-', $horaires[$jour]);
        if (empty($heures) || !isset($heures[1]))return false;

        $currentTime = (new DateTime(date('H:i')))->modify('+1 day');
        $startTime = new DateTime($heures[0]);
        $endTime = (new DateTime($heures[1]))->modify('+1 day');

        if ($currentTime >= $startTime && $currentTime <= $endTime) {
            return true;
        }
        return false;
    }

}