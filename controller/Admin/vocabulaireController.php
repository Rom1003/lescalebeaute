<?php

namespace AppController\Admin;

use \App\Config;
use \App\Database;
use \App\Tables\Categorie;
use App\Tables\Vocabulaire;
use \Illuminate\Database\Eloquent\Model;
use Respect\Validation\Validator as v;

class vocabulaireController{

    public static function indexAction($vars = array()){
        $config = new Config();
        $twig = $config->initTwig();

        try {
            $vocabulaire = Vocabulaire::get()->toArray();
        } catch (\PDOException $exception){
            \AppController\errorController::error500();
            exit;
        }


        echo $twig->render('Admin/Vocabulaire/information.twig', array(
            'vocabulaire' => $vocabulaire,
            'vars' => $vars
        ));
    }

    public static function editAction(){
        $anomalies = array();
        $message = array('type' => 'conf', 'message' => "Les modifications ont bien été prises en compte", 'titre' => "Confirmation");

        $vocabulaires = array();
        foreach ($_POST as $name=>$post){
            if (preg_match('/(vocabulaire_)([0-9]+)$/', $name, $matches) == 1){
                $vocabulaires[$matches[2]] = $post;
            }
        }

        //Vérification du n° de téléphone
        if (!isset($vocabulaires[1]) || empty($vocabulaires[1])){
            $anomalies[1] = 'Le n° de téléphone doit être renseigné';
        }
        $vocabulaires[1] = preg_replace('/[^0-9]/', '', $vocabulaires[1]);
        if (!v::phone()->validate($vocabulaires[1])){
            $anomalies[1] = 'Le n° de téléphone renseigné est incorect';
        }
        //Mise à jour
        $voc = Vocabulaire::find(1);
        $voc->valeur = $vocabulaires[1];
        if (!$voc->save()){
            $anomalies[1] = 'Une erreur est survenue lors de la mise à jour du n° de téléphone';
        }

        //Vérification Adresse
        if (!isset($vocabulaires[2]) || empty($vocabulaires[2])){
            $anomalies[2] = 'Une adresse doit être renseignée';
        }
        //Mise à jour
        $voc = Vocabulaire::find(2);
        $voc->valeur = $vocabulaires[2];
        if (!$voc->save()){
            $anomalies[2] = 'Une erreur est survenue lors de la mise à jour de l\'adresse';
        }

        //Création du message d'anomalie
        if (!empty($anomalies)){
            //Présence d'anomalies
            $message = array('type' => 'err', 'message' => "<ul>", 'titre' => "Erreur");
            foreach ($anomalies as $anomalie){
                $message['message'] .= '<li>'.$anomalie.'</li>';
            }
            $message['message'] .= "</ul>";
        } else {
            //Tout est bon, on retire les post
            $vocabulaires = array();
        }




        self::indexAction(array(
            'notification' => $message,
            'form_voc' => $vocabulaires
        ));

    }
}


?>