<?php

use App\Config;

function getRouteUrl($routeName, $params = array())
{
    try {
        require dirname(dirname(__FILE__)) . '/config/routes.php';
        $url = $router->generate($routeName, $params);
    } catch (\Exception $exception) {
        $url = sprintf(
            "%s://%s%s",
            isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off' ? 'https' : 'http',
            $_SERVER['SERVER_NAME'],
            $_SERVER['REQUEST_URI']
        );
    }
    return $url;
}

function generatePagination($page, $total, $nbParPage)
{
    $totalpages = ceil($total / $nbParPage);


    $pagination = '<ul class="pagination text-center">';
    //Nombre de pages à afficher
    $range = 3;

//Affichage du premier et page précédente
    if ($page > 1) {
        $pagination .= '
                <li><a class="pagination-page" data-page="' . ($page - 1) . '"><i class="fas fa-angle-left"></i></a></li>
                <li><a class="pagination-page" data-page="1"><i class="fas fa-angle-double-left"></i></a></li>';
    } else {
        $pagination .= '
                <li class="disabled"><i class="fas fa-angle-left"></i></li>
                <li class="disabled"><i class="fas fa-angle-double-left"></i></li>';
    }

//Affichage des pages autour de la page actuelle
    for ($x = ($page - $range); $x < (($page + $range) + 1); $x++) {
        //Page à afficher
        if (($x > 0) && ($x <= $totalpages)) {
            //Page actuelle
            if ($x == $page) {
                $pagination .= '
                <li class="current"><span class="show-for-sr"></span> ' . $x . '</li>';
            } else {
                $pagination .= '
                <li><a class="pagination-page" data-page="' . $x . '">' . $x . '</a></li>';
            }
        }
    }

//Affichage de la dernière et de la page suivante
    if ($page != $totalpages) {
        $pagination .= '
                <li><a class="pagination-page" data-page="' . $totalpages . '"><i class="fas fa-angle-double-right"></i></a></li>
                <li><a class="pagination-page" data-page="' . ($page + 1) . '"><i class="fas fa-angle-right"></i></a></li>';
    } else {
        $pagination .= '
                <li class="disabled"><i class="fas fa-angle-double-right"></i></li>
                <li class="disabled"><i class="fas fa-angle-right"></i></li>';
    }

    $pagination .= '</ul>';

    return $pagination;
}

function createDirIfNotExist($path)
{
    if (!file_exists($path)) {
        mkdir($path, 0755, true);
    }
}

function trimArray($data)
{
    if ($data == null)
        return null;

    if (is_array($data)) {
        return array_map('trimArray', $data);
    } else return trim($data);
}

function removeAccent($string)
{
    $unwanted_array = array('Š' => 'S', 'š' => 's', 'Ž' => 'Z', 'ž' => 'z', 'À' => 'A', 'Á' => 'A', 'Â' => 'A', 'Ã' => 'A', 'Ä' => 'A', 'Å' => 'A', 'Æ' => 'A', 'Ç' => 'C', 'È' => 'E', 'É' => 'E',
        'Ê' => 'E', 'Ë' => 'E', 'Ì' => 'I', 'Í' => 'I', 'Î' => 'I', 'Ï' => 'I', 'Ñ' => 'N', 'Ò' => 'O', 'Ó' => 'O', 'Ô' => 'O', 'Õ' => 'O', 'Ö' => 'O', 'Ø' => 'O', 'Ù' => 'U',
        'Ú' => 'U', 'Û' => 'U', 'Ü' => 'U', 'Ý' => 'Y', 'Þ' => 'B', 'ß' => 'Ss', 'à' => 'a', 'á' => 'a', 'â' => 'a', 'ã' => 'a', 'ä' => 'a', 'å' => 'a', 'æ' => 'a', 'ç' => 'c',
        'è' => 'e', 'é' => 'e', 'ê' => 'e', 'ë' => 'e', 'ì' => 'i', 'í' => 'i', 'î' => 'i', 'ï' => 'i', 'ð' => 'o', 'ñ' => 'n', 'ò' => 'o', 'ó' => 'o', 'ô' => 'o', 'õ' => 'o',
        'ö' => 'o', 'ø' => 'o', 'ù' => 'u', 'ú' => 'u', 'û' => 'u', 'ý' => 'y', 'þ' => 'b', 'ÿ' => 'y');
    return strtr($string, $unwanted_array);
}

function toAscii($str, $replace=array(), $delimiter='-') {
    if( !empty($replace) ) {
        $str = str_replace((array)$replace, ' ', $str);
    }

    $clean = removeAccent($str);
    $clean = iconv('UTF-8', 'ASCII//TRANSLIT', $clean);
    $clean = preg_replace("/[^a-zA-Z0-9\/_|+ -]/", '', $clean);
    $clean = strtolower(trim($clean, '-'));
    $clean = preg_replace("/[\/_|+ -]+/", $delimiter, $clean);

    return $clean;
}

function imagePath($path){
    $config = new Config();
    if (!$config)return false;
    $url = $config->getGlobal('IMG_ROOT');
    if (!$url)return false;
    return $url.$path;
}

function isJson($string){
    $reg = "/(\[|\{).*(\]|\})$/";
    if (preg_match($reg, $string) === 0 ){
        return false;
    } else {
        return true;
    }
}

function timeVerification($time){
    $reg = '/^([0-9]|0[0-9]|1[0-9]|2[0-3]):[0-5][0-9]$/';
    if (preg_match($reg, $time) != 1){
        return false;
    } else {
        return true;
    }
}