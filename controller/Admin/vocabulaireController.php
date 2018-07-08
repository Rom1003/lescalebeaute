<?php

namespace AppController\Admin;

use \App\Config;
use \App\Database;
use \App\Tables\Categorie;
use App\Tables\Vocabulaire;
use \Illuminate\Database\Eloquent\Model;
use Respect\Validation\Validator as v;

class vocabulaireController{

    public static function indexAction(){
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
        ));
    }

    public static function editAction(){
        $anomalies = array();
        $message = array('type' => 'conf', 'message' => "Les modifications ont bien été prises en compte", 'titre' => "Confirmation");

        $vocabulaires = array();
        foreach ($_POST as $name=>$post){
            if (preg_match('/(vocabulaire_)([0-9]+)(.*)/', $name, $matches) == 1){
                if (isset($matches[3]) && $matches[3] != ''){
                    $vocabulaires[$matches[2]][ltrim($matches[3], '_')] = $post;
                } else {
                    $vocabulaires[$matches[2]] = $post;
                }

            }
        }
        //Vérification du n° de téléphone
        $id = 1;
        if (!isset($vocabulaires[$id]) || empty($vocabulaires[$id])){
            $anomalies[$id] = 'Le n° de téléphone doit être renseigné';
        } else {
            $vocabulaires[$id] = preg_replace('/[^0-9]/', '', $vocabulaires[$id]);
            if (!v::phone()->validate($vocabulaires[1])){
                $anomalies[$id] = 'Le n° de téléphone renseigné est incorect';
            }
            //Mise à jour
            $voc = Vocabulaire::find($id);
            $voc->valeur = $vocabulaires[$id];
            if (!$voc->save()){
                $anomalies[$id] = 'Une erreur est survenue lors de la mise à jour du n° de téléphone';
            }
        }


        //Vérification Adresse
        $id = 2;
        if (!isset($vocabulaires[$id]) || empty($vocabulaires[$id])){
            $anomalies[$id] = 'Une adresse doit être renseignée';
        } else {
            //Mise à jour
            $voc = Vocabulaire::find($id);
            $voc->valeur = $vocabulaires[$id];
            if (!$voc->save()){
                $anomalies[$id] = 'Une erreur est survenue lors de la mise à jour de l\'adresse';
            }
        }


        //Vérification Texte massages
        $id = 3;
        if (!isset($vocabulaires[$id]) || empty($vocabulaires[$id])){
            $anomalies[$id] = 'Une adresse doit être renseignée';
        } else {
            //Mise à jour
            $voc = Vocabulaire::find($id);
            $voc->valeur = $vocabulaires[$id];
            if (!$voc->save()){
                $anomalies[$id] = 'Une erreur est survenue lors de la mise à jour de l\'adresse';
            }
        }


        //Vérification Texte produits
        $id = 4;
        if (!isset($vocabulaires[$id]) || empty($vocabulaires[$id])){
            $anomalies[$id] = 'Une adresse doit être renseignée';
        } else {
            //Mise à jour
            $voc = Vocabulaire::find($id);
            $voc->valeur = $vocabulaires[$id];
            if (!$voc->save()){
                $anomalies[$id] = 'Une erreur est survenue lors de la mise à jour de l\'adresse';
            }
        }


        //Vérification des horaires
        $id = 5;
        $valeur = array();
        foreach (Vocabulaire::HORAIRES_JOURS as $index=>$jour){
            $valeur[$index] = "";
            $erreur = false;
            foreach (['am' => 'matin', 'pm' => 'après-midi'] as $typePeriode=>$libPeriode){
                if (!isset($vocabulaires[$id][$index."_".$typePeriode])){
                    $anomalies[$id."_".$index."_am"] = 'Erreur lors de la vérification de l\'horaire d\'ouverture du '.$jour.' '.$libPeriode;
                    $erreur = true;
                    continue;
                } else {
                    if (!empty($vocabulaires[$id][$index."_".$typePeriode])){
                        if (!timeVerification($vocabulaires[$id][$index."_".$typePeriode])){
                            $anomalies[$id."_".$index."_".$typePeriode] = 'L\'horaire d\'ouverture du '.$jour.' '.$libPeriode.' est incorrecte';
                            $erreur = true;
                            continue;
                        }
                    }
                }
                if (!empty($valeur[$index]))$valeur[$index] .= '-';
                $valeur[$index] .= $vocabulaires[$id][$index."_".$typePeriode];
            }
        }
        if ($erreur === false){
            //Aucune erreur dans les horaires, on met à jour en bdd
            $voc = Vocabulaire::find($id);
            $voc->valeur = json_encode($valeur);
            if (!$voc->save()){
                $anomalies[$id] = 'Une erreur est survenue lors de la mise à jour des horaires';
            }
        }

        //Création du message d'anomalie
        if (!empty($anomalies)){
            //Présence d'anomalies
            $message = '<ul>';
            foreach ($anomalies as $anomalie) {
                $message .= '<li>' . $anomalie . '</li>';
            }
            $message .= "</ul>";
            exit(json_encode(array('etat' => 'err', 'message' => $message)));
        }

        $_SESSION['notification'] = array('type' => 'conf', 'message' => "Les informations ont bien été modifiées", 'titre' => "Confirmation");
        echo json_encode(array('etat' => 'conf', 'url' => getRouteUrl('admin_information')));
        exit();
    }
}


?>