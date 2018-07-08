<?php

namespace AppController\Admin;

use \App\Config;
use \App\Database;
use App\Tables\Gamme;
use \Illuminate\Database\Eloquent\Model;
use Respect\Validation\Validator as v;

class gammeController
{
    public static function indexAction(){
        //Récupération de la liste des gammes existantes
        $config = new Config();
        $twig = $config->initTwig();


        try {
            $gammes = Gamme::orderBy('libelle')->get();
        } catch (\PDOException $exception) {
            \AppController\errorController::error500();
            exit;
        }

        echo $twig->render('Admin/Gamme/list.twig', array(
            'gammes' => $gammes
        ));
    }

    public static function addFormAction()
    {
        $config = new Config();
        $twig = $config->initTwig();


        echo $twig->render('Admin/Gamme/new.twig', array(
        ));
    }

    public static function addProcessAction(){
        $anomalies = array();

        $post = trimArray($_POST);

        //Si libellé vide
        if (!isset($post['libelle']) || empty($post['libelle'])){
            $anomalies[] = 'Veuillez renseigner un libellé';
        }

        //Création du message d'anomalie
        if (!empty($anomalies)) {
            //Présence d'anomalies
            $message = '<ul>';
            foreach ($anomalies as $anomalie) {
                $message .= '<li>' . $anomalie . '</li>';
            }
            $message .= "</ul>";
        } else {
            //On vérifie que la marque n'existe pas deja
            $gamme = Gamme::where('libelle', $post['libelle'])->get();
            if (!$gamme->isEmpty()){
                $message = "Cette gamme existe déjà";
            } else {
                //Tout est bon, on ajoute en bdd
                $gamme = new Gamme();
                $gamme->libelle = $post['libelle'];
                if (!$gamme->save()) {
                    $message = "Une erreur est survenue lors de l'ajout de la gamme";
                } else {
                    //Redirection vers la liste des services
                    $_SESSION['notification'] = array('type' => 'conf', 'message' => "La gamme a bien été ajoutée", 'titre' => "Confirmation");
                    echo json_encode(array('etat' => 'conf', 'url' => getRouteUrl('admin_gamme')));
                    exit;
                }
            }


        }

        echo json_encode(array('etat' => 'err', 'message' => $message));
    }

    public static function editFormAction($id)
    {
        $config = new Config();
        $twig = $config->initTwig();

        //Récupération des informations de la gamme
        try {
            $gamme = Gamme::find($id);
            if (!$gamme || empty($gamme)){
                \AppController\errorController::error404();
                exit;
            }
        } catch (\PDOException $exception) {
            \AppController\errorController::error500();
            exit;
        }


        echo $twig->render('Admin/Gamme/edit.twig', array(
            'gamme' => $gamme,
        ));
    }

    public static function editProcessAction($id){
        $anomalies = array();

        $post = trimArray($_POST);

        //Récupération des informations de la gamme
        try {
            $gamme = Gamme::find($id);
            if (!$gamme || empty($gamme)){
                exit(json_encode(array('etat' => 'err', 'message' => "Impossible de retrouver cet gamme")));
            }
        } catch (\PDOException $exception) {
            exit(json_encode(array('etat' => 'err', 'message' => "Une erreur est survenue")));
        }

        //Si libellé vide
        if (!isset($post['libelle']) || empty($post['libelle'])){
            $anomalies[] = 'Veuillez renseigner un libellé';
        }

        //Création du message d'anomalie
        if (!empty($anomalies)) {
            //Présence d'anomalies
            $message = '<ul>';
            foreach ($anomalies as $anomalie) {
                $message .= '<li>' . $anomalie . '</li>';
            }
            $message .= "</ul>";
        } else {
            //On vérifie que la gamme n'existe pas deja
            if (!Gamme::where('libelle', $post['libelle'])->get()->isEmpty()){
                $message = "Cette gamme existe déjà";
            } else {
                //Tout est bon, on modifie en bdd
                $gamme->libelle = $post['libelle'];
                if (!$gamme->save()) {
                    $message = "Une erreur est survenue lors de l'édition de la gamme";
                } else {
                    //Redirection vers la liste des services
                    $_SESSION['notification'] = array('type' => 'conf', 'message' => "La gamme a bien été modifiée", 'titre' => "Confirmation");
                    echo json_encode(array('etat' => 'conf', 'url' => getRouteUrl('admin_gamme')));
                    exit;
                }
            }


        }

        echo json_encode(array('etat' => 'err', 'message' => $message));
    }
}