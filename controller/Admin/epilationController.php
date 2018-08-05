<?php

namespace AppController\Admin;

use \App\Config;
use \App\Database;
use App\Tables\Epilation;
use App\Tables\Vocabulaire;
use App\Tables\Image;
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

        $description = Vocabulaire::find(Vocabulaire::TEXTE_EPILATION);

        //Récupération de l'image du slider
        $slide = false;
        $voc_slide = Vocabulaire::find(Vocabulaire::SLIDER_EPILATION);
        if (!empty($voc_slide->valeur)) {
            $slide = Image::find($voc_slide->valeur);
            if (empty($slide)) {
                $slide = false;
            }
        }

        echo $twig->render('Admin/Epilation/list.twig', array(
            'epilations' => $epilations,
            'slide' => $slide,
            'description' => $description
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
        $config = new Config();
        if (!isset($_POST['type']) || !isset($_POST['libelle']) || !isset($_POST['prix']) || empty($_POST['type']) || empty($_POST['libelle']) || empty($_POST['prix'])){
            exit(json_encode(array('etat' => 'err', 'message' => "Une erreur est survenue")));
        }

        $anomalies = array();

        $nameSlide = 'slide';
        $path = $config->getGlobal('FILE_ROOT') . '/src/img/';
        $bddPathSlide = 'slide/';

        if (isset($_FILES[$nameSlide]) && !empty($_FILES[$nameSlide]) && !empty($_FILES[$nameSlide]['tmp_name'])) {
            createDirIfNotExist($path . $bddPathSlide);
            $id = Vocabulaire::SLIDER_EPILATION;

            //Ajout de l'image
            $storage = new \Upload\Storage\FileSystem($path . $bddPathSlide);
            $file = new \Upload\File($nameSlide, $storage);

            //On vérifie si l'image existe déjà en base
            $image_existe = Image::where('md5', $file->getMd5())->get();
            if ($image_existe->isNotEmpty()) {
                $image = array(
                    'etat' => 'exist',
                    'id' => $image_existe->first()->id
                );
                unset($file);
            } else {
                $new_filename = uniqid();
                $file->setName($new_filename);

                $file->addValidations(array(
                    new \Upload\Validation\Mimetype(array('image/png', 'image/jpg', 'image/jpeg', 'image/gif')),
                    new \Upload\Validation\Size('5M')
                ));

                $image = array(
                    'etat' => 'new',
                    'name' => $file->getNameWithExtension(),
                    'extension' => $file->getExtension(),
                    'mime' => $file->getMimetype(),
                    'size' => $file->getSize(),
                    'md5' => $file->getMd5(),
                    'dimensions' => $file->getDimensions(),
                    'origine_name' => preg_replace('/\\.[^.\\s]{3,4}$/', '', $_FILES[$nameSlide]['name'])
                );
            }
            try {
                //Ajout de l'image en base
                if ($image['etat'] == 'new') {
                    $file->upload();
                    $insert = new Image;
                    $insert->title = 'Slide à propos';
                    $insert->path = $bddPathSlide;
                    $insert->filename = $image['name'];
                    $insert->md5 = $image ['md5'];
                    if (!$insert->save()) {
                        exit(json_encode(array('etat' => 'err', 'message' => "Une erreur est survenue lors de l'ajout de l'image")));
                    }
                    $image_id = $insert->id;
                } else {
                    $image_id = $image['id'];
                }

                //Mise à jour
                $voc = Vocabulaire::find($id);
                $voc->valeur = $image_id;
                if (!$voc->save()) {
                    $anomalies['slide'] = 'Une erreur est survenue lors de la mise à jour de l\'image de l\'entête';
                }
            } catch (\Exception $e) {
                $anomalies['slide'] = 'Une erreur est survenue lors de l\'ajout de l\'image d\'entête. Veuillez vérifier que l\'image ne dépasse pas la taille de 5Mo et qu\'il s\'agit d\'un format compatible';
            }


        }

        //Vérification Texte massages
        $id = Vocabulaire::TEXTE_EPILATION;
        if (!isset($_POST['vocabulaire_' . $id]) || empty(trim($_POST['vocabulaire_' . $id]))) {
            $anomalies['texte_epilation'] = 'Une description doit être renseignée';
        } else {
            //Mise à jour
            $voc = Vocabulaire::find($id);
            $voc->valeur = trim($_POST['vocabulaire_' . $id]);
            if (!$voc->save()) {
                $anomalies['texte_epilation'] = 'Une erreur est survenue lors de la mise à jour de la description';
            }
        }

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