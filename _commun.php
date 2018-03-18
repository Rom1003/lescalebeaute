<?php

require_once __DIR__ . '/vendor/autoload.php';

$config = new App\Config();
if ($config === false){
    exit();
}
$global = $config->getGlobal();

// Charge les templates
$loader = new Twig_Loader_Filesystem(array($global['FILE_ROOT'].'view', $global['FILE_ROOT'].'templates'));

$twig = new Twig_Environment($loader, array(
    'cache' => false,
    'debug' => true

));

$function = new Twig_Function('titre', function ($titre, $texte) {
    return 'ok';
});
$twig->addFunction($function);

//Activation du debug()
$twig->addExtension(new Twig_Extension_Debug());

//Ajout des fonctions custom
include_once $global['FILE_ROOT'].'model/Twig/Functions.php';

//Ajout des variables globales
$twig->addGlobal('glb', $global);
