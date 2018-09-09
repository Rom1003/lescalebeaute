<?php

namespace AppController;

use \App\Config;
use \App\Database;
use \App\Tables\Categorie;
use App\Tables\Epilation;
use App\Tables\Produit;
use App\Tables\Slider;
use App\Tables\Image;
use App\Tables\Vocabulaire;
use App\pdf\EpilationPDF;
use \Illuminate\Database\Eloquent\Model;

class indexController{

    public static function indexAction(){
        $config = new Config();
        $twig = $config->initTwig();

        $menu = Categorie::getMenu();
        $slider = Slider::getSlides();
        $produits = Produit::with('image')->with('gamme')->where('actif', 1)->inRandomOrder()->limit(8)->get();
        $massages = Categorie::with('image')->where('niveau', 1)->inRandomOrder()->limit(4)->get();

        echo $twig->render('index.twig', array(
            'menu' => $menu,
            'slider' => $slider,
            'produits' => $produits,
            'massages' => $massages
        ));
    }

    public static function aproposAction(){
        $config = new Config();
        $twig = $config->initTwig();
        $menu = Categorie::getMenu();

        //récupération de la description
        $description = Vocabulaire::find(Vocabulaire::TEXTE_APROPOS);

        //récupération du slide
        $slide = Vocabulaire::find(Vocabulaire::SLIDER_APROPOS);
        $image_header = false;
        if (!empty($slide)){
            $image = Image::find($slide->valeur);
            if (!empty($image)){
                $image_header = $image->path.$image->filename;
            }
        }

        //récupération des images
        $images = array();
        $voc_images = Vocabulaire::find(Vocabulaire::IMAGES_APROPOS);
        if (!empty($voc_images)){
            foreach (unserialize($voc_images->valeur) as $id_image){
                $images[] = Image::find($id_image);
            }
        }

        echo $twig->render('a_propos.twig', array(
            'menu' => $menu,
            'image_header' => $image_header,
            'description' => $description,
            'images' => $images
        ));
    }

    public static function epilationAction(){
        $config = new Config();
        $twig = $config->initTwig();

        $menu = Categorie::getMenu();
        //récupération du slide
        $slide = Vocabulaire::find(Vocabulaire::SLIDER_EPILATION);
        $image_header = false;
        if (!empty($slide)){
            $image = Image::find($slide->valeur);
            if (!empty($image)){
                $image_header = $image->path.$image->filename;
            }
        }

        $produits = Produit::with('image')->with('gamme')->where('actif', 1)->inRandomOrder()->limit(6)->get();

        $epilations = Epilation::getListByType();



        echo $twig->render('epilations.twig', array(
            'menu' => $menu,
            'image_header' => $image_header,
            'epilations' => $epilations,
            'produits' => $produits
        ));
    }

    public static function epilationPDFAction(){
        //Récupération des épilations
        $epilations = Epilation::getListByType();

        if (empty($epilations)){
            \AppController\errorController::error500();
            exit;
        }

        EpilationPDF::listeTarifs($epilations);
    }

    public static function hammamAction(){
        //On récupère l'ID de la page hammam
        $hammam = Categorie::where('hammam', 1)->first();
        if (empty($hammam)){
            \AppController\errorController::error404();
            exit;
        }

        //Redirection vers la page correspondante
        categorieController::listeServicesAction($hammam->id);
//        header('location: ' . getRouteUrl('categorie_liste', array('id' => $hammam->id, 'libelle' => toAscii($hammam->libelle))));

    }

}


?>