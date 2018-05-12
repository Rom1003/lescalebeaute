<?php

namespace AppController\Admin;

use \App\Config;
use \App\Database;
use \App\Tables\Categorie;
use App\Tables\Vocabulaire;
use \Illuminate\Database\Eloquent\Model;
use Respect\Validation\Validator as v;

class categorieController{

    public static function indexAction($vars = array()){
        $config = new Config();
        $twig = $config->initTwig();

        $categories = Categorie::getMenu(false, false);

        echo $twig->render('Admin/Categorie/list.twig', array(
            'categories' => $categories,
            'vars' => $vars
        ));
    }

    public static function editFormAction($id, $vars = array()){
        $config = new Config();
        $twig = $config->initTwig();

        try {
            $categorie = Categorie::find($id);
        } catch (\PDOException $exception){
            \AppController\errorController::error500();
            exit;
        }


        echo $twig->render('Admin/Categorie/edit.twig', array(
            'categorie' => $categorie,
            'vars' => $vars
        ));
    }

    public static function editProcessAction($id){
        $anomalies = array();
        $message = array('type' => 'conf', 'message' => "Les modifications ont bien été prises en compte", 'titre' => "Confirmation");

        $post = array_map('trim', $_POST);

        //Vérification des post
        if (!isset($post['libelle']) || empty($post['libelle'])){
            $anomalies[] = 'Le libellé doit être renseigné';
        }
        if (!isset($post['description']) || empty($post['description'])){
            $anomalies[] = 'Une description doit être renseignée';
        }

        //Création du message d'anomalie
        if (!empty($anomalies)){
            //Présence d'anomalies
            $message = array('type' => 'err', 'message' => "<ul>", 'titre' => "Erreur");
            foreach ($anomalies as $anomalie){
                $message['message'] .= '<li>'.$anomalie.'</li>';
            }
            $message['message'] .= "</ul>";

            $_SESSION['notification'] = $message;

            //Redirection vers la page de formulaire
            self::editFormAction($id, array(
                'post' => $post
            ));
        } else {
            //Tout est bon, on retire les post et on met à jour
            $update = Categorie::find($id);
            $update->libelle = $post['libelle'];
            $update->description = $post['description'];
            if (!$update->save()){
                $message = array('type' => 'err', 'message' => "Une erreur est survenue lors de la mise à jour de la catégorie", 'titre' => "Erreur");
            }

            //Redirection vers la liste des catégorie
            $_SESSION['notification'] = $message;
            header('location: '.getRouteUrl('admin_categorie'));
            exit;
        }

    }

    public static function newFormAction($categorie_id, $vars = array()){
        $config = new Config();
        $twig = $config->initTwig();

        //Vérifie que la catégorie existe
        $categorie = Categorie::where('id', $categorie_id)->where('niveau', 0)->where('editable', 1)->get()->first();
        if ($categorie === false || empty($categorie)){
            self::indexAction(array('notification' => array('type' => 'err', 'message' => 'Impossible d\'ajouter une sous catégorie à cette catégorie', 'titre' => 'Erreur')));
            exit;
        }

        echo $twig->render('Admin/Categorie/new.twig', array(
            'vars' => $vars,
            'categorie' => $categorie
        ));
    }

    public static function newProcessAction($categorie_id){
        $anomalies = array();
        $message = array('type' => 'conf', 'message' => "La catégorie a bien été ajoutée", 'titre' => "Confirmation");

        $post = array_map('trim', $_POST);

        //Vérification des post
        if (!isset($post['libelle']) || empty($post['libelle'])){
            $anomalies[] = 'Le libellé doit être renseigné';
        }
        if (!isset($post['description']) || empty($post['description'])){
            $anomalies[] = 'Une description doit être renseignée';
        }

        //Création du message d'anomalie
        if (!empty($anomalies)){
            //Présence d'anomalies
            $message = array('type' => 'err', 'message' => "<ul>", 'titre' => "Erreur");
            foreach ($anomalies as $anomalie){
                $message['message'] .= '<li>'.$anomalie.'</li>';
            }
            $message['message'] .= "</ul>";

            $_SESSION['notification'] = $message;

            //Redirection vers la page de formulaire
            self::editFormAction($categorie_id, array(
                'post' => $post
            ));
        } else {
            //Tout est bon, on retire les post et on met à jour
            //Vérifie que la catégorie existe
            $categorie = Categorie::where('id', $categorie_id)->where('niveau', 0)->where('editable', 1)->get()->first();
            if ($categorie === false || empty($categorie)){
                self::indexAction(array('notification' => array('type' => 'err', 'message' => 'Impossible d\'ajouter une sous catégorie à cette catégorie', 'titre' => 'Erreur')));
                exit;
            }

            //Récupération de la dernière sous-catégorie
            $last_categorie = Categorie::where('categorie_id', $categorie_id)->where('niveau', 1)->orderBy('ordre', 'desc')->get()->first();
            if ($last_categorie === false){
                self::indexAction(array('notification' => array('type' => 'err', 'message' => 'Impossible d\'ajouter une sous catégorie à cette catégorie', 'titre' => 'Erreur')));
                exit;
            } elseif (empty($last_categorie)){
                $ordre = '1';
            } else {
                $ordre = $last_categorie->toArray()['ordre'] + 1;
            }

            $insert = new Categorie;
            $insert->libelle = $post['libelle'];
            $insert->description = $post['description'];
            $insert->ordre = $ordre;
            $insert->niveau = 1;
            $insert->categorie_id = $categorie_id;
            if (!$insert->save()){
                $message = array('type' => 'err', 'message' => "Une erreur est survenue lors de l\'ajour de la catégorie", 'titre' => "Erreur");
            }

            //Redirection vers la liste des catégorie
            $_SESSION['notification'] = $message;
            header('location: '.getRouteUrl('admin_categorie'));
            exit;
            //Redirection vers la liste des catégorie
        }

    }
}


?>