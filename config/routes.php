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

//Detail d'un service
$collection->attachRoute(new Route('/service/:id/:libelle', array(
    'name' => 'service_detail',
    '_controller' => '\AppController\serviceController::detailAction',
    'parameters' => ['id' => '\d+'],
    'methods' => 'GET'
)));

//Voir un produit
$collection->attachRoute(new Route('/produit', array(
    'name' => 'produit',
    '_controller' => '\AppController\produitController::indexAction',
    'methods' => 'GET',
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
$collection->attachRoute(new Route('/administration/informations', array(
    'name' => 'admin_information_edit',
    '_controller' => '\AppController\Admin\vocabulaireController::editAction',
    'methods' => 'POST',
)));

//Catégories
//Liste des catégories
$collection->attachRoute(new Route('/administration/categories', array(
    'name' => 'admin_categorie',
    '_controller' => '\AppController\Admin\categorieController::indexAction',
    'methods' => 'GET',
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


$router = new Router($collection);
$router->setBasePath('/');
