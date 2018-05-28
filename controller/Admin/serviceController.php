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
            $categorie = Categorie::find($id);
        } catch (\PDOException $exception) {
            \AppController\errorController::error500();
            exit;
        }


        echo $twig->render('Admin/Categorie/edit.twig', array(
            'categorie' => $categorie,
            'vars' => $vars
        ));
    }

    public static function editProcessAction($id)
    {
        $anomalies = array();
        $message = array('type' => 'conf', 'message' => "Les modifications ont bien été prises en compte", 'titre' => "Confirmation");

        $post = array_map('trim', $_POST);

        //Vérification des post
        if (!isset($post['libelle']) || empty($post['libelle'])) {
            $anomalies[] = 'Le libellé doit être renseigné';
        }
        if (!isset($post['description']) || empty($post['description'])) {
            $anomalies[] = 'Une description doit être renseignée';
        }

        //Création du message d'anomalie
        if (!empty($anomalies)) {
            //Présence d'anomalies
            $message = array('type' => 'err', 'message' => "<ul>", 'titre' => "Erreur");
            foreach ($anomalies as $anomalie) {
                $message['message'] .= '<li>' . $anomalie . '</li>';
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
            if (!$update->save()) {
                $message = array('type' => 'err', 'message' => "Une erreur est survenue lors de la mise à jour de la catégorie", 'titre' => "Erreur");
            }

            //Redirection vers la liste des catégorie
            $_SESSION['notification'] = $message;
            header('location: ' . getRouteUrl('admin_categorie'));
            exit;
        }

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
                        new \Upload\Validation\Mimetype(array('image/png', 'image/jpg', 'image/jpeg')),
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
                    $anomalies[] = 'Une erreur est survenue lors de l\'ajout de l\image. Veuillez vérifier que l\'image ne dépasse pas la taille de 5Mo';
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
}


?>