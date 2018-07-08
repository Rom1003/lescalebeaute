<?php

namespace AppController\Admin;

use \App\Config;
use \App\Database;
use \App\Tables\Categorie;
use \App\Tables\Image;
use App\Tables\Vocabulaire;
use \Illuminate\Database\Eloquent\Model;
use Respect\Validation\Validator as v;

class categorieController
{

    public static function indexAction($vars = array())
    {
        $config = new Config();
        $twig = $config->initTwig();

        $categories = Categorie::getMenu(false, true);

        echo $twig->render('Admin/Categorie/list.twig', array(
            'categories' => $categories,
            'vars' => $vars
        ));
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
        $config = new Config();
        $name = 'image';
        $path = $config->getGlobal('FILE_ROOT') . '/src/img/';
        $bddPath = 'categorie/';
        createDirIfNotExist($path . $bddPath);

        $anomalies = array();
        $message = array('type' => 'conf', 'message' => "Les modifications ont bien été prises en compte", 'titre' => "Confirmation");

        $post = array_map('trim', $_POST);

        if (empty($_POST)) {
            $anomalies[] = 'Une erreur inatendue est survenue. Si des images ont été ajoutés, veuillez vérifier qu\'elles ne dépassent pas 8Mo';
        }

        //Vérification des post
        if (!isset($post['libelle']) || empty($post['libelle'])) {
            $anomalies[] = 'Le libellé doit être renseigné';
        }
        if (!isset($post['description']) || empty($post['description'])) {
            $anomalies[] = 'Une description doit être renseignée';
        }
        if (!isset($_FILES[$name]) || empty($_FILES[$name]) || empty($_FILES[$name]['tmp_name'])) {
            $anomalies[] = "Veuillez sélectionner une image";
        }

        //Ajout de l'image si aucune erreur
        if (empty($anomalies)) {
            $storage = new \Upload\Storage\FileSystem($path . $bddPath);
            $file = new \Upload\File($name, $storage);

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
                    'origine_name' => preg_replace('/\\.[^.\\s]{3,4}$/', '', $_FILES[$name]['name'])
                );

                try {
                    $file->upload();
                } catch (\Exception $e) {
                    $anomalies[] = 'Une erreur est survenue lors de l\'ajout d\'une image. Veuillez vérifier que l\'image ne dépasse pas la taille de 5Mo et qu\'il s\'agit d\'un format compatible';
                }

                //Ajout de l'image en base
                if ($image['etat'] == 'new') {
                    $insert = new Image;
                    $insert->title = $image['origine_name'];
                    $insert->path = $bddPath;
                    $insert->filename = $image['name'];
                    $insert->md5 = $image ['md5'];
                    if (!$insert->save()) {
                        $anomalies[] = "Une erreur est survenue lors de l'ajout de l'image";
                    } else {
                        $image_id = $insert->id;
                    }

                } else {
                    $image_id = $image['id'];
                }
            }
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
            $update->image_id = $image_id;
            if (!$update->save()) {
                $message = array('type' => 'err', 'message' => "Une erreur est survenue lors de la mise à jour de la catégorie", 'titre' => "Erreur");
            }

            //Redirection vers la liste des catégorie
            $_SESSION['notification'] = $message;
            unset($_POST);
            header('location: ' . getRouteUrl('admin_categorie'));
            exit;
        }

    }

    public static function newFormAction($categorie_id, $vars = array())
    {
        $config = new Config();
        $twig = $config->initTwig();

        //Vérifie que la catégorie existe
        $categorie = Categorie::where('id', $categorie_id)->where('niveau', 0)->where('editable', 1)->get()->first();
        if ($categorie === false || empty($categorie)) {
            self::indexAction(array('notification' => array('type' => 'err', 'message' => 'Impossible d\'ajouter une sous catégorie à cette catégorie', 'titre' => 'Erreur')));
            exit;
        }

        echo $twig->render('Admin/Categorie/new.twig', array(
            'vars' => $vars,
            'categorie' => $categorie
        ));
    }

    public static function newProcessAction($categorie_id)
    {
        $config = new Config();
        $name = 'image';
        $path = $config->getGlobal('FILE_ROOT') . '/src/img/';
        $bddPath = 'categorie/';
        createDirIfNotExist($path . $bddPath);
        $message = array('type' => 'conf', 'message' => "La catégorie a bien été ajoutée", 'titre' => "Confirmation");

        $anomalies = array();

        $post = array_map('trim', $_POST);

        if (empty($_POST)) {
            $anomalies[] = 'Une erreur inatendue est survenue. Si des images ont été ajoutés, veuillez vérifier qu\'elles ne dépassent pas 8Mo';
        }

        //Vérification des post
        if (!isset($post['libelle']) || empty($post['libelle'])) {
            $anomalies[] = 'Le libellé doit être renseigné';
        }
        if (!isset($post['description']) || empty($post['description'])) {
            $anomalies[] = 'Une description doit être renseignée';
        }

        if (!isset($_FILES[$name]) || empty($_FILES[$name]) || empty($_FILES[$name]['tmp_name'])) {
            $anomalies[] = "Veuillez sélectionner une image";
        }

        if (empty($anomalies)) {
            //Ajout de l'image
            $storage = new \Upload\Storage\FileSystem($path . $bddPath);
            $file = new \Upload\File($name, $storage);

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
                    'origine_name' => preg_replace('/\\.[^.\\s]{3,4}$/', '', $_FILES[$name]['name'])
                );

                try {
                    $file->upload();
                } catch (\Exception $e) {
                    $anomalies[] = 'Une erreur est survenue lors de l\'ajout d\'une image. Veuillez vérifier que l\'image ne dépasse pas la taille de 5Mo et qu\'il s\'agit d\'un format compatible';
                }

                //Ajout de l'image en base
                if ($image['etat'] == 'new') {
                    $insert = new Image;
                    $insert->title = $image['origine_name'];
                    $insert->path = $bddPath;
                    $insert->filename = $image['name'];
                    $insert->md5 = $image ['md5'];
                    if (!$insert->save()) {
                        $anomalies[] = "Une erreur est survenue lors de l'ajout de l'image";
                    } else {
                        $image_id = $insert->id;
                    }

                } else {
                    $image_id = $image['id'];
                }
            }
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
            self::editFormAction($categorie_id, array(
                'post' => $post
            ));
        } else {
            //Tout est bon, on retire les post et on met à jour
            //Vérifie que la catégorie existe
            $categorie = Categorie::where('id', $categorie_id)->where('niveau', 0)->where('editable', 1)->get()->first();
            if ($categorie === false || empty($categorie)) {
                self::indexAction(array('notification' => array('type' => 'err', 'message' => 'Impossible d\'ajouter une sous catégorie à cette catégorie', 'titre' => 'Erreur')));
                exit;
            }

            //Récupération de la dernière sous-catégorie
            $last_categorie = Categorie::where('categorie_id', $categorie_id)->where('niveau', 1)->orderBy('ordre', 'desc')->get()->first();
            if ($last_categorie === false) {
                self::indexAction(array('notification' => array('type' => 'err', 'message' => 'Impossible d\'ajouter une sous catégorie à cette catégorie', 'titre' => 'Erreur')));
                exit;
            } elseif (empty($last_categorie)) {
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
            $insert->image_id = $image_id;
            if (!$insert->save()) {
                $message = array('type' => 'err', 'message' => "Une erreur est survenue lors de l\'ajour de la catégorie", 'titre' => "Erreur");
            }

            //Ajout de l'image à la catégorie

            //Redirection vers la liste des catégorie
            $_SESSION['notification'] = $message;
            unset($_POST);
            header('location: ' . getRouteUrl('admin_categorie'));
            exit;
            //Redirection vers la liste des catégorie
        }

    }

    public static function slideFormAction($categorie_id)
    {
        $config = new Config();
        $twig = $config->initTwig();

        try {
            $categorie = Categorie::find($categorie_id);
            if (empty($categorie) || $categorie === false) {
                \AppController\errorController::error404();
                exit;
            }
        } catch (\PDOException $exception) {
            \AppController\errorController::error500();
            exit;
        }

        $slide = null;
        if (!empty($categorie->slide_image_id)){
            //Récupération de la slide actuelle
            $slide = Image::find($categorie->slide_image_id);
        }

        echo $twig->render('Admin/Categorie/slide.twig', array(
            'categorie' => $categorie,
            'slide' => $slide
        ));
    }

    public static function slideProcessAction($categorie_id)
    {
        $config = new Config();

        $name = 'image';
        $path = $config->getGlobal('FILE_ROOT') . '/src/img/';
        $bddPath = 'slider/';
        createDirIfNotExist($path . $bddPath);

        if (!isset($_FILES[$name]) || empty($_FILES[$name]) || empty($_FILES[$name]['tmp_name'])) {
            $_SESSION['notification'] = array('type' => 'err', 'message' => "Veuillez sélectionner une image", 'titre' => "Erreur");
            header('location: ' . getRouteUrl('admin_categorie_slide', ['categorie_id' => $categorie_id]));
            exit();
        }

        //Récupération de la catégorie
        $categorie = Categorie::find($categorie_id);
        if (empty($categorie)){
            $_SESSION['notification'] = array('type' => 'err', 'message' => "La catégorie n'existe pas", 'titre' => "Erreur");
            header('location: ' . getRouteUrl('admin_categorie_slide', ['categorie_id' => $categorie_id]));
            exit();
        }

        //Ajout de l'image
        $storage = new \Upload\Storage\FileSystem($path . $bddPath);
        $file = new \Upload\File($name, $storage);

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
                'origine_name' => preg_replace('/\\.[^.\\s]{3,4}$/', '', $_FILES[$name]['name'])
            );

            try {
                $file->upload();
            } catch (\Exception $e) {
                $errors = $file->getErrors();
                $_SESSION['notification'] = array('type' => 'err', 'message' => 'Une erreur est survenue lors de l\'ajout d\'une image. Veuillez vérifier que l\'image ne dépasse pas la taille de 5Mo et qu\'il s\'agit d\'un format compatible', 'titre' => "Erreur");
                header('location: ' . getRouteUrl('admin_categorie_slide', ['categorie_id' => $categorie_id]));
                exit();
            }
        }

        //Ajout de l'image en base
        if ($image['etat'] == 'new') {
            $insert = new Image;
            $insert->title = $image['origine_name'];
            $insert->path = $bddPath;
            $insert->filename = $image['name'];
            $insert->md5 = $image ['md5'];
            if (!$insert->save()) {
                $_SESSION['notification'] = array('type' => 'err', 'message' => "Une erreur est survenue lors de l'ajout de l'image", 'titre' => "Erreur");
                header('location: ' . getRouteUrl('admin_categorie_slide', ['categorie_id' => $categorie_id]));
                exit();
            }
            $image_id = $insert->id;
        } else {
            $image_id = $image['id'];
        }

        //Mise à jour de la catégorie
        if (!Categorie::where('id', $categorie_id)->update(['slide_image_id' => $image_id])){
            $_SESSION['notification'] = array('type' => 'err', 'message' => "Une erreur est survenue lors de la modification de la catégorie", 'titre' => "Erreur");
            header('location: ' . getRouteUrl('admin_categorie_slide', ['categorie_id' => $categorie_id]));
            exit();
        }


        $_SESSION['notification'] = array('type' => 'conf', 'message' => "L'image a bien été ajoutée", 'titre' => "Erreur");
        header('location: ' . getRouteUrl('admin_categorie_slide', ['categorie_id' => $categorie_id]));
        exit();


    }
}


?>