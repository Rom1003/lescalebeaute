<?php
use PHPRouter\RouteCollection;
use PHPRouter\Router;
use PHPRouter\Route;

$collection = new RouteCollection();

//Index du site
$collection->attachRoute(new Route('/', array(
    'name' => 'base',
    '_controller' => '\AppController\indexController::indexAction',
    'methods' => 'GET',
)));
//Index du site
$collection->attachRoute(new Route('/index', array(
    'name' => 'index',
    '_controller' => '\AppController\indexController::indexAction',
    'methods' => 'GET',
)));
//Page à propos
$collection->attachRoute(new Route('/a_propos', array(
    'name' => 'a_propos',
    '_controller' => '\AppController\indexController::aproposAction',
    'methods' => 'GET',
)));

//Liste des catégories/services
$collection->attachRoute(new Route('/categorie/:id/:libelle', array(
    'name' => 'categorie_liste',
    '_controller' => '\AppController\categorieController::listeAction',
    'parameters' => ['id' => '\d+'],
    'methods' => 'GET'
)));

//Detail d'un service
$collection->attachRoute(new Route('/service/:id/:libelle', array(
    'name' => 'service_detail',
    '_controller' => '\AppController\serviceController::detailAction',
    'parameters' => ['id' => '\d+'],
    'methods' => 'GET'
)));

//Liste des produits
$collection->attachRoute(new Route('/produits', array(
    'name' => 'liste_produits',
    '_controller' => '\AppController\produitController::indexAction',
    'methods' => 'GET',
)));
//Pagination liste des produits
$collection->attachRoute(new Route('/produits/ajax/paginate', array(
    'name' => 'ajax_pagine_produits',
    '_controller' => '\AppController\produitController::paginateAction',
    'methods' => 'POST',
)));




/********************
 *      Upload      *
 ********************/
//Suprresion d'un upload
$collection->attachRoute(new Route('/upload/remove', array(
    'name' => 'upload_remove',
    '_controller' => '\AppController\uploadController::removeAction',
    'methods' => 'POST',
)));


/*************************
 * Partie Administration *
 *************************/

//Page de login
$collection->attachRoute(new Route('/administration/login', array(
    'name' => 'admin_login',
    '_controller' => '\AppController\Admin\adminController::loginFormAction',
    'methods' => 'GET',
)));
//Page de login
$collection->attachRoute(new Route('/administration/login', array(
    'name' => 'admin_login',
    '_controller' => '\AppController\Admin\adminController::loginProcessAction',
    'methods' => 'POST',
)));

//Index de l'administration
$collection->attachRoute(new Route('/administration', array(
    'name' => 'administration',
    '_controller' => '\AppController\Admin\adminController::indexAction',
    'methods' => 'GET',
)));

//Informations
//Liste des informations
$collection->attachRoute(new Route('/administration/informations', array(
    'name' => 'admin_information',
    '_controller' => '\AppController\Admin\vocabulaireController::indexAction',
    'methods' => 'GET',
)));
//Traitement des modification des informations
$collection->attachRoute(new Route('/administration/informations/edit', array(
    'name' => 'admin_information_edit',
    '_controller' => '\AppController\Admin\vocabulaireController::editAction',
    'methods' => 'POST',
)));

//A propos
//Liste des infos
$collection->attachRoute(new Route('/administration/a_propos', array(
    'name' => 'admin_apropos',
    '_controller' => '\AppController\Admin\vocabulaireController::aproposAction',
    'methods' => 'GET',
)));
//Traitement des modification a propos
$collection->attachRoute(new Route('/administration/a_propos/edit', array(
    'name' => 'admin_apropos_edit',
    '_controller' => '\AppController\Admin\vocabulaireController::aproposEditAction',
    'methods' => 'POST',
)));
//Ajax supression image a propos
$collection->attachRoute(new Route('/administration/a_propos/ajax/image/delete', array(
    'name' => 'admin_apropos_image_delete',
    '_controller' => '\AppController\Admin\vocabulaireController::imageAproposDeleteAction',
    'methods' => 'POST'
)));

//Catégories
//Liste des catégories
$collection->attachRoute(new Route('/administration/categories', array(
    'name' => 'admin_categorie',
    '_controller' => '\AppController\Admin\categorieController::indexAction',
    'methods' => 'GET',
)));
//Formulaire d'edition slide d'une catégorie
$collection->attachRoute(new Route('/administration/categories/slide/:categorie_id', array(
    'name' => 'admin_categorie_slide',
    '_controller' => '\AppController\Admin\categorieController::slideFormAction',
    'methods' => 'GET',
    'parameters' => ['categorie_id' => '\d+']
)));
//Traitement ajout slide
$collection->attachRoute(new Route('/administration/categories/slide/:categorie_id', array(
    'name' => 'admin_categorie_slide_process',
    '_controller' => '\AppController\Admin\categorieController::slideProcessAction',
    'methods' => 'POST',
    'parameters' => ['categorie_id' => '\d+']
)));
//Formulaire d'édition d'une catégorie
$collection->attachRoute(new Route('/administration/categories/edit/:id', array(
    'name' => 'admin_categorie_edit',
    '_controller' => '\AppController\Admin\categorieController::editFormAction',
    'methods' => 'GET',
    'parameters' => ['id' => '\d+']
)));
//Traitement de l'édition d'une catégorie
$collection->attachRoute(new Route('/administration/categories/edit/:id', array(
    'name' => 'admin_categorie_edit_process',
    '_controller' => '\AppController\Admin\categorieController::editProcessAction',
    'methods' => 'POST',
    'parameters' => ['id' => '\d+']
)));
//Formulaire d'une nouvelle catégorie
$collection->attachRoute(new Route('/administration/categories/new/:categorie_id', array(
    'name' => 'admin_categorie_new',
    '_controller' => '\AppController\Admin\categorieController::newFormAction',
    'methods' => 'GET',
    'parameters' => ['categorie_id' => '\d+']
)));
//Traitement d'ajout d'une catégorie
$collection->attachRoute(new Route('/administration/categories/new/:categorie_id', array(
    'name' => 'admin_categorie_new_process',
    '_controller' => '\AppController\Admin\categorieController::newProcessAction',
    'methods' => 'POST',
    'parameters' => ['categorie_id' => '\d+']
)));

//Services
//Liste des services
$collection->attachRoute(new Route('/administration/services', array(
    'name' => 'admin_service',
    '_controller' => '\AppController\Admin\serviceController::indexAction',
    'methods' => 'GET',
)));
//Liste des services liés à une catégorie
$collection->attachRoute(new Route('/administration/services/categorie/:categorie_id', array(
    'name' => 'admin_service_categorie',
    '_controller' => '\AppController\Admin\serviceController::indexAction',
    'methods' => 'GET',
    'parameters' => ['categorie_id' => '\d+']
)));
//Ajax de pagination de la liste des services
$collection->attachRoute(new Route('/administration/services/ajax/paginate', array(
    'name' => 'admin_service_paginate',
    '_controller' => '\AppController\Admin\serviceController::paginateAction',
    'methods' => 'POST',
)));
//Formulaire d'ajout d'un service
$collection->attachRoute(new Route('/administration/services/new', array(
    'name' => 'admin_service_new',
    '_controller' => '\AppController\Admin\serviceController::newFormAction',
    'methods' => 'GET',
)));
//Traitement d'ajout d'un service
$collection->attachRoute(new Route('/administration/services/new', array(
    'name' => 'admin_service_new_process',
    '_controller' => '\AppController\Admin\serviceController::newProcessAction',
    'methods' => 'POST',
)));
//Formulaire d'édition d'un service
$collection->attachRoute(new Route('/administration/services/edit/:id', array(
    'name' => 'admin_service_edit',
    '_controller' => '\AppController\Admin\serviceController::editFormAction',
    'methods' => 'GET',
    'parameters' => ['id' => '\d+']
)));
//Traitement de l'édition d'un service
$collection->attachRoute(new Route('/administration/services/edit/:id', array(
    'name' => 'admin_service_edit_process',
    '_controller' => '\AppController\Admin\serviceController::editProcessAction',
    'methods' => 'POST',
    'parameters' => ['id' => '\d+']
)));
//Ajax supression service_image
$collection->attachRoute(new Route('/administration/services/ajax/image/delete', array(
    'name' => 'admin_service_image_delete',
    '_controller' => '\AppController\Admin\serviceController::imageDeleteAction',
    'methods' => 'POST'
)));
//Slider
$collection->attachRoute(new Route('/administration/slider', array(
    'name' => 'admin_slider',
    '_controller' => '\AppController\Admin\sliderController::indexAction',
    'methods' => 'GET',
)));
//Ajout slide
$collection->attachRoute(new Route('/administration/slider/add/:ordre', array(
    'name' => 'admin_slider_add',
    '_controller' => '\AppController\Admin\sliderController::addFormAction',
    'methods' => 'GET',
    'parameters' => ['ordre' => '\d+']
)));
//Traitement ajout slide
$collection->attachRoute(new Route('/administration/slider/add/:ordre', array(
    'name' => 'admin_slider_add_process',
    '_controller' => '\AppController\Admin\sliderController::addProcessAction',
    'methods' => 'POST',
    'parameters' => ['ordre' => '\d+']
)));

//Liste des gammes
$collection->attachRoute(new Route('/administration/gammes', array(
    'name' => 'admin_gamme',
    '_controller' => '\AppController\Admin\gammeController::indexAction',
    'methods' => 'GET',
)));
//Ajout gamme
$collection->attachRoute(new Route('/administration/gamme/add', array(
    'name' => 'admin_gamme_add',
    '_controller' => '\AppController\Admin\gammeController::addFormAction',
    'methods' => 'GET',
)));
//Traitement ajout gamme
$collection->attachRoute(new Route('/administration/gamme/add', array(
    'name' => 'admin_gamme_add_process',
    '_controller' => '\AppController\Admin\gammeController::addProcessAction',
    'methods' => 'POST'
)));
//Formulaire d'édition d'une gamme
$collection->attachRoute(new Route('/administration/gamme/edit/:id', array(
    'name' => 'admin_gamme_edit',
    '_controller' => '\AppController\Admin\gammeController::editFormAction',
    'methods' => 'GET',
    'parameters' => ['id' => '\d+']
)));
//Traitement d'édition d'une gamme
$collection->attachRoute(new Route('/administration/gamme/edit/:id', array(
    'name' => 'admin_gamme_edit_process',
    '_controller' => '\AppController\Admin\gammeController::editProcessAction',
    'methods' => 'POST',
    'parameters' => ['id' => '\d+']
)));


//Liste des produits
$collection->attachRoute(new Route('/administration/produits', array(
    'name' => 'admin_produit',
    '_controller' => '\AppController\Admin\produitController::indexAction',
    'methods' => 'GET',
)));
//Ajax de pagination de la liste des produits
$collection->attachRoute(new Route('/administration/produit/ajax/paginate', array(
    'name' => 'admin_produit_paginate',
    '_controller' => '\AppController\Admin\produitController::paginateAction',
    'methods' => 'POST',
)));
//Ajout produit
$collection->attachRoute(new Route('/administration/produit/add', array(
    'name' => 'admin_produit_add',
    '_controller' => '\AppController\Admin\produitController::addFormAction',
    'methods' => 'GET',
)));
//Traitement ajout produit
$collection->attachRoute(new Route('/administration/produit/add', array(
    'name' => 'admin_produit_add_process',
    '_controller' => '\AppController\Admin\produitController::addProcessAction',
    'methods' => 'POST'
)));
//Formulaire d'édition d'un produit
$collection->attachRoute(new Route('/administration/produit/edit/:id', array(
    'name' => 'admin_produit_edit',
    '_controller' => '\AppController\Admin\produitController::editFormAction',
    'methods' => 'GET',
    'parameters' => ['id' => '\d+']
)));
//Traitement d'édition d'une gamme
$collection->attachRoute(new Route('/administration/produit/edit/:id', array(
    'name' => 'admin_produit_edit_process',
    '_controller' => '\AppController\Admin\produitController::editProcessAction',
    'methods' => 'POST',
    'parameters' => ['id' => '\d+']
)));
//Traitement activation/desactivation produit
$collection->attachRoute(new Route('/administration/produit/etat/:id/:actif', array(
    'name' => 'admin_produit_etat',
    '_controller' => '\AppController\Admin\produitController::editEtatAction',
    'methods' => 'POST',
    'parameters' => ['id' => '\d+', 'actif' => '0|1']
)));


$router = new Router($collection);
$router->setBasePath('/');
