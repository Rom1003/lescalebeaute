<?php

namespace AppController\Admin;

use \App\Config;
use \App\Database;
use \App\Tables\Categorie;
use App\Tables\Service;
use App\Tables\Image;
use App\Tables\ServiceImage;
use App\Tables\ServicePrix;
use App\Tables\Vocabulaire;
use \Illuminate\Database\Eloquent\Model;
use Respect\Validation\Validator as v;

class serviceController
{

    public static function indexAction($categorie_id = '', $vars = array())
    {
        $config = new Config();
        $twig = $config->initTwig();

        echo $twig->render('Admin/Service/list.twig', array(
            'vars' => $vars,
            'categorie_id' => $categorie_id
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
        if (!isset($_POST['categorie_id']) || empty($_POST['categorie_id'])) {
            $_POST['categorie_id'] = '';
        }
        $services = Service::pagination($_POST['page'], $_POST['nbParPage'], $_POST['categorie_id']);

//        echo $services;

        echo json_encode(array('etat' => 'conf', 'html' => $services));
    }

    public static function editFormAction($id, $vars = array())
    {
        $config = new Config();
        $twig = $config->initTwig();

        try {
            $service = Service::detail($id);
            if (!$service || empty($service)){
                \AppController\errorController::error404();
                exit;
            }
        } catch (\PDOException $exception) {
            \AppController\errorController::error500();
            exit;
        }
        $categories = Categorie::getMenu(false, false);


        echo $twig->render('Admin/Service/edit.twig', array(
            'service' => $service,
            'categories' => $categories,
            'vars' => $vars
        ));
    }

    public static function editProcessAction($id)
    {
        $config = new Config();

        $maxImages = 6;
        $message = "Les modifications ont bien été prises en compte";
        $name = 'image';

        $nbImages = 0;
        $anomalies = array();
        $path = $config->getGlobal('FILE_ROOT') . '/src/img/';
        $bddPath = 'services/'.date('Y') . '/' . date('m') . '/' . date('d') . '/';
        createDirIfNotExist($path . $bddPath);
        $post = trimArray($_POST);
        $images = array();
        $tarifsDelete = array();

        //Récupération des données du service
        $service = Service::find($id);
        if ($service === false || empty($service)){
            echo json_encode(array('etat' => 'err', 'message' => 'Ce service n\'existe pas'));
            exit;
        }

        //Récupération des tarifs
        $tarifsExistants = ServicePrix::where("service_id", $id)->get();
        if (!$tarifsExistants){
            echo json_encode(array('etat' => 'err', 'message' => 'Une erreur est survenue ['.__LINE__.']'));
            exit;
        } elseif (!empty($tarifsExistants)){
            foreach ($tarifsExistants as $tarifsExistant){
                $tarifsDelete[] = $tarifsExistant->id;
            }
        }

        //Récupération des images existantes
        $imagesExistantes = ServiceImage::where('service_id', $id)->get();
        if (!$imagesExistantes->isEmpty()){
            $nbImages = $imagesExistantes->count();
        }

        //Vérification des post

        if (empty($_POST)){
            $anomalies[] = 'Une erreur inatendue est survenue. Si des images ont été ajoutés, veuillez vérifier qu\'elles ne dépassent pas 8Mo';
        }

        if (!isset($post['libelle']) || empty($post['libelle'])) {
            $anomalies[] = 'Un libellé doit être renseigné';
        }
        if (!isset($post['description']) || empty($post['description'])) {
            $anomalies[] = 'Une description doit être renseignée';
        }
        if (!isset($post['categorie']) || empty($post['categorie'])) {
            $anomalies[] = 'Une catégorie doit être sélectionnée';
        } else {
            //Vérifie que la catégorie existe
            $categorie = Categorie::where('id', $post['categorie'])->where('niveau', 1)->where('editable', 1)->get()->first();
            if ($categorie === false || empty($categorie)) {
                $anomalies[] = 'Impossible de retrouver la catégorie sélectionnée';
            }
        }

        //Vérification des tarifs
        if (!isset($post['tarif_type']) || !in_array($post['tarif_type'], array(1, 2))) {
            $anomalies[] = 'Veuillez selectionner un type de tarif';
        }

        if ($post['tarif_type'] == '1') {
            //Tarif sur la durée
            if (!isset($post['duree']) || empty($post['duree']) || !isset($post['tarif']) || empty($post['tarif'])) {
                $anomalies[] = 'Un tarif doit être renseigné';
            }
            foreach ($post['duree'] as $duree) {
                if (empty($duree)) {
                    $anomalies[] = 'Un tarif n\'a pas de durée renseignée';
                    break;
                }
            }
            foreach ($post['tarif'] as $tarif) {
                if (empty($tarif)) {
                    $anomalies[] = 'Un tarif n\'a pas de prix renseigné';
                    break;
                }
                if (!v::floatVal()->validate($tarif) || $tarif <= 0) {
                    $anomalies[] = 'Un tarif renseigné est incorrect';
                    break;
                }
            }
            if (count($post['tarif']) != count($post['duree'])) {
                $anomalies[] = 'Une erreur est survenue.';
            }
        } else {
            //Tarif fixe
            if (!isset($post['tarif_fixe']) || empty($post['tarif_fixe'])) {
                $anomalies[] = 'Un tarif doit être renseigné';
            }
            if (!v::floatVal()->validate($post['tarif_fixe']) || $post['tarif_fixe'] <= 0) {
                $anomalies[] = 'Le tarif renseigné est incorrect';
            }
        }


        //Upload des images
        if (empty($_FILES[$name]) || !isset($_FILES[$name]['tmp_name'][0]) || empty($_FILES[$name]['tmp_name'][0])) {
            //Aucune image à upload
            if ($nbImages == 0){
                $anomalies[] = 'Aucune image sélectionnée';
            }
        } elseif (empty($anomalies)) {
            //Images à upload
            $storage = new \Upload\Storage\FileSystem($path . $bddPath);
            $file = new \Upload\File($name, $storage);
            foreach ($_FILES[$name]['tmp_name'] as $i => $tmp) {
                if (empty($tmp)) continue;
                if ($nbImages >= $maxImages){
                    $anomalies[] = 'Le nombre maximum d\'images est de '.$nbImages;
                    break;
                }

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
                        'origine_name' => preg_replace('/\\.[^.\\s]{3,4}$/', '', $_FILES[$name]['name'][$i])
                    );
                }
                $nbImages++;
            }
            if (!empty($images) && empty($anomalies)) {
                try {
                    $file->upload();
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
        } else {
            //Tout est bon, on modifie en base

            //Update du service
            $service->libelle = $post['libelle'];
            $service->description = $post['description'];
            $service->categorie_id = $post['categorie'];

            if (!$service->save()) {
                echo json_encode(array('etat' => 'err', 'message' => "Une erreur est survenue lors de la mise à jour du service"));
                exit();
            }

            //Ajout du/des tarifs
            if ($post['tarif_type'] == '1') {
                //Tarif sur la durée
                foreach ($post['duree'] as $index=>$duree){
                    //Récupération du prix associé
                    $prix = $post['tarif'][$index];

                    $tarif = new ServicePrix();
                    $tarif->prix = $prix;
                    $tarif->libelle = $duree;
                    $tarif->type = 1;
                    $tarif->service_id = $service->id;
                    if (!$tarif->save()) {
                        echo json_encode(array('etat' => 'err', 'message' => "Une erreur est survenue lors de l'ajout des tarifs"));
                        exit();
                    }
                }
            } else {
                //Tarif fixe
                $tarif = new ServicePrix();
                $tarif->prix = $post['tarif_fixe'];
                $tarif->type = 2;
                $tarif->service_id = $service->id;
                if (!$tarif->save()) {
                    echo json_encode(array('etat' => 'err', 'message' => "Une erreur est survenue lors de l'ajout du tarif"));
                    exit();
                }
            }

            //Les tarifs ont été ajoutés, on supprime les anciens
            foreach ($tarifsDelete as $td){
                ServicePrix::destroy($td);
            }

            //Ajout des images et du lien service<->image
            foreach ($images as $index => $image) {
                //On vérifie si l'image existe déjà
                if ($image['etat'] == 'new') {
                    $insert = new Image;
                    $insert->title = $image['origine_name'];
                    $insert->path = $bddPath;
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

                //Ajout du lien avec le service
                $serviceImage = new ServiceImage;
                $serviceImage->service_id = $service->id;
                $serviceImage->image_id = $image_id;
                $serviceImage->actif = 1;
                if (!$serviceImage->save()) {
                    //Supression de tous les enregistrements précédents
                    echo json_encode(array('etat' => 'err', 'message' => "Une erreur est survenue lors de l'ajout d'une image"));
                    exit();
                }
            }

            //Redirection vers la liste des services
            $_SESSION['notification'] = array('type' => 'conf', 'message' => $message, 'titre' => "Confirmation");
            echo json_encode(array('etat' => 'conf', 'url' => getRouteUrl('admin_service_edit', ['id' => $service->id])));
            exit;
        }

        echo json_encode(array('etat' => 'err', 'message' => $message));


    }

    public static function newFormAction($vars = array())
    {
        $config = new Config();
        $twig = $config->initTwig();

        $categories = Categorie::getMenu(false, false);

        echo $twig->render('Admin/Service/new.twig', array(
            'vars' => $vars,
            'categories' => $categories
        ));
    }

    public static function newProcessAction()
    {
        $config = new Config();

        $name = 'image';
        $path = $config->getGlobal('FILE_ROOT') . '/src/img/';
        $bddPath = 'services/'.date('Y') . '/' . date('m') . '/' . date('d') . '/';
        createDirIfNotExist($path . $bddPath);

        $anomalies = array();
        $post = trimArray($_POST);
        $images = array();

        if (empty($_POST)){
            $anomalies[] = 'Une erreur inatendue est survenue. Si des images ont été ajoutés, veuillez vérifier qu\'elles ne dépassent pas 8Mo';
        }

        //Vérification des post
        if (!isset($post['libelle']) || empty($post['libelle'])) {
            $anomalies[] = 'Un libellé doit être renseigné';
        }
        if (!isset($post['description']) || empty($post['description'])) {
            $anomalies[] = 'Une description doit être renseignée';
        }
        if (!isset($post['categorie']) || empty($post['categorie'])) {
            $anomalies[] = 'Une catégorie doit être sélectionnée';
        } else {
            //Vérifie que la catégorie existe
            $categorie = Categorie::where('id', $post['categorie'])->where('niveau', 1)->where('editable', 1)->get()->first();
            if ($categorie === false || empty($categorie)) {
                $anomalies[] = 'Impossible de retrouver la catégorie sélectionnée';
            }
        }

        //Vérification des tarifs
        if (!isset($post['tarif_type']) || !in_array($post['tarif_type'], array(1, 2))) {
            $anomalies[] = 'Veuillez selectionner un type de tarif';
        }

        if ($post['tarif_type'] == '1') {
            //Tarif sur la durée
            if (!isset($post['duree']) || empty($post['duree']) || !isset($post['tarif']) || empty($post['tarif'])) {
                $anomalies[] = 'Un tarif doit être renseigné';
            }
            foreach ($post['duree'] as $duree) {
                if (empty($duree)) {
                    $anomalies[] = 'Un tarif n\'a pas de durée renseignée';
                    break;
                }
            }
            foreach ($post['tarif'] as $tarif) {
                if (empty($tarif)) {
                    $anomalies[] = 'Un tarif n\'a pas de prix renseigné';
                    break;
                }
                //Remplacement des "," par des "."
                $tarif = str_replace(",", ".", $tarif);
                if (!v::floatVal()->validate($tarif) || $tarif <= 0) {
                    $anomalies[] = 'Un tarif renseigné est incorrect';
                    break;
                }
            }
            if (count($post['tarif']) != count($post['duree'])) {
                $anomalies[] = 'Une erreur est survenue.';
            }
        } else {
            //Tarif fixe
            if (!isset($post['tarif_fixe']) || empty($post['tarif_fixe'])) {
                $anomalies[] = 'Un tarif doit être renseigné';
            }
            //Remplacement des "," par des "."
            $post['tarif_fixe'] = str_replace(",", ".", $post['tarif_fixe']);
            if (!v::floatVal()->validate($post['tarif_fixe']) || $post['tarif_fixe'] <= 0) {
                $anomalies[] = 'Le tarif renseigné est incorrect';
            }
        }


        //Upload des images
        if (empty($_FILES[$name]) || !isset($_FILES[$name]['tmp_name'][0]) || empty($_FILES[$name]['tmp_name'][0])) {
            $anomalies[] = 'Aucune image sélectionnée';
        } elseif (empty($anomalies)) {
            $storage = new \Upload\Storage\FileSystem($path . $bddPath);
            $file = new \Upload\File($name, $storage);
            foreach ($_FILES[$name]['tmp_name'] as $i => $tmp) {
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
                        'origine_name' => preg_replace('/\\.[^.\\s]{3,4}$/', '', $_FILES[$name]['name'][$i])
                    );
                }


            }
            if (!empty($images)) {
                try {
                    $file->upload();
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
        } else {
            //Tout est bon, on ajoute en base

            //Ajout du service
            $service = new Service;
            $service->libelle = $post['libelle'];
            $service->description = $post['description'];
            $service->categorie_id = $post['categorie'];

            if (!$service->save()) {
                echo json_encode(array('etat' => 'err', 'message' => "Une erreur est survenue lors de l'ajout du service"));
                exit();
            }

            //Ajout du/des tarifs
            if ($post['tarif_type'] == '1') {
                //Tarif sur la durée
                foreach ($post['duree'] as $index=>$duree){
                    //Récupération du prix associé
                    $prix = $post['tarif'][$index];

                    $tarif = new ServicePrix();
                    $tarif->prix = $prix;
                    $tarif->libelle = $duree;
                    $tarif->type = 1;
                    $tarif->service_id = $service->id;
                    if (!$tarif->save()) {
                        Service::where('id', $service->id)->delete();
                        echo json_encode(array('etat' => 'err', 'message' => "Une erreur est survenue lors de l'ajout des tarifs"));
                        exit();
                    }
                }
            } else {
                //Tarif fixe
                $tarif = new ServicePrix();
                $tarif->prix = $post['tarif_fixe'];
                $tarif->type = 2;
                $tarif->service_id = $service->id;
                if (!$tarif->save()) {
                    Service::where('id', $service->id)->delete();
                    echo json_encode(array('etat' => 'err', 'message' => "Une erreur est survenue lors de l'ajout du tarif"));
                    exit();
                }
            }

            //Ajout des images et du lien service<->image
            foreach ($images as $index => $image) {
                //On vérifie si l'image existe déjà
                if ($image['etat'] == 'new') {
                    $insert = new Image;
                    $insert->title = $image['origine_name'];
                    $insert->path = $bddPath;
                    $insert->filename = $image['name'];
                    $insert->md5 = $image ['md5'];
                    if (!$insert->save()) {
                        Service::where('id', $service->id)->delete();
                        echo json_encode(array('etat' => 'err', 'message' => "Une erreur est survenue lors de l'ajout d'une image"));
                        exit();
                    }
                    $image_id = $insert->id;
                } else {
                    $image_id = $image['id'];
                }

                //Ajout du lien avec le service
                $serviceImage = new ServiceImage;
                $serviceImage->service_id = $service->id;
                $serviceImage->image_id = $image_id;
                $serviceImage->actif = 1;
                if (!$serviceImage->save()) {
                    //Supression de tous les enregistrements précédents
                    ServiceImage::where('service_id', $service->id)->delete();
                    Service::where('id', $service->id)->delete();
                    echo json_encode(array('etat' => 'err', 'message' => "Une erreur est survenue lors de l'ajout d'une image"));
                    exit();
                }
            }

            //Redirection vers la liste des services
            $_SESSION['notification'] = array('type' => 'conf', 'message' => "Le service a bien été ajouté", 'titre' => "Confirmation");
            echo json_encode(array('etat' => 'conf', 'url' => getRouteUrl('admin_service')));
            exit;
        }

        echo json_encode(array('etat' => 'err', 'message' => $message));

    }

    public static function imageDeleteAction($id = ''){
        if (!isset($_POST['id']) && empty($_POST['id']) && empty($id)){
            echo json_encode(array('etat' => 'err', 'message' => 'Une erreur est survenue ['.__LINE__.']'));
            exit;
        }
        if (isset($_POST['id']) && empty($id)){
            $id = $_POST['id'];
        }

        $delete = ServiceImage::where('id', $id)->delete();
        if ($delete != 1){
            echo json_encode(array('etat' => 'err', 'message' => 'Une erreur est survenue ['.__LINE__.']'));
            exit;
        }

        echo json_encode(array('etat' => 'conf', 'message' => "L'image a bien été supprimée"));
    }
}


?>