<?php
/*
 * Page de redirection
 */
ini_set("xdebug.var_display_max_children", -1);
ini_set("xdebug.var_display_max_data", -1);
ini_set("xdebug.var_display_max_depth", -1);

//Autoload des class
require_once __DIR__ . '/vendor/autoload.php';

use App\Database;

//Identification du controller à appeler
/*$config = Config::loadFromFile(__DIR__.'/config/routes.yml');
$router = Router::parseConfig($config);*/

include_once __DIR__.'/config/routes.php';
include_once __DIR__.'/model/My_functions.php';

//Création des sessions si elles n'existent pas
if (!session_id()){
    @session_start();
}



new Database();
$goto = $router->matchCurrentRequest();


//Si aucune route trouvée
if ($goto === false){
    \AppController\errorController::error404();
}

if (isset($_SESSION['notification'])){
    unset($_SESSION['notification']);
}

if (!isset($_SESSION['chargement'])){
    $_SESSION['chargement'] = true;
} else {
    $_SESSION['chargement'] = false;
}
//var_dump(\AppController\indexController::)
