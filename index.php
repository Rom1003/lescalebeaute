<?php
/*
 * Page de redirection
 */

//Autoload des class
require_once __DIR__ . '/vendor/autoload.php';

use PHPRouter\RouteCollection;
use PHPRouter\Config;
use PHPRouter\Router;
use PHPRouter\Route;

//Identification du controller à appeler
$config = Config::loadFromFile(__DIR__.'/config/routes.yml');
$router = Router::parseConfig($config);
$goto = $router->matchCurrentRequest();

//Si aucune route trouvée
if ($goto === false){
    \AppController\errorController::error404();
}


//var_dump(\AppController\indexController::)
