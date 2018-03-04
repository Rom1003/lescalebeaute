<?php

/*
 * Ajout de variables globales
 * Ces variables seront ajoutés automatiquements dans le modèle de Twig
 */


$global = array();

$global['MODE'] = 'test';
if (strpos($_SERVER['SERVER_NAME'], '144.172.80.209')){
    $global['MODE'] = 'prod';
}



if ($global['MODE'] != 'prod'){
    $global['HTTP_ROOT'] = 'http://lescalebeaute';
    $global['FILE_ROOT'] = 'C:\\wamp64\\www\\lescalebeaute/';
} else {
    $global['HTTP_ROOT'] = 'http://144.172.80.209/lescalebeaute';
    $global['FILE_ROOT'] = '/var/www/html/lescalebeaute/';
}

$global['SRC_ROOT'] = $global['HTTP_ROOT'].'/src/';
$global['IMG_ROOT'] = $global['SRC_ROOT'].'img/';
