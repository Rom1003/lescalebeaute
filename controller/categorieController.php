<?php

namespace AppController;

use \App\Config;
use \App\Database;
use \App\Tables\Categorie;
use \App\Tables\Service;
use \App\Tables\Produit;
use \App\Tables\Slider;
use \Illuminate\Database\Eloquent\Model;

class categorieController
{

    public static function listeAllAction(){
        $config = new Config();
        $twig = $config->initTwig();

        //récupérations des catégories à afficher
        $categories = Categorie::with('image')->where('niveau', 1)->orderBy('libelle')->get();
        if ($categories === false || empty($categories)){
            \AppController\errorController::error500();
            exit;
        }

        $menu = Categorie::getMenu();
        $slider = Slider::getSlides();
        $produits = Produit::with('image')->with('gamme')->where('actif', 1)->inRandomOrder()->limit(8)->get();

        echo $twig->render('liste_categories.twig', array(
            'menu' => $menu,
            'categories' => $categories,
            'produits' => $produits,
            'slider' => $slider,
        ));
    }

    public static function listeAction($id)
    {
        if (empty($id)) {
            \AppController\errorController::error404();
            exit;
        }

        //récupération de la catégorie demandée
        $categorie = Categorie::find($id);
        if ($categorie === false || empty($categorie)) {
            \AppController\errorController::error404();
            exit;
        }

        //Si la catégorie est une catégorie de niveau 0 : on récupère la liste catégories
        if ($categorie->niveau == 0 && $categorie->niveau_max > 0) {
            self::listeCategoriesAction($id);
        } else {
            //Afficher la liste des servicesc liés
            self::listeServicesAction($id);
        }

    }

    public static function listeCategoriesAction($id)
    {
        $config = new Config();
        $twig = $config->initTwig();
        $menu = Categorie::getMenu();

        //récupération de la catégorie demandée
        $categorie = Categorie::find($id);
        if ($categorie === false || empty($categorie)) {
            \AppController\errorController::error404();
            exit;
        }

        $image_header = Categorie::getImageSlide($categorie);

        //Récupération de la liste des catégories liées
        $categories = Categorie::listeCategorie($id);

        $produits = Produit::with('image')->with('gamme')->where('actif', 1)->inRandomOrder()->limit(6)->get();

        echo $twig->render('liste_categories.twig', array(
            'menu' => $menu,
            'categorie' => $categorie,
            'categories' => $categories,
            'image_header' => $image_header,
            'produits' => $produits
        ));
    }

    public static function listeServicesAction($id)
    {
        $config = new Config();
        $twig = $config->initTwig();
        $menu = Categorie::getMenu();

        //récupération de la catégorie demandée
        $categorie = Categorie::find($id);
        if ($categorie === false || empty($categorie)) {
            \AppController\errorController::error404();
            exit;
        }

        $image_header = Categorie::getImageSlide($categorie->categorie_id);

        //Récupération de la liste des services liés
        $services = Service::servicesFromCategorie($id);

        $produits = Produit::with('image')->with('gamme')->inRandomOrder()->limit(6)->get();

        echo $twig->render('liste_services.twig', array(
            'menu' => $menu,
            'categorie' => $categorie,
            'services' => $services,
            'produits' => $produits,
            'image_header' => $image_header
        ));

    }
}


?>