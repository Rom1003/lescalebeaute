<?php
/*
 * Ajout de fonctions dans le modèle Twig 
 */

//Récupération d'une url
$function = new Twig_Function('url', function ($name) {
    $config = new App\Config();
    if (!$config)return false;
    $url = $config->getRoutes($name);
    if (!$url)return false;
    return $url;
});
$twig->addFunction($function);

//Récupération de l'url d'une image
$function = new Twig_Function('image', function ($name) {
    $config = new App\Config();
    if (!$config)return false;
    $url = $config->getGlobal('IMG_ROOT');
    if (!$url)return false;
    return $url.$name;
});
$twig->addFunction($function);