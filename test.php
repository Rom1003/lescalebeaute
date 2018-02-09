<?php
require_once '_commun.php';


    echo $twig->render('index.twig', array(

        'moteur_name' => 'Twig'

    ));

?>