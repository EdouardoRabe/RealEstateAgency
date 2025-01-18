<?php

use app\controllers\AdminController;
use app\controllers\ConfirmationController;
use app\controllers\DepotController;
use app\controllers\LoginController;
use app\controllers\RegenerationController;
use flight\Engine;
use flight\net\Router;

/** 
 * @var Router $router 
 * @var Engine $app
 */
/*$router->get('/', function() use ($app) {
	$Welcome_Controller = new WelcomeController($app);
	$app->render('welcome', [ 'message' => 'It works!!' ]);
});*/

$racine="";
$login_controller=new LoginController();
// $depot_controller=new DepotController();
// $confirmation_controller=new ConfirmationController();
// $regeneration_controller=new RegenerationController();
// $admin_controller= new AdminController();
$router-> get('/',[$login_controller,'getStart']);
$router-> post($racine.'/checkLogin',[$login_controller,'checkLogin']);
$router-> post($racine.'/signUp',[$login_controller,'signUp']);
$router-> get($racine.'/home',[$login_controller,'getHome']);
// $router-> post($racine.'/traitement-depot',[$depot_controller,'deposer']);
// $router-> post($racine.'/traitement-enfants',[$depot_controller,'generer']);
// $router-> post($racine.'/confirmation',[$confirmation_controller,'confirmer']);
// $router-> get($racine.'/regenerer-cadeaux',[$regeneration_controller,'regenerer']);
// $router-> get($racine.'/admin',[$admin_controller,'getStart']);
// $router-> post($racine.'/checkLoginAdmin',[$admin_controller,'checkLogin']);
// $router-> post($racine.'/confirmer-depot',[$depot_controller,'confirmer']);
// $router-> get($racine.'/combler',[$depot_controller,'combler']);
// $router-> get($racine.'/accueilAdmin',[$admin_controller,'getTable']);



//$router->get('/', \app\controllers\WelcomeController::class.'->home'); 

$router->get('/hello-world/@name', function($name) {
	echo '<h1>Hello world! Oh hey '.$name.'!</h1>';
});

