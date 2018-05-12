<?php
use PHPRouter\RouteCollection;
use PHPRouter\Router;
use PHPRouter\Route;

$collection = new RouteCollection();

$collection->attachRoute(new Route('/', array(
    'name' => 'base',
    '_controller' => '\AppController\indexController::indexAction',
    'methods' => 'GET',
)));

$collection->attachRoute(new Route('/index', array(
    'name' => 'index',
    '_controller' => '\AppController\indexController::indexAction',
    'methods' => 'GET',
)));

$collection->attachRoute(new Route('/produit', array(
    'name' => 'produit',
    '_controller' => '\AppController\produitController::indexAction',
    'methods' => 'GET',
)));

/*************************
 * Partie Administration *
 *************************/
$collection->attachRoute(new Route('/administration', array(
    'name' => 'administration',
    '_controller' => '\AppController\Admin\adminController::indexAction',
    'methods' => 'GET',
)));
$collection->attachRoute(new Route('/administration/informations', array(
    'name' => 'admin_information',
    '_controller' => '\AppController\Admin\vocabulaireController::indexAction',
    'methods' => 'GET',
)));
$collection->attachRoute(new Route('/administration/informations', array(
    'name' => 'admin_information_edit',
    '_controller' => '\AppController\Admin\vocabulaireController::editAction',
    'methods' => 'POST',
)));
$collection->attachRoute(new Route('/administration/categories', array(
    'name' => 'admin_categorie',
    '_controller' => '\AppController\Admin\categorieController::indexAction',
    'methods' => 'GET',
)));
$collection->attachRoute(new Route('/administration/categories/edit/:id', array(
    'name' => 'admin_categorie_edit',
    '_controller' => '\AppController\Admin\categorieController::editFormAction',
    'methods' => 'GET',
    'parameters' => ['id' => '\d+']
)));
$collection->attachRoute(new Route('/administration/categories/edit/:id', array(
    'name' => 'admin_categorie_edit_process',
    '_controller' => '\AppController\Admin\categorieController::editProcessAction',
    'methods' => 'POST',
    'parameters' => ['id' => '\d+']
)));
$collection->attachRoute(new Route('/administration/categories/new/:categorie_id', array(
    'name' => 'admin_categorie_new',
    '_controller' => '\AppController\Admin\categorieController::newFormAction',
    'methods' => 'GET',
    'parameters' => ['categorie_id' => '\d+']
)));
$collection->attachRoute(new Route('/administration/categories/new/:categorie_id', array(
    'name' => 'admin_categorie_new_process',
    '_controller' => '\AppController\Admin\categorieController::newProcessAction',
    'methods' => 'POST',
    'parameters' => ['categorie_id' => '\d+']
)));
$collection->attachRoute(new Route('/administration/services', array(
    'name' => 'admin_service',
    '_controller' => '\AppController\Admin\serviceController::indexAction',
    'methods' => 'GET',
)));
$collection->attachRoute(new Route('/administration/services/ajax/paginate', array(
    'name' => 'admin_service_paginate',
    '_controller' => '\AppController\Admin\serviceController::paginateAction',
    'methods' => 'POST',
)));


$router = new Router($collection);
$router->setBasePath('/');
