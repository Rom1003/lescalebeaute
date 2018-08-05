<?php

/*
 * Ajout de variables globales
 * Ces variables seront ajoutés automatiquements dans le modèle de Twig
 */


$global = array();

$global['MODE'] = 'test';
if ($_SERVER['HTTP_HOST'] == '144.172.80.209'){
    $global['MODE'] = 'prod';
}

$global['LOGIN'] = 'admin';
$global['PASSWORD'] = '$2y$10$eRC31c8kR0osHQV6uEEU.uQvlwt24jgFrMDMy2ULrfVlfH3RPfucu';


if ($global['MODE'] != 'prod'){
    $global['HTTP_ROOT'] = 'http://lescalebeaute';
    $global['FILE_ROOT'] = 'C:\\wamp64\\www\\lescalebeaute/';
} else {
    $global['HTTP_ROOT'] = 'http://144.172.80.209/lescalebeaute';
    $global['FILE_ROOT'] = '/var/www/html/lescalebeaute/';
}

$global['SRC_ROOT'] = $global['HTTP_ROOT'].'/src/';
$global['IMG_ROOT'] = $global['SRC_ROOT'].'img/';


//Changement du path pour charger les fonts de FPDF
//define('FPDF_FONTPATH', $global['FILE_ROOT']."model/pdf/font/");