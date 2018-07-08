<?php

namespace AppController;

use \App;
use App\Tables\Categorie;
use App\Tables\Produit;

class produitController {

    public static function indexAction(){
        $config = new App\Config();
        $twig = $config->initTwig();
        $menu = Categorie::getMenu();

        echo $twig->render('liste_produits.twig', array(
            'menu' => $menu
        ));
    }

    public static function paginateAction()
    {
        if (!isset($_POST['nbParPage']) || $_POST['nbParPage'] <= 0) {
            $_POST['nbParPage'] = '';
        }
        if (!isset($_POST['page']) || $_POST['page'] <= 0) {
            $_POST['page'] = 1;
        }

        $recherche = array(
            'actif' => 1
        );

        $produits = Produit::pagination('base', $_POST['page'], $_POST['nbParPage'], $recherche);
        echo json_encode(array('etat' => 'conf', 'html' => $produits));
    }
}


?>