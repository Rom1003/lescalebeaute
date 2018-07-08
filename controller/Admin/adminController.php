<?php

namespace AppController\Admin;

use \App\Config;
use \App\Database;
use \App\Tables\Categorie;
use \Illuminate\Database\Eloquent\Model;

class adminController{

    public static function indexAction(){
        $config = new Config();
        $twig = $config->initTwig();

        $config->admin();

        echo $twig->render('Admin/admin.twig', array(

        ));
    }

    public static function loginFormAction(){
        $config = new Config();
        $twig = $config->initTwig();

        if ($config->admin(false) === true){
            header('location: ' . getRouteUrl('administration'));
            exit();
        }

        echo $twig->render('Admin/login.twig', array(

        ));
    }

    public static function loginProcessAction(){
        $config = new Config();
        $twig = $config->initTwig();

        $post = trimArray($_POST);

        //On vérifie les champs
        if (!isset($post['login']) || empty($post['login'])){
            $_SESSION['notification'] = array('type' => 'err', 'message' => "Veuillez renseigner le login", 'titre' => "Erreur");
            header('location: ' . getRouteUrl('admin_login'));
            exit();
        }

        if (!isset($post['mdp']) || empty($post['mdp'])){
            $_SESSION['notification'] = array('type' => 'err', 'message' => "Veuillez renseigner le mot de passe", 'titre' => "Erreur");
            header('location: ' . getRouteUrl('admin_login'));
            exit();
        }

        if ($post['login'] != $config->getGlobal('LOGIN') || !password_verify($post['mdp'], $config->getGlobal('PASSWORD'))){
            $_SESSION['notification'] = array('type' => 'err', 'message' => "Le login ou le mot de passe est incorrect", 'titre' => "Erreur");
            header('location: ' . getRouteUrl('admin_login'));
            exit();
        }

        //Connexion réussie on ajoute en session le hash du password
        $_SESSION['ADMIN'] = $config->getGlobal('PASSWORD');
        header('location: ' . getRouteUrl('administration'));
        exit();
    }
}


?>