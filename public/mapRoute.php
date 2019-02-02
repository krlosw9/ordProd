<?php

ini_set('display_errors',1);
ini_set('display_starup_error',1);
error_reporting(E_ALL);

//require_once '../vendor/autoload.php';
use Aura\Router\RouterContainer;
$routerContainer = new RouterContainer();

$request = Zend\Diactoros\ServerRequestFactory::fromGlobals(
    $_SERVER,
    $_GET,
    $_POST,
    $_COOKIE,
    $_FILES
);

$map = $routerContainer->getMap();
//Ruta raiz o index
$map->get('index', '/curso/', [
        'controller' => 'App\Controllers\IndexController',
        'action' => 'indexAction',
        'auth' => true
]);

//Rutas Actividad Tarea
$map->get('addJobs', '/curso/jobsadd', [
        'controller' => 'App\Controllers\JobsController',
        'action' => 'getAddJobAction',
        'auth' => true
]);
$map->post('saveJobs', '/curso/jobsadd', [
        'controller' => 'App\Controllers\JobsController',
        'action' => 'getAddJobAction',
        'auth' => true
]);
$map->get('listActOpe', '/curso/actopelist', [
        'controller' => 'App\Controllers\JobsController',
        'action' => 'getListActOperario',
        'auth' => true
]);
$map->get('updateActivity', '/curso/updateActivity', [
        'controller' => 'App\Controllers\JobsController',
        'action' => 'getUpdateActivity',
        'auth' => true
]);
$map->post('postUpdateActivity', '/curso/updateActivity', [
        'controller' => 'App\Controllers\JobsController',
        'action' => 'getUpdateActivity',
        'auth' => true
]);
$map->post('activitydel', '/curso/activitydel', [
        'controller' => 'App\Controllers\JobsController',
        'action' => 'postUpdDelActOperario',
        'auth' => true
]);

//Rutas Registro de usuarios
$map->get('addUsers', '/curso/usersadd', [
        'controller' => 'App\Controllers\UsersController',
        'action' => 'getAddUserAction',
        'auth' => true
]);
$map->post('saveUsers', '/curso/usersadd', [
        'controller' => 'App\Controllers\UsersController',
        'action' => 'postSaveUser',
        'auth' => true
]);

//Rutas Personas
$map->get('getAddPersonas', '/curso/peopleadd', [
        'controller' => 'App\Controllers\PersonasController',
        'action' => 'getAddPersonasAction',
        'auth' => true
]);
$map->post('postAddPersonas', '/curso/peopleadd', [
        'controller' => 'App\Controllers\PersonasController',
        'action' => 'postAddPersonasAction',
        'auth' => true
]);
$map->get('getListPersonas', '/curso/peoplelist', [
        'controller' => 'App\Controllers\PersonasController',
        'action' => 'getListPersonas',
        'auth' => true
]);
$map->post('postDelPersonas', '/curso/peopledel', [
        'controller' => 'App\Controllers\PersonasController',
        'action' => 'postUpdDelPersonas',
        'auth' => true
]);
$map->get('getPeopleUpdate', '/curso/peopleupdate', [
        'controller' => 'App\Controllers\PersonasController',
        'action' => 'getUpdatePersonas',
        'auth' => true
]);
$map->post('postPeopleUpdate', '/curso/peopleupdate', [
        'controller' => 'App\Controllers\PersonasController',
        'action' => 'getUpdatePersonas',
        'auth' => true
]);



//Rutas que validan el login, dan acceso o denega acceso
$map->get('loginForm', '/curso/login', [
        'controller' => 'App\Controllers\AuthController',
        'action' => 'getLogin'
]);
$map->post('auth', '/curso/auth', [
        'controller' => 'App\Controllers\AuthController',
        'action' => 'postLogin'
]);
$map->get('admin', '/curso/admin', [
        'controller' => 'App\Controllers\AdminController',
        'action' => 'getIndex',
        'auth' => true
]);
$map->get('logout', '/curso/logout', [
        'controller' => 'App\Controllers\AuthController',
        'action' => 'getLogout'
]);
$map->get('noRoute', '/curso/noRoute', [
        'controller' => 'App\Controllers\NoRouteController',
        'action' => 'getNoRoute'
]);


$matcher = $routerContainer->getMatcher();
$route = $matcher->match($request);


?>