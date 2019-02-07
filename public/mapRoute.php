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
$map->get('addActividadTarea', '/curso/activityadd', [
        'controller' => 'App\Controllers\ActividadTareaController',
        'action' => 'getAddActividadTareaAction',
        'auth' => true
]);
$map->post('saveActividadTarea', '/curso/activityadd', [
        'controller' => 'App\Controllers\ActividadTareaController',
        'action' => 'postAddActividadTareaAction',
        'auth' => true
]);
$map->get('listActividadTarea', '/curso/activitylist', [
        'controller' => 'App\Controllers\ActividadTareaController',
        'action' => 'getListActividadTarea',
        'auth' => true
]);
$map->get('updateActividadTarea', '/curso/activityupdate', [
        'controller' => 'App\Controllers\ActividadTareaController',
        'action' => 'getUpdateActividadTarea',
        'auth' => true
]);
$map->post('postUpdateActividadTarea', '/curso/activityupdate', [
        'controller' => 'App\Controllers\ActividadTareaController',
        'action' => 'getUpdateActividadTarea',
        'auth' => true
]);
$map->post('delActividadTarea', '/curso/activitydel', [
        'controller' => 'App\Controllers\ActividadTareaController',
        'action' => 'postUpdDelActividadTarea',
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

//Rutas Ciudades
$map->get('getAddCiudades', '/curso/cityadd', [
        'controller' => 'App\Controllers\CiudadController',
        'action' => 'getAddCiudadAction',
        'auth' => true
]);
$map->post('postAddCiudades', '/curso/cityadd', [
        'controller' => 'App\Controllers\CiudadController',
        'action' => 'postAddCiudadAction',
        'auth' => true
]);
$map->get('getListCiudades', '/curso/citylist', [
        'controller' => 'App\Controllers\CiudadController',
        'action' => 'getListCiudad',
        'auth' => true
]);
$map->post('postDelCiudades', '/curso/citydel', [
        'controller' => 'App\Controllers\CiudadController',
        'action' => 'postUpdDelCiudad',
        'auth' => true
]);
$map->get('getCiudadUpdate', '/curso/cityupdate', [
        'controller' => 'App\Controllers\CiudadController',
        'action' => 'getUpdateCiudad',
        'auth' => true
]);
$map->post('postCiudadUpdate', '/curso/cityupdate', [
        'controller' => 'App\Controllers\CiudadController',
        'action' => 'getUpdateCiudad',
        'auth' => true
]);

//Rutas Clientes
$map->get('getAddClientes', '/curso/customeradd', [
        'controller' => 'App\Controllers\ClientesController',
        'action' => 'getAddClientesAction',
        'auth' => true
]);
$map->post('postAddClientes', '/curso/customeradd', [
        'controller' => 'App\Controllers\ClientesController',
        'action' => 'postAddClientesAction',
        'auth' => true
]);
$map->get('getListClientes', '/curso/customerlist', [
        'controller' => 'App\Controllers\ClientesController',
        'action' => 'getListClientes',
        'auth' => true
]);
$map->post('postDelClientes', '/curso/customerdel', [
        'controller' => 'App\Controllers\ClientesController',
        'action' => 'postUpdDelClientes',
        'auth' => true
]);
$map->get('getClientesUpdate', '/curso/customerupdate', [
        'controller' => 'App\Controllers\ClientesController',
        'action' => 'getUpdateClientes',
        'auth' => true
]);
$map->post('postClientesUpdate', '/curso/customerupdate', [
        'controller' => 'App\Controllers\ClientesController',
        'action' => 'getUpdateClientes',
        'auth' => true
]);

//Rutas Proveedores
$map->get('getAddProveedores', '/curso/provideradd', [
        'controller' => 'App\Controllers\ProveedoresController',
        'action' => 'getAddProveedoresAction',
        'auth' => true
]);
$map->post('postAddProveedores', '/curso/provideradd', [
        'controller' => 'App\Controllers\ProveedoresController',
        'action' => 'postAddProveedoresAction',
        'auth' => true
]);
$map->get('getListProveedores', '/curso/providerlist', [
        'controller' => 'App\Controllers\ProveedoresController',
        'action' => 'getListProveedores',
        'auth' => true
]);
$map->post('postDelProveedores', '/curso/providerdel', [
        'controller' => 'App\Controllers\ProveedoresController',
        'action' => 'postUpdDelProveedores',
        'auth' => true
]);
$map->get('getProveedoresUpdate', '/curso/providerupdate', [
        'controller' => 'App\Controllers\ProveedoresController',
        'action' => 'getUpdateProveedores',
        'auth' => true
]);
$map->post('postProveedoresUpdate', '/curso/providerupdate', [
        'controller' => 'App\Controllers\ProveedoresController',
        'action' => 'getUpdateProveedores',
        'auth' => true
]);

//Rutas Pieza
$map->get('getAddPieza', '/curso/partadd', [
        'controller' => 'App\Controllers\PiezaController',
        'action' => 'getAddPiezaAction',
        'auth' => true
]);
$map->post('postAddPieza', '/curso/partadd', [
        'controller' => 'App\Controllers\PiezaController',
        'action' => 'postAddPiezaAction',
        'auth' => true
]);
$map->get('getListPieza', '/curso/partlist', [
        'controller' => 'App\Controllers\PiezaController',
        'action' => 'getListPieza',
        'auth' => true
]);
$map->post('postDelPieza', '/curso/partdel', [
        'controller' => 'App\Controllers\PiezaController',
        'action' => 'postUpdDelPieza',
        'auth' => true
]);
$map->get('getPiezaUpdate', '/curso/partupdate', [
        'controller' => 'App\Controllers\PiezaController',
        'action' => 'getUpdatePieza',
        'auth' => true
]);
$map->post('postPiezaUpdate', '/curso/partupdate', [
        'controller' => 'App\Controllers\PiezaController',
        'action' => 'getUpdatePieza',
        'auth' => true
]);

//Rutas Hormas
$map->get('getAddHormas', '/curso/shapeadd', [
        'controller' => 'App\Controllers\HormasController',
        'action' => 'getAddHormasAction',
        'auth' => true
]);
$map->post('postAddHormas', '/curso/shapeadd', [
        'controller' => 'App\Controllers\HormasController',
        'action' => 'postAddHormasAction',
        'auth' => true
]);
$map->get('getListHormas', '/curso/shapelist', [
        'controller' => 'App\Controllers\HormasController',
        'action' => 'getListHormas',
        'auth' => true
]);
$map->post('postDelHormas', '/curso/shapedel', [
        'controller' => 'App\Controllers\HormasController',
        'action' => 'postUpdDelHormas',
        'auth' => true
]);
$map->get('getHormasUpdate', '/curso/shapeupdate', [
        'controller' => 'App\Controllers\HormasController',
        'action' => 'getUpdateHormas',
        'auth' => true
]);
$map->post('postHormasUpdate', '/curso/shapeupdate', [
        'controller' => 'App\Controllers\HormasController',
        'action' => 'getUpdateHormas',
        'auth' => true
]);
//Rutas Modelos
$map->get('getAddModelo', '/curso/modeladd', [
        'controller' => 'App\Controllers\ModeloController',
        'action' => 'getAddModeloAction',
        'auth' => true
]);
$map->post('postAddModelo', '/curso/modeladd', [
        'controller' => 'App\Controllers\ModeloController',
        'action' => 'postAddModeloAction',
        'auth' => true
]);
$map->get('getListModelo', '/curso/modellist', [
        'controller' => 'App\Controllers\ModeloController',
        'action' => 'getListModelo',
        'auth' => true
]);
$map->post('postDelModelo', '/curso/modeldel', [
        'controller' => 'App\Controllers\ModeloController',
        'action' => 'postUpdDelModelo',
        'auth' => true
]);
$map->get('getModeloUpdate', '/curso/modelupdate', [
        'controller' => 'App\Controllers\ModeloController',
        'action' => 'getUpdateModelo',
        'auth' => true
]);
$map->post('postModeloUpdate', '/curso/modelupdate', [
        'controller' => 'App\Controllers\ModeloController',
        'action' => 'getUpdateModelo',
        'auth' => true
]);

//Rutas Compras
$map->get('getAddCompra', '/curso/buyadd', [
        'controller' => 'App\Controllers\CompraController',
        'action' => 'getAddCompraAction',
        'auth' => true
]);
$map->post('postAddCompra', '/curso/buyadd', [
        'controller' => 'App\Controllers\CompraController',
        'action' => 'postAddCompraAction',
        'auth' => true
]);
$map->get('getListCompra', '/curso/buylist', [
        'controller' => 'App\Controllers\CompraController',
        'action' => 'getListCompra',
        'auth' => true
]);
$map->post('postDelCompra', '/curso/buydel', [
        'controller' => 'App\Controllers\CompraController',
        'action' => 'postUpdDelCompra',
        'auth' => true
]);
$map->get('getCompraUpdate', '/curso/buyupdate', [
        'controller' => 'App\Controllers\CompraController',
        'action' => 'getUpdateCompra',
        'auth' => true
]);
$map->post('postCompraUpdate', '/curso/buyupdate', [
        'controller' => 'App\Controllers\CompraController',
        'action' => 'postUpdateCompra',
        'auth' => true
]);
//Rutas Pedido
$map->get('getAddPedido', '/curso/pedidoadd', [
        'controller' => 'App\Controllers\PedidoController',
        'action' => 'getAddPedidoAction',
        'auth' => true
]);
$map->post('postAddPedido', '/curso/pedidoadd', [
        'controller' => 'App\Controllers\PedidoController',
        'action' => 'postAddPedidoAction',
        'auth' => true
]);
$map->get('getListPedido', '/curso/pedidolist', [
        'controller' => 'App\Controllers\PedidoController',
        'action' => 'getListPedido',
        'auth' => true
]);
$map->post('postDelPedido', '/curso/pedidodel', [
        'controller' => 'App\Controllers\PedidoController',
        'action' => 'postUpdDelPedido',
        'auth' => true
]);
$map->get('getPedidoUpdate', '/curso/pedidoupdate', [
        'controller' => 'App\Controllers\PedidoController',
        'action' => 'getUpdatePedido',
        'auth' => true
]);
$map->post('postPedidoUpdate', '/curso/pedidoupdate', [
        'controller' => 'App\Controllers\PedidoController',
        'action' => 'postUpdatePedido',
        'auth' => true
]);


$map->get('getPdf', '/curso/pdf', [
        'controller' => 'App\Controllers\PedidoController',
        'action' => 'getPdf',
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