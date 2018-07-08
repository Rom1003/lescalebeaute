<?php

namespace AppController\Admin;

use \App\Config;
use \App\Database;
use App\Tables\Slider;
use App\Tables\Image;
use \Illuminate\Database\Eloquent\Model;

class sliderController
{

    public static function indexAction($vars = array())
    {
        $config = new Config();
        $twig = $config->initTwig();

        try {
            $slides = array(1 => Slider::getSlide(1), 2 => Slider::getSlide(2), 3 => Slider::getSlide(3));
        } catch (\PDOException $exception) {
            \AppController\errorController::error500();
            exit;
        }


        echo $twig->render('Admin/Slider/show.twig', array(
            'slides' => $slides,
            'vars' => $vars
        ));
    }

    public static function addFormAction($ordre)
    {
        $config = new Config();
        $twig = $config->initTwig();

        if (!in_array($ordre, array(1, 2, 3))) {
            header('location: ' . getRouteUrl('admin_slider'));
            exit();
        }

        echo $twig->render('Admin/Slider/add.twig', array(
            'ordre' => $ordre
        ));
    }

    public static function addProcessAction($ordre)
    {
        if (!in_array($ordre, array(1, 2, 3))) {
            header('location: ' . getRouteUrl('admin_slider'));
            exit();
        }
        $config = new Config();

        $name = 'image';
        $path = $config->getGlobal('FILE_ROOT') . '/src/img/';
        $bddPath = 'slider/';
        createDirIfNotExist($path . $bddPath);

        if (!isset($_FILES[$name]) || empty($_FILES[$name]) || empty($_FILES[$name]['tmp_name'])) {
            $_SESSION['notification'] = array('type' => 'err', 'message' => "Veuillez sélectionner une image", 'titre' => "Erreur");
            header('location: ' . getRouteUrl('admin_slider_add', ['ordre' => $ordre]));
            exit();
        }

        //Récupération de la slide actuelle
        $slide = Slider::where('ordre', $ordre)->where('actif', 1)->get();
        if ($slide->isEmpty()) {
            $old_id = null;
        } else {
            $old_id = $slide->first()->id;
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
                header('location: ' . getRouteUrl('admin_slider_add', ['ordre' => $ordre]));
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
                header('location: ' . getRouteUrl('admin_slider_add', ['ordre' => $ordre]));
                exit();
            }
            $image_id = $insert->id;
        } else {
            $image_id = $image['id'];
        }

        //Mise à jour de l'ancienne slide
        if (!empty($old_id)) {
            if (!Slider::where('id', $old_id)->update(['actif' => 0, 'ordre' => null])) {
                //Erreur lors de la mise à jour
                $_SESSION['notification'] = array('type' => 'err', 'message' => "Une erreur est survenue lors de la mise à jour du carroussel", 'titre' => "Erreur");
                header('location: ' . getRouteUrl('admin_slider_add', ['ordre' => $ordre]));
                exit();
            }
        }


        //On regarde si une correspondance existe déjà dans la table slider
        $slider = Slider::where('image_id', $image_id)->get();
        if ($slider->isNotEmpty()) {
            $slider = $slider->first();
            if ($slider->actif == '1') {
                //Une slide existe déjà avec cette image
                $_SESSION['notification'] = array('type' => 'err', 'message' => "Cette image est déjà utilisée dans le carroussel", 'titre' => "Erreur");
                header('location: ' . getRouteUrl('admin_slider_add', ['ordre' => $ordre]));
                exit();
            }
            //On met à jour
            if (!Slider::where('id', $slider->id)->update(['actif' => 1, 'ordre' => $ordre])){
                //Erreur lors de l'update de la slide
                Slider::where('id', $old_id)->update(['actif' => 1, 'ordre' => $ordre]); //On remet l'ancienne slide
                $_SESSION['notification'] = array('type' => 'err', 'message' => "Une erreur inatendue est survenue", 'titre' => "Erreur");
                header('location: ' . getRouteUrl('admin_slider_add', ['ordre' => $ordre]));
                exit();
            }
        } else {
            //On ajoute un nouvel enregistrement
            $slider = new Slider;
            $slider->ordre = $ordre;
            $slider->actif = 1;
            $slider->image_id = $image_id;

            if (!$slider->save()) {
                //Erreur lors de l'ajout de la slide
                Slider::where('id', $old_id)->update(['actif' => 1, 'ordre' => $ordre]); //On remet l'ancienne slide
                $_SESSION['notification'] = array('type' => 'err', 'message' => "Une erreur inatendue est survenue", 'titre' => "Erreur");
                header('location: ' . getRouteUrl('admin_slider_add', ['ordre' => $ordre]));
                exit();
            }
        }



        $_SESSION['notification'] = array('type' => 'conf', 'message' => "Le carroussel a bien été modifié", 'titre' => "Erreur");
        header('location: ' . getRouteUrl('admin_slider_add', ['ordre' => $ordre]));
        exit();


    }
}


?>