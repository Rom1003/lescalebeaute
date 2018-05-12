<?php

function getRouteUrl($routeName, $params = array()){

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

