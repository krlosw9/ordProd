<?php

ini_set('display_errors',1);
ini_set('display_starup_error',1);
error_reporting(E_ALL);

require_once '../vendor/autoload.php';  

session_start();

$dotenv = Dotenv\Dotenv::create(__DIR__ . '/..');
$dotenv->load();

use Illuminate\Database\Capsule\Manager as Capsule;
use Aura\Router\RouterContainer;

$capsule = new Capsule;

$capsule->addConnection([
    'driver'    => getenv('DB_DRIVER'),
    'host'      => getenv('DB_HOST'),
    'database'  => getenv('DB_NAME'),
    'username'  => getenv('DB_USER'),
    'password'  => getenv('DB_PASS'),
    'charset'   => 'utf8',
    'collation' => 'utf8_unicode_ci',
    'prefix'    => '',
]);

// Make this Capsule instance available globally via static methods... (optional)
$capsule->setAsGlobal();
// Setup the Eloquent ORM... (optional; unless you've used setEventDispatcher())
$capsule->bootEloquent();

$request = Zend\Diactoros\ServerRequestFactory::fromGlobals(
    $_SERVER,
    $_GET,
    $_POST,
    $_COOKIE,
    $_FILES
);

$routerContainer = new RouterContainer();
$map = $routerContainer->getMap();
$map->get('index', '/curso/', [
        'controller' => 'App\Controllers\IndexController',
        'action' => 'indexAction'
]);
$map->get('addJobs', '/curso/jobsadd', [
        'controller' => 'App\Controllers\JobsController',
        'action' => 'getAddJobAction'
]);
$map->post('saveJobs', '/curso/jobsadd', [
        'controller' => 'App\Controllers\JobsController',
        'action' => 'getAddJobAction'
]);
$map->get('addUsers', '/curso/usersadd', [
        'controller' => 'App\Controllers\UsersController',
        'action' => 'getAddUserAction'
]);
$map->post('saveUsers', '/curso/usersadd', [
        'controller' => 'App\Controllers\UsersController',
        'action' => 'postSaveUser'
]);
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

if(!$route){
    $controllerName = 'App\Controllers\NoRouteController';
    $actionName = 'getNoRoute';

    $controller = new $controllerName;
    $response = $controller->$actionName($request);
    echo $response->getBody();
}else{
    $handlerData = $route->handler;
    $controllerName = $handlerData['controller'];
    $actionName = $handlerData['action'];
    $needsAuth = $handlerData['auth'] ?? false;

    $sessionUserId = $_SESSION['userId'] ?? null;
    if ($needsAuth && !$sessionUserId) {
      $controllerName = 'App\Controllers\AuthController';
      $actionName = 'getLogout';
    }

    $controller = new $controllerName;
    $response = $controller->$actionName($request);

    foreach ($response->getHeaders() as $name => $values) {
      foreach ($values as $value) {
        header(sprintf('%s: %s', $name, $value), false);
      }
    }
    http_response_code($response->getStatusCode());

    echo $response->getBody();
}



?>