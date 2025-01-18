<?php

use app\controllers\AdminController;
use app\controllers\ConfirmationController;
use app\controllers\DepotController;
use app\controllers\HabitationController;
use app\controllers\LoginController;
use app\controllers\RegenerationController;
use app\models\HabitationModel;
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
$habitation_controller=new HabitationController();

$router-> get('/',[$login_controller,'getStart']);
$router-> post($racine.'/checkLogin',[$login_controller,'checkLogin']);
$router-> post($racine.'/signUp',[$login_controller,'signUp']);
$router-> get($racine.'/home',[$habitation_controller,'getHome']);




//$router->get('/', \app\controllers\WelcomeController::class.'->home'); 

$router->get('/hello-world/@name', function($name) {
	echo '<h1>Hello world! Oh hey '.$name.'!</h1>';
});

