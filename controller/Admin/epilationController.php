<?php

namespace AppController\Admin;

use \App\Config;
use \App\Database;
use App\Tables\Epilation;
use \Illuminate\Database\Eloquent\Model;
use Respect\Validation\Validator as v;

class epilationController
{

    public static function indexAction()
    {
        $config = new Config();
        $twig = $config->initTwig();

        try {
            $epilations = Epilation::getListByType();
        } catch (\PDOException $exception) {
            \AppController\errorController::error500();
            exit;
        }

        echo $twig->render('Admin/Epilation/list.twig', array(
            'epilations' => $epilations
        ));
    }

    /**
     * Supression d'un enregistrement en base
     */
    public static function removeAction()
    {
        if (!isset($_POST['id']) || empty($_POST['id'])){
            exit(json_encode(array('etat' => 'err', 'message' => 'Une erreur est survenue')));
        }
        if (!Epilation::where('id', $_POST['id'])->delete()){
            exit(json_encode(array('etat' => 'err', 'message' => 'Une erreur est survenue')));
        } else {
            exit(json_encode(array('etat' => 'conf', 'message' => 'La ligne a bien été retirée')));
        }
    }

    public static function editAction()
    {
        if (!isset($_POST['type']) || !isset($_POST['libelle']) || !isset($_POST['prix']) || empty($_POST['type']) || empty($_POST['libelle']) || empty($_POST['prix'])){
            exit(json_encode(array('etat' => 'err', 'message' => "Une erreur est survenue")));
        }

        $anomalies = array();
        foreach ($_POST['libelle'] as $key=>$libelle){

            $texteAno = "N° ".$key." : ";
            $anomalie = array();

            //ne pas garder les lignes vides
            if (empty($_POST['libelle'][$key]) && empty($_POST['type'][$key]) && empty($_POST['prix'][$key]))continue;

            //On vérifie si il s'agit d'une modification ou d'une insertion
            $update = false;
            if (isset($_POST['id'][$key])){
                $update = true;
            }

            //On vérifie le type
            if (!isset($_POST['type'][$key])){
                $anomalie[] = 'Type inexistant';
            } elseif (!isset(Epilation::TYPES[$_POST['type'][$key]])){
                $anomalie[] = 'Type inconnu : '.$_POST['type'][$key];
            }

            //On vérifie le libelle
            if (!isset($_POST['libelle'][$key])){
                $anomalie[] = 'Libellé inexistant';
            } elseif (empty(trim($_POST['libelle'][$key]))){
                $anomalie[] = 'Libellé vide';
            }

            //On vérifie le prix
            if (!isset($_POST['prix'][$key])){
                $anomalie[] = 'Prix inexistant';
            } elseif (empty(trim($_POST['prix'][$key]))){
                $anomalie[] = 'Veuillez renseigner un prix';
            } else {
                //Remplacement des "," par des "."
                $_POST['prix'][$key] = str_replace(",", ".", $_POST['prix'][$key]);
                if (!v::floatVal()->validate($_POST['prix'][$key]) || $_POST['prix'][$key] <= 0) {
                    $anomalie[] = 'Le prix renseigné est incorrect';
                }
            }

            if (!empty($anomalie)){
                //anomalies présente pour cette ligne
                foreach ($anomalie as $i=>$ano){
                    if ($i != 0)$texteAno .= " / ";
                    $texteAno .= $ano;
                }
                $anomalies[$key] = $texteAno;
            } else {
                //Tout est bon, on met à jour
                if ($update === true){
                    //On effectue un update
                    $epilation = Epilation::find($_POST['id'][$key]);

                    $epilation->libelle = $_POST['libelle'][$key];
                    $epilation->prix = $_POST['prix'][$key];
                    $epilation->type = $_POST['type'][$key];
                    if (!$epilation->save()) {
                        $anomalies[$key] = $texteAno.'Une erreur est survenue lors de la mise à jour';
                    }
                } else {
                    //On effectue un insert
                    $epilation = new Epilation();

                    $epilation->libelle = $_POST['libelle'][$key];
                    $epilation->prix = $_POST['prix'][$key];
                    $epilation->type = $_POST['type'][$key];
                    if (!$epilation->save()) {
                        $anomalies[$key] = $texteAno.'Une erreur est survenue lors de l\'ajout';
                    }
                }
            }

        }

        //Création du message d'anomalie
        if (!empty($anomalies)) {
            //Présence d'anomalies
            exit(json_encode(array('etat' => 'err', 'anomalies' => $anomalies)));
        } else {
            //Aucune anomalies
            $_SESSION['notification'] = array('type' => 'conf', 'message' => "Les modifications on bien été prises en compte", 'titre' => "Confirmation");
            exit(json_encode(array('etat' => 'conf', 'message' => "Les modifications on bien été prises en compte")));
        }


    }
}

?>