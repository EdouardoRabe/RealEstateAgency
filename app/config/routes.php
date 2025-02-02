<?php

use app\controllers\AdminController;
use app\controllers\ConfirmationController;
use app\controllers\DepotController;
use app\controllers\DetailController;
use app\controllers\HabitationController;
use app\controllers\LoginController;
use app\controllers\RegenerationController;
use app\controllers\ReservationController;
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

$login_controller=new LoginController();
$habitation_controller=new HabitationController();
$detail_controller= new DetailController();
$reservation_controller=new ReservationController();
$admin_controller=new AdminController();

$router-> get('/',[$login_controller,'getStart']);
$router-> post('/checkLogin',[$login_controller,'checkLogin']);
$router-> post('/signUp',[$login_controller,'signUp']);
$router-> get('/home',[$habitation_controller,'getHome']);
$router-> get('/detail',[$detail_controller, 'getDetail']);
$router-> post("/reservation",[$reservation_controller,'makeReservation']);
$router-> get('/admin',[$admin_controller,'getStart']);
$router-> post('/checkLoginAdmin',[$admin_controller,'checkLogin']);
$router-> get('/crud',[$admin_controller,'getCrud']);
$router-> get('/delete',[$admin_controller,'delete']);
$router-> get('/update',[$admin_controller,'updateForm']);
$router-> get('/create',[$admin_controller,'createForm']);
$router-> post('/upload',[$admin_controller,'insertImgBase']);
$router-> post('/creation',[$admin_controller,'creation']);


//$router->get('/', \app\controllers\WelcomeController::class.'->home'); 

$router->get('/hello-world/@name', function($name) {
	echo '<h1>Hello world! Oh hey '.$name.'!</h1>';
});

