<?php

namespace AppController\Admin;

use \App\Config;
use \App\Database;
use \App\Tables\Categorie;
use App\Tables\Image;
use App\Tables\Vocabulaire;
use \Illuminate\Database\Eloquent\Model;
use Respect\Validation\Validator as v;

class vocabulaireController
{

    public static function indexAction()
    {
        $config = new Config();
        $twig = $config->initTwig();

        try {
            $vocabulaire = Vocabulaire::get()->toArray();
        } catch (\PDOException $exception) {
            \AppController\errorController::error500();
            exit;
        }


        echo $twig->render('Admin/Vocabulaire/information.twig', array(
            'vocabulaire' => $vocabulaire,
        ));
    }

    public static function editAction()
    {
        $anomalies = array();

        $vocabulaires = array();
        foreach ($_POST as $name => $post) {
            if (preg_match('/(vocabulaire_)([0-9]+)(.*)/', $name, $matches) == 1) {
                if (isset($matches[3]) && $matches[3] != '') {
                    $vocabulaires[$matches[2]][ltrim($matches[3], '_')] = $post;
                } else {
                    $vocabulaires[$matches[2]] = $post;
                }

            }
        }
        //Vérification du n° de téléphone
        $id = Vocabulaire::TELEPHONE;
        if (!isset($vocabulaires[$id]) || empty($vocabulaires[$id])) {
            $anomalies[$id] = 'Le n° de téléphone doit être renseigné';
        } else {
            $vocabulaires[$id] = preg_replace('/[^0-9]/', '', $vocabulaires[$id]);
            if (!v::phone()->validate($vocabulaires[$id])) {
                $anomalies[$id] = 'Le n° de téléphone renseigné est incorect';
            } else {
                //Mise à jour
                $voc = Vocabulaire::find($id);
                $voc->valeur = $vocabulaires[$id];
                if (!$voc->save()) {
                    $anomalies[$id] = 'Une erreur est survenue lors de la mise à jour du n° de téléphone';
                }
            }

        }

        //Vérification Adresse
        $id = Vocabulaire::ADRESSE;
        if (!isset($vocabulaires[$id]) || empty($vocabulaires[$id])) {
            $anomalies[$id] = 'Une adresse doit être renseignée';
        } else {
            //Mise à jour
            $voc = Vocabulaire::find($id);
            $voc->valeur = $vocabulaires[$id];
            if (!$voc->save()) {
                $anomalies[$id] = 'Une erreur est survenue lors de la mise à jour de l\'adresse';
            }
        }

        //Vérification E-mail
        $id = Vocabulaire::MAIL;
        if (!isset($vocabulaires[$id]) || empty($vocabulaires[$id])) {
            $anomalies[$id] = 'Une adresse e-mail doit être renseignée';
        } else {
            if (!v::email()->validate($vocabulaires[$id])) {
                $anomalies[$id] = 'L\'adresse e-mail renseignée est incorecte';
            } else {
                //Mise à jour
                $voc = Vocabulaire::find($id);
                $voc->valeur = $vocabulaires[$id];
                if (!$voc->save()) {
                    $anomalies[$id] = 'Une erreur est survenue lors de la mise à jour de l\'adresse';
                }
            }
        }

        //Vérification Facebook lien
        $id = Vocabulaire::FACEBOOK_URL;
        if (!isset($vocabulaires[$id]) || empty($vocabulaires[$id])) {
            $anomalies[$id] = 'Un lien Facebook doit être renseigné';
        } else {
            //Mise à jour
            $voc = Vocabulaire::find($id);
            $voc->valeur = $vocabulaires[$id];
            if (!$voc->save()) {
                $anomalies[$id] = 'Une erreur est survenue lors de la mise à jour du lien Facebook';
            }
        }

        //Vérification Texte massages
        $id = Vocabulaire::TEXTE_MASSAGES;
        if (!isset($vocabulaires[$id]) || empty($vocabulaires[$id])) {
            $anomalies[$id] = 'Une adresse doit être renseignée';
        } else {
            //Mise à jour
            $voc = Vocabulaire::find($id);
            $voc->valeur = $vocabulaires[$id];
            if (!$voc->save()) {
                $anomalies[$id] = 'Une erreur est survenue lors de la mise à jour de l\'adresse';
            }
        }


        //Vérification Texte produits
        $id = Vocabulaire::TEXTE_PRODUITS;
        if (!isset($vocabulaires[$id]) || empty($vocabulaires[$id])) {
            $anomalies[$id] = 'Une adresse doit être renseignée';
        } else {
            //Mise à jour
            $voc = Vocabulaire::find($id);
            $voc->valeur = $vocabulaires[$id];
            if (!$voc->save()) {
                $anomalies[$id] = 'Une erreur est survenue lors de la mise à jour de l\'adresse';
            }
        }


        //Vérification des horaires
        $id = Vocabulaire::HORAIRES;
        $valeur = array();
        foreach (Vocabulaire::HORAIRES_JOURS as $index => $jour) {
            $valeur[$index] = "";
            $erreur = false;

            //On vérifie que les 2 horaires sont bien définis
            if (!isset($vocabulaires[$id][$index . "_debut"]) || !isset($vocabulaires[$id][$index . "_fin"])) {
                $anomalies[$id . "_" . $index . "_debut"] = 'Erreur lors de la vérification des horaires du ' . $jour;
                $erreur = true;
                continue;
            } elseif (empty($vocabulaires[$id][$index . "_debut"]) && !empty($vocabulaires[$id][$index . "_fin"])) {
                $anomalies[$id . "_" . $index . "_debut"] = 'L\'horaire d\'ouverture du ' . $jour . ' est vide';
                $erreur = true;
                continue;
            } elseif (!empty($vocabulaires[$id][$index . "_debut"]) && empty($vocabulaires[$id][$index . "_fin"])) {
                $anomalies[$id . "_" . $index . "_debut"] = 'L\'horaire de fermeture du ' . $jour . ' est vide';
                $erreur = true;
                continue;
            }

            foreach (['debut' => 'ouverture', 'fin' => 'fermeture'] as $typePeriode => $libPeriode) {
                if (!isset($vocabulaires[$id][$index . "_" . $typePeriode])) {
                    $anomalies[$id . "_" . $index . "_debut"] = 'Erreur lors de la vérification de l\'horaire du ' . $jour . ' (' . $libPeriode . ')';
                    $erreur = true;
                    continue;
                } else {
                    if (!empty($vocabulaires[$id][$index . "_" . $typePeriode])) {
                        if (!timeVerification($vocabulaires[$id][$index . "_" . $typePeriode])) {
                            $anomalies[$id . "_" . $index . "_" . $typePeriode] = 'L\'horaire du ' . $jour . ' (' . $libPeriode . ') est incorrecte';
                            $erreur = true;
                            continue;
                        }
                    }
                }
                if (!empty($valeur[$index])) {
                    $valeur[$index] .= '-';
                }
                $valeur[$index] .= $vocabulaires[$id][$index . "_" . $typePeriode];
            }
        }
        if ($erreur === false) {
            //Aucune erreur dans les horaires, on met à jour en bdd
            $voc = Vocabulaire::find($id);
            $voc->valeur = json_encode($valeur);
            if (!$voc->save()) {
                $anomalies[$id] = 'Une erreur est survenue lors de la mise à jour des horaires';
            }
        }

        //Création du message d'anomalie
        if (!empty($anomalies)) {
            //Présence d'anomalies
            $message = '<ul>';
            foreach ($anomalies as $anomalie) {
                $message .= '<li>' . $anomalie . '</li>';
            }
            $message .= "</ul>";
            exit(json_encode(array('etat' => 'err', 'message' => $message)));
        }

        $_SESSION['notification'] = array('type' => 'conf', 'message' => "Les modifications ont bien été prises en compte", 'titre' => "Confirmation");
        echo json_encode(array('etat' => 'conf', 'url' => getRouteUrl('admin_information')));
        exit();
    }

    public static function aproposAction()
    {
        $config = new Config();
        $twig = $config->initTwig();

        $description = Vocabulaire::find(Vocabulaire::TEXTE_APROPOS);

        //Récupération de l'image du slider
        $slide = false;
        $voc_slide = Vocabulaire::find(Vocabulaire::SLIDER_APROPOS);
        if (!empty($voc_slide->valeur)) {
            $slide = Image::find($voc_slide->valeur);
            if (empty($slide)) {
                $slide = false;
            }
        }

        //Récupérations des différentes images
        $voc_images = Vocabulaire::find(Vocabulaire::IMAGES_APROPOS);
        $images = array();
        if (!empty($voc_images->valeur)) {
            foreach (unserialize($voc_images->valeur) as $id){
                $images[] = Image::find($id);
            }
        }

        echo $twig->render('Admin/Vocabulaire/apropos.twig', array(
            'description' => $description,
            'slide' => $slide,
            'images' => $images,
        ));
    }

    public static function aproposEditAction()
    {
        $config = new Config();
        $anomalies = array();

        $nameSlide = 'slide';
        $nameImages = 'image';
        $path = $config->getGlobal('FILE_ROOT') . '/src/img/';
        $bddPathSlide = 'slide/';
        $bddPathImages = 'a_propos/';

        //Vérification Texte massages
        $id = Vocabulaire::TEXTE_APROPOS;
        if (!isset($_POST['vocabulaire_' . $id]) || empty(trim($_POST['vocabulaire_' . $id]))) {
            $anomalies[$id] = 'Une description doit être renseignée';
        } else {
            //Mise à jour
            $voc = Vocabulaire::find($id);
            $voc->valeur = trim($_POST['vocabulaire_' . $id]);
            if (!$voc->save()) {
                $anomalies[$id] = 'Une erreur est survenue lors de la mise à jour de la description';
            }
        }

        if (isset($_FILES[$nameSlide]) && !empty($_FILES[$nameSlide]) && !empty($_FILES[$nameSlide]['tmp_name'])) {
            createDirIfNotExist($path . $bddPathSlide);
            $id = Vocabulaire::SLIDER_APROPOS;

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
                    $anomalies[$id] = 'Une erreur est survenue lors de la mise à jour de l\'image de l\'entête';
                }
            } catch (\Exception $e) {
                $anomalies[$id] = 'Une erreur est survenue lors de l\'ajout de l\'image d\'entête. Veuillez vérifier que l\'image ne dépasse pas la taille de 5Mo et qu\'il s\'agit d\'un format compatible';
            }


        }

        //Liste des images
        $id = Vocabulaire::IMAGES_APROPOS;
        //Récupération des images existantes
        $voc_images = Vocabulaire::find($id);
        $imagesExistantes = array();
        if (!empty($voc_images->valeur)) {
            $imagesExistantes = unserialize($voc_images->valeur);
        }
        $nbImages = count($imagesExistantes);

        //Upload des images
        if (empty($_FILES[$nameImages]) || !isset($_FILES[$nameImages]['tmp_name'][0]) || empty($_FILES[$nameImages]['tmp_name'][0])) {
            //Aucune image à upload
            if ($nbImages == 0){
                $anomalies[$id] = 'Aucune image sélectionnée';
            }
        } elseif (empty($anomalies)) {
            createDirIfNotExist($path . $bddPathImages);
            //Images à upload
            $storage = new \Upload\Storage\FileSystem($path . $bddPathImages);
            $file = new \Upload\File($nameImages, $storage);
            foreach ($_FILES[$nameImages]['tmp_name'] as $i => $tmp) {
                if (empty($tmp)) continue;

                //On vérifie si l'image existe déjà en base
                $image_existe = Image::where('md5', $file[$i]->getMd5())->get();
                if ($image_existe->isNotEmpty()) {
                    $images[] = array(
                        'etat' => 'exist',
                        'id' => $image_existe->first()->id
                    );
                    unset($file[$i]);
                } else {
                    $new_filename = uniqid();
                    $file[$i]->setName($new_filename);

                    $file->addValidations(array(
                        new \Upload\Validation\Mimetype(array('image/png', 'image/jpg', 'image/jpeg', 'image/gif')),
                        new \Upload\Validation\Size('5M')
                    ));

                    $images[] = array(
                        'etat' => 'new',
                        'name' => $file[$i]->getNameWithExtension(),
                        'extension' => $file[$i]->getExtension(),
                        'mime' => $file[$i]->getMimetype(),
                        'size' => $file[$i]->getSize(),
                        'md5' => $file[$i]->getMd5(),
                        'dimensions' => $file[$i]->getDimensions(),
                        'origine_name' => preg_replace('/\\.[^.\\s]{3,4}$/', '', $_FILES[$nameImages]['name'][$i])
                    );
                }
                $nbImages++;
            }
            if (!empty($images) && empty($anomalies)) {
                try {
                    $file->upload();
                    //Ajout des images et du lien
                    foreach ($images as $index => $image) {
                        //On vérifie si l'image existe déjà
                        if ($image['etat'] == 'new') {
                            $insert = new Image;
                            $insert->title = $image['origine_name'];
                            $insert->path = $bddPathImages;
                            $insert->filename = $image['name'];
                            $insert->md5 = $image ['md5'];
                            if (!$insert->save()) {
                                echo json_encode(array('etat' => 'err', 'message' => "Une erreur est survenue lors de l'ajout d'une image"));
                                exit();
                            }
                            $image_id = $insert->id;
                        } else {
                            $image_id = $image['id'];
                        }

                        if (!in_array($image_id, $imagesExistantes)){
                            $imagesExistantes[] = $image_id;
                        }
                    }

                    //Update de la liste des images
                    $voc_images->valeur = serialize($imagesExistantes);
                    if (!$voc_images->save()) {
                        $anomalies[$id] = 'Une erreur est survenue lors de la mise à jour des images';
                    }
                } catch (\Exception $e) {
                    $errors = $file->getErrors();
                    $anomalies[] = 'Une erreur est survenue lors de l\'ajout d\'une image. Veuillez vérifier que l\'image ne dépasse pas la taille de 5Mo et qu\'il s\'agit d\'un format compatible';
                }
            }
        }


        //Création du message d'anomalie
        if (!empty($anomalies)) {
            //Présence d'anomalies
            $message = '<ul>';
            foreach ($anomalies as $anomalie) {
                $message .= '<li>' . $anomalie . '</li>';
            }
            $message .= "</ul>";
            exit(json_encode(array('etat' => 'err', 'message' => $message)));
        }

        $_SESSION['notification'] = array('type' => 'conf', 'message' => "Les modifications ont bien été prises en compte", 'titre' => "Confirmation");
        echo json_encode(array('etat' => 'conf', 'url' => getRouteUrl('admin_apropos')));
        exit();
    }

    public static function imageAproposDeleteAction($id = ''){
        if (!isset($_POST['id']) && empty($_POST['id']) && empty($id)){
            echo json_encode(array('etat' => 'err', 'message' => 'Une erreur est survenue ['.__LINE__.']'));
            exit;
        }
        if (isset($_POST['id']) && empty($id)){
            $id = $_POST['id'];
        }

        //Récupération de la liste des images
        $voc_images = Vocabulaire::find(Vocabulaire::IMAGES_APROPOS);
        $id_images = unserialize($voc_images->valeur);
        foreach ($id_images as $key=>$id_image){
            if ($id_image == $id){
                unset($id_images[$key]);
                break;
            }
        }

        if (empty($id_images)){
            $voc_images->valeur = null;
        } else {
            $voc_images->valeur = serialize($id_images);
        }
        if (!$voc_images->save()) {
            exit(json_encode(array('etat' => 'err', 'message' => "Erreur lors de la supression de l'image")));
        }

        echo json_encode(array('etat' => 'conf', 'message' => "L'image a bien été supprimée"));
    }
}


?>