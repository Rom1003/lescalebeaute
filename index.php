<?php
/*
 * Page de redirection
 */
ini_set("xdebug.var_display_max_children", -1);
ini_set("xdebug.var_display_max_data", -1);
ini_set("xdebug.var_display_max_depth", -1);

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
