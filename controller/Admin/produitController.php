<?php

namespace AppController\Admin;

use \App\Config;
use \App\Database;
use App\Tables\Gamme;
use App\Tables\Produit;
use App\Tables\Image;
use \Illuminate\Database\Eloquent\Model;
use Respect\Validation\Validator as v;

class produitController
{
    public static function indexAction(){
        $config = new Config();
        $twig = $config->initTwig();

        //Récupération des gammes
        $gammes = Gamme::orderBy('libelle')->get();

        echo $twig->render('Admin/Produit/list.twig', array(
            'gammes' => $gammes
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

        $recherche = array();

        if (isset($_POST['search_libelle']) && !empty($_POST['search_libelle'])){
            $recherche['libelle'] = $_POST['search_libelle'];
        }
        if (isset($_POST['search_gamme']) && !empty($_POST['search_gamme'])){
            $recherche['gamme_id'] = $_POST['search_gamme'];
        }

        $produits = Produit::pagination('admin', $_POST['page'], $_POST['nbParPage'], $recherche);

//        echo $services;

        echo json_encode(array('etat' => 'conf', 'html' => $produits));
    }

    public static function addFormAction()
    {
        $config = new Config();
        $twig = $config->initTwig();

        //Récupération de la liste des gammes
        $gammes = Gamme::orderBy('libelle')->get();

        echo $twig->render('Admin/Produit/new.twig', array(
            'gammes' => $gammes
        ));
    }

    public static function addProcessAction(){
        $config = new Config();

        $anomalies = array();
        $post = trimArray($_POST);

        $name = 'image';
        $path = $config->getGlobal('FILE_ROOT') . '/src/img/';
        $bddPath = 'produit/';
        createDirIfNotExist($path . $bddPath);

        if (!isset($_FILES[$name]) || empty($_FILES[$name]) || empty($_FILES[$name]['tmp_name'])) {
            $anomalies[] = 'Veuillez sélectionner une image';
        }

        //Si gamme vide
        if (!isset($post['gamme']) || empty($post['gamme'])){
            $anomalies[] = 'Veuillez sélectionner une gamme';
        } else {
            //Vérifie que la gamme existe
            $gamme = Gamme::find($post['gamme']);
            if ($gamme === false || empty($gamme)) {
                $anomalies[] = 'Impossible de retrouver la gamme sélectionnée';
            }
        }

        //Si libellé vide
        if (!isset($post['libelle']) || empty($post['libelle'])){
            $anomalies[] = 'Veuillez renseigner un libellé';
        }

/*        //Si tarif vide
        if (!isset($post['tarif']) || empty($post['libelle'])){
            $anomalies[] = 'Veuillez renseigner un tarif';
        } else {
            //Remplacement des "," par des "."
            $post['tarif'] = str_replace(",", ".", $post['tarif']);
            if (!v::floatVal()->validate($post['tarif']) || $post['tarif'] <= 0) {
                $anomalies[] = 'Le tarif renseigné est incorrect';
            }
        }*/


        //Création du message d'anomalie
        if (!empty($anomalies)) {
            //Présence d'anomalies
            $message = '<ul>';
            foreach ($anomalies as $anomalie) {
                $message .= '<li>' . $anomalie . '</li>';
            }
            $message .= "</ul>";
        } else {
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
                    exit(json_encode(array('etat' => 'err', 'message' => 'Une erreur est survenue lors de l\'ajout d\'une image. Veuillez vérifier que l\'image ne dépasse pas la taille de 5Mo et qu\'il s\'agit d\'un format compatible')));
                }
            }

            //Ajout de l'image en base
            if ($image['etat'] == 'new') {
                $insert = new Image;
                $insert->title = $post['libelle'];
                $insert->path = $bddPath;
                $insert->filename = $image['name'];
                $insert->md5 = $image ['md5'];
                if (!$insert->save()) {
                    exit(json_encode(array('etat' => 'err', 'message' => "Une erreur est survenue lors de l'ajout de l'image")));
                }
                $image_id = $insert->id;
            } else {
                $image_id = $image['id'];
            }

            //On ajoute dans la table produit
            $produit = new Produit;
            $produit->libelle = $post['libelle'];
//            $produit->tarif = $post['tarif'];
            $produit->actif = 1;
            $produit->image_id = $image_id;
            $produit->gamme_id = $gamme->id;

            if (!$produit->save()) {
                //Erreur lors de l'ajout du produit
                exit(json_encode(array('etat' => 'err', 'message' => "Une erreur inatendue est survenue")));
            }

            //Redirection vers la liste des produit
            $_SESSION['notification'] = array('type' => 'conf', 'message' => "Le produit a bien été ajouté", 'titre' => "Confirmation");
            echo json_encode(array('etat' => 'conf', 'url' => getRouteUrl('admin_produit')));
            exit;


        }

        echo json_encode(array('etat' => 'err', 'message' => $message));
    }

    public static function editFormAction($id)
    {
        $config = new Config();
        $twig = $config->initTwig();

        //Récupération des informations du produit
        try {
            $produit = Produit::find($id);
            if (!$produit || empty($produit)){
                \AppController\errorController::error404();
                exit;
            }
        } catch (\PDOException $exception) {
            \AppController\errorController::error500();
            exit;
        }

        //Récupération de la liste des gammes
        $gammes = Gamme::orderBy('libelle')->get();


        echo $twig->render('Admin/Produit/edit.twig', array(
            'produit' => $produit,
            'gammes' => $gammes,
        ));
    }

    public static function editProcessAction($id){
        $config = new Config();
        $anomalies = array();

        $post = trimArray($_POST);

        //Récupération des informations du produit
        try {
            $produit = Produit::find($id);
            if (!$produit || empty($produit)){
                exit(json_encode(array('etat' => 'err', 'message' => "Impossible de retrouver ce produit")));
            }
        } catch (\PDOException $exception) {
            exit(json_encode(array('etat' => 'err', 'message' => "Une erreur est survenue")));
        }

        $name = 'image';
        $path = $config->getGlobal('FILE_ROOT') . '/src/img/';
        $bddPath = 'produit/';

        if (!isset($_FILES[$name]) || empty($_FILES[$name]) || empty($_FILES[$name]['tmp_name'])) {
            $setImage = false;
        } else {
            $setImage = true;
            createDirIfNotExist($path . $bddPath);
        }

        //Si gamme vide
        if (!isset($post['gamme']) || empty($post['gamme'])){
            $anomalies[] = 'Veuillez sélectionner une gamme';
        } else {
            //Vérifie que la gamme existe
            $gamme = Gamme::find($post['gamme']);
            if ($gamme === false || empty($gamme)) {
                $anomalies[] = 'Impossible de retrouver la gamme sélectionnée';
            }
        }

        //Si libellé vide
        if (!isset($post['libelle']) || empty($post['libelle'])){
            $anomalies[] = 'Veuillez renseigner un libellé';
        }

/*        //Si tarif vide
        if (!isset($post['tarif']) || empty($post['libelle'])){
            $anomalies[] = 'Veuillez renseigner un tarif';
        } else {
            //Remplacement des "," par des "."
            $post['tarif'] = str_replace(",", ".", $post['tarif']);
            if (!v::floatVal()->validate($post['tarif']) || $post['tarif'] <= 0) {
                $anomalies[] = 'Le tarif renseigné est incorrect';
            }
        }*/


        //Création du message d'anomalie
        if (!empty($anomalies)) {
            //Présence d'anomalies
            $message = '<ul>';
            foreach ($anomalies as $anomalie) {
                $message .= '<li>' . $anomalie . '</li>';
            }
            $message .= "</ul>";
        } else {
            if ($setImage === true) {
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
                        exit(json_encode(array('etat' => 'err', 'message' => 'Une erreur est survenue lors de l\'ajout d\'une image. Veuillez vérifier que l\'image ne dépasse pas la taille de 5Mo et qu\'il s\'agit d\'un format compatible')));
                    }
                }

                //Ajout de l'image en base
                if ($image['etat'] == 'new') {
                    $insert = new Image;
                    $insert->title = $post['libelle'];
                    $insert->path = $bddPath;
                    $insert->filename = $image['name'];
                    $insert->md5 = $image ['md5'];
                    if (!$insert->save()) {
                        exit(json_encode(array('etat' => 'err', 'message' => "Une erreur est survenue lors de l'ajout de l'image")));
                    }
                    $image_id = $insert->id;
                } else {
                    $image_id = $image['id'];
                }
            }

            //On effectue l'update
            $produit->libelle = $post['libelle'];
//            $produit->tarif = $post['tarif'];
            $produit->actif = 1;
            if ($setImage === true)$produit->image_id = $image_id;
            $produit->gamme_id = $gamme->id;

            if (!$produit->save()) {
                //Erreur lors de la modification du produit
                exit(json_encode(array('etat' => 'err', 'message' => "Une erreur inatendue est survenue")));
            }

            //Redirection vers la liste des produit
            $_SESSION['notification'] = array('type' => 'conf', 'message' => "Le produit a bien été modifié", 'titre' => "Confirmation");
            echo json_encode(array('etat' => 'conf', 'url' => getRouteUrl('admin_produit')));
            exit;


        }

        echo json_encode(array('etat' => 'err', 'message' => $message));
    }

    public static function editEtatAction($id, $etat){

        if (!in_array($etat, array(0, 1))){
            exit(json_encode(array('etat' => 'err', 'message' => "Etat inconnu")));
        }

        //Récupération des informations du produit
        try {
            $produit = Produit::find($id);
            if (!$produit || empty($produit)){
                exit(json_encode(array('etat' => 'err', 'message' => "Impossible de retrouver ce produit")));
            }
        } catch (\PDOException $exception) {
            exit(json_encode(array('etat' => 'err', 'message' => "Une erreur est survenue")));
        }

        //Modification en bdd
        $produit->actif = $etat;
        if (!$produit->save()) {
            //Erreur lors de la modification du produit
            exit(json_encode(array('etat' => 'err', 'message' => "Une erreur inatendue est survenue")));
        }

        echo json_encode(array('etat' => 'conf', 'message' => 'Le produit a bien été '.($etat == 1 ? 'activé' : 'désactivé')));
        exit;
    }
}