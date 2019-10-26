<?php

ini_set('display_errors',1);
ini_set('display_starup_error',1);
error_reporting(E_ERROR | E_PARSE); 

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
$map->get('index', '/order/', [
        'controller' => 'App\Controllers\IndexController',
        'action' => 'indexAction',
        'auth' => true
]);

//Rutas Actividad Tarea
$map->get('addActividadTarea', '/order/activityadd', [
        'controller' => 'App\Controllers\ActividadTareaController',
        'action' => 'getAddActividadTareaAction',
        'auth' => true
]);
$map->post('saveActividadTarea', '/order/activityadd', [
        'controller' => 'App\Controllers\ActividadTareaController',
        'action' => 'postAddActividadTareaAction',
        'auth' => true
]);
$map->get('listActividadTarea', '/order/activitylist', [
        'controller' => 'App\Controllers\ActividadTareaController',
        'action' => 'getListActividadTarea',
        'auth' => true
]);
$map->get('updateActividadTarea', '/order/activityupdate', [
        'controller' => 'App\Controllers\ActividadTareaController',
        'action' => 'getUpdateActividadTarea',
        'auth' => true
]);
$map->post('postUpdateActividadTarea', '/order/activityupdate', [
        'controller' => 'App\Controllers\ActividadTareaController',
        'action' => 'getUpdateActividadTarea',
        'auth' => true
]);
$map->post('delActividadTarea', '/order/activitydel', [
        'controller' => 'App\Controllers\ActividadTareaController',
        'action' => 'postUpdDelActividadTarea',
        'auth' => true
]);

//Rutas Talla
$map->get('addTalla', '/order/sizeadd', [
        'controller' => 'App\Controllers\TallaController',
        'action' => 'getAddTallaAction',
        'auth' => true
]);
$map->post('saveSize', '/order/sizeadd', [
        'controller' => 'App\Controllers\TallaController',
        'action' => 'postAddTallaAction',
        'auth' => true
]);
$map->get('listSize', '/order/sizelist', [
        'controller' => 'App\Controllers\TallaController',
        'action' => 'getListTalla',
        'auth' => true
]);
$map->get('updateSize', '/order/sizeupdate', [
        'controller' => 'App\Controllers\TallaController',
        'action' => 'getUpdateTalla',
        'auth' => true
]);
$map->post('postUpdateSize', '/order/sizeupdate', [
        'controller' => 'App\Controllers\TallaController',
        'action' => 'getUpdateTalla',
        'auth' => true
]);
$map->post('delSize', '/order/sizedel', [
        'controller' => 'App\Controllers\TallaController',
        'action' => 'postUpdDelTalla',
        'auth' => true
]);


//Rutas Registro de usuarios
$map->get('addUsers', '/order/usersadd', [
        'controller' => 'App\Controllers\UsersController',
        'action' => 'getAddUserAction',
        'auth' => true
]);
$map->post('saveUsers', '/order/usersadd', [
        'controller' => 'App\Controllers\UsersController',
        'action' => 'postSaveUser',
        'auth' => true
]);
$map->get('updateUsers', '/order/usersupdate', [
        'controller' => 'App\Controllers\UsersController',
        'action' => 'getUpdateUserAction',
        'auth' => true
]);
$map->post('passUsers', '/order/usersupdate', [
        'controller' => 'App\Controllers\UsersController',
        'action' => 'postUpdateUser',
        'auth' => true
]);

//Rutas Personas
$map->get('getAddPersonas', '/order/peopleadd', [
        'controller' => 'App\Controllers\PersonasController',
        'action' => 'getAddPersonasAction',
        'auth' => true
]);
$map->post('postAddPersonas', '/order/peopleadd', [
        'controller' => 'App\Controllers\PersonasController',
        'action' => 'postAddPersonasAction',
        'auth' => true
]);
$map->get('getListPersonas', '/order/peoplelist', [
        'controller' => 'App\Controllers\PersonasController',
        'action' => 'getListPersonas',
        'auth' => true
]);
$map->post('postDelPersonas', '/order/peopledel', [
        'controller' => 'App\Controllers\PersonasController',
        'action' => 'postUpdDelPersonas',
        'auth' => true
]);
$map->get('getPeopleUpdate', '/order/peopleupdate', [
        'controller' => 'App\Controllers\PersonasController',
        'action' => 'getUpdatePersonas',
        'auth' => true
]);
$map->post('postPeopleUpdate', '/order/peopleupdate', [
        'controller' => 'App\Controllers\PersonasController',
        'action' => 'getUpdatePersonas',
        'auth' => true
]);

//Rutas Ciudades
$map->get('getAddCiudades', '/order/cityadd', [
        'controller' => 'App\Controllers\CiudadController',
        'action' => 'getAddCiudadAction',
        'auth' => true
]);
$map->post('postAddCiudades', '/order/cityadd', [
        'controller' => 'App\Controllers\CiudadController',
        'action' => 'postAddCiudadAction',
        'auth' => true
]);
$map->get('getListCiudades', '/order/citylist', [
        'controller' => 'App\Controllers\CiudadController',
        'action' => 'getListCiudad',
        'auth' => true
]);
$map->post('postDelCiudades', '/order/citydel', [
        'controller' => 'App\Controllers\CiudadController',
        'action' => 'postUpdDelCiudad',
        'auth' => true
]);
$map->get('getCiudadUpdate', '/order/cityupdate', [
        'controller' => 'App\Controllers\CiudadController',
        'action' => 'getUpdateCiudad',
        'auth' => true
]);
$map->post('postCiudadUpdate', '/order/cityupdate', [
        'controller' => 'App\Controllers\CiudadController',
        'action' => 'getUpdateCiudad',
        'auth' => true
]);

//Rutas Linea
$map->get('getAddLinea', '/order/lineadd', [
        'controller' => 'App\Controllers\LineaController',
        'action' => 'getAddLineaAction',
        'auth' => true
]);
$map->post('postAddLinea', '/order/lineadd', [
        'controller' => 'App\Controllers\LineaController',
        'action' => 'postAddLineaAction',
        'auth' => true
]);
$map->get('getListLinea', '/order/linelist', [
        'controller' => 'App\Controllers\LineaController',
        'action' => 'getListLinea',
        'auth' => true
]);
$map->post('postDelLinea', '/order/linedel', [
        'controller' => 'App\Controllers\LineaController',
        'action' => 'postUpdDelLinea',
        'auth' => true
]);
$map->get('getLineaUpdate', '/order/lineupdate', [
        'controller' => 'App\Controllers\LineaController',
        'action' => 'getUpdateLinea',
        'auth' => true
]);
$map->post('postLineaUpdate', '/order/lineupdate', [
        'controller' => 'App\Controllers\LineaController',
        'action' => 'getUpdateLinea',
        'auth' => true
]);

//Rutas Clientes
$map->get('getAddClientes', '/order/customeradd', [
        'controller' => 'App\Controllers\ClientesController',
        'action' => 'getAddClientesAction',
        'auth' => true
]);
$map->post('postAddClientes', '/order/customeradd', [
        'controller' => 'App\Controllers\ClientesController',
        'action' => 'postAddClientesAction',
        'auth' => true
]);
$map->get('getListClientes', '/order/customerlist', [
        'controller' => 'App\Controllers\ClientesController',
        'action' => 'getListClientes',
        'auth' => true
]);
$map->post('postDelClientes', '/order/customerdel', [
        'controller' => 'App\Controllers\ClientesController',
        'action' => 'postUpdDelClientes',
        'auth' => true
]);
$map->get('getClientesUpdate', '/order/customerupdate', [
        'controller' => 'App\Controllers\ClientesController',
        'action' => 'getUpdateClientes',
        'auth' => true
]);
$map->post('postClientesUpdate', '/order/customerupdate', [
        'controller' => 'App\Controllers\ClientesController',
        'action' => 'getUpdateClientes',
        'auth' => true
]);

//Rutas Proveedores
$map->get('getAddProveedores', '/order/provideradd', [
        'controller' => 'App\Controllers\ProveedoresController',
        'action' => 'getAddProveedoresAction',
        'auth' => true
]);
$map->post('postAddProveedores', '/order/provideradd', [
        'controller' => 'App\Controllers\ProveedoresController',
        'action' => 'postAddProveedoresAction',
        'auth' => true
]);
$map->get('getListProveedores', '/order/providerlist', [
        'controller' => 'App\Controllers\ProveedoresController',
        'action' => 'getListProveedores',
        'auth' => true
]);
$map->post('postDelProveedores', '/order/providerdel', [
        'controller' => 'App\Controllers\ProveedoresController',
        'action' => 'postUpdDelProveedores',
        'auth' => true
]);
$map->get('getProveedoresUpdate', '/order/providerupdate', [
        'controller' => 'App\Controllers\ProveedoresController',
        'action' => 'getUpdateProveedores',
        'auth' => true
]);
$map->post('postProveedoresUpdate', '/order/providerupdate', [
        'controller' => 'App\Controllers\ProveedoresController',
        'action' => 'getUpdateProveedores',
        'auth' => true
]);

//Rutas Pieza
$map->get('getAddPieza', '/order/partadd', [
        'controller' => 'App\Controllers\PiezaController',
        'action' => 'getAddPiezaAction',
        'auth' => true
]);
$map->post('postAddPieza', '/order/partadd', [
        'controller' => 'App\Controllers\PiezaController',
        'action' => 'postAddPiezaAction',
        'auth' => true
]);
$map->get('getListPieza', '/order/partlist', [
        'controller' => 'App\Controllers\PiezaController',
        'action' => 'getListPieza',
        'auth' => true
]);
$map->post('postDelPieza', '/order/partdel', [
        'controller' => 'App\Controllers\PiezaController',
        'action' => 'postUpdDelPieza',
        'auth' => true
]);
$map->get('getPiezaUpdate', '/order/partupdate', [
        'controller' => 'App\Controllers\PiezaController',
        'action' => 'getUpdatePieza',
        'auth' => true
]);
$map->post('postPiezaUpdate', '/order/partupdate', [
        'controller' => 'App\Controllers\PiezaController',
        'action' => 'getUpdatePieza',
        'auth' => true
]);

//Rutas Hormas
$map->get('getAddHormas', '/order/shapeadd', [
        'controller' => 'App\Controllers\HormasController',
        'action' => 'getAddHormasAction',
        'auth' => true
]);
$map->post('postAddHormas', '/order/shapeadd', [
        'controller' => 'App\Controllers\HormasController',
        'action' => 'postAddHormasAction',
        'auth' => true
]);
$map->get('getListHormas', '/order/shapelist', [
        'controller' => 'App\Controllers\HormasController',
        'action' => 'getListHormas',
        'auth' => true
]);
$map->post('postDelHormas', '/order/shapedel', [
        'controller' => 'App\Controllers\HormasController',
        'action' => 'postUpdDelHormas',
        'auth' => true
]);
$map->get('getHormasUpdate', '/order/shapeupdate', [
        'controller' => 'App\Controllers\HormasController',
        'action' => 'getUpdateHormas',
        'auth' => true
]);
$map->post('postHormasUpdate', '/order/shapeupdate', [
        'controller' => 'App\Controllers\HormasController',
        'action' => 'getUpdateHormas',
        'auth' => true
]);
//Rutas InventarioMaterial
$map->get('getAddInventarioMaterial', '/order/inventoryadd', [
        'controller' => 'App\Controllers\InventarioMaterialController',
        'action' => 'getAddInventarioAction',
        'auth' => true
]);
$map->post('postAddInventarioMaterial', '/order/inventoryadd', [
        'controller' => 'App\Controllers\InventarioMaterialController',
        'action' => 'postAddInventarioAction',
        'auth' => true
]);
$map->get('getListInventarioMaterial', '/order/inventorylist', [
        'controller' => 'App\Controllers\InventarioMaterialController',
        'action' => 'getListInventario',
        'auth' => true
]);
$map->post('postDelInventarioMaterial', '/order/inventorydel', [
        'controller' => 'App\Controllers\InventarioMaterialController',
        'action' => 'postUpdDelInventario',
        'auth' => true
]);
$map->get('getHormasInventarioMaterial', '/order/inventoryupdate', [
        'controller' => 'App\Controllers\InventarioMaterialController',
        'action' => 'getUpdateInventario',
        'auth' => true
]);
$map->post('postInventarioMaterialUpdate', '/order/inventoryupdate', [
        'controller' => 'App\Controllers\InventarioMaterialController',
        'action' => 'getUpdateInventario',
        'auth' => true
]);

//Rutas Modelos
$map->get('getAddModelo', '/order/modeladd', [
        'controller' => 'App\Controllers\ModeloController',
        'action' => 'getAddModeloAction',
        'auth' => true
]);
$map->post('postAddModelo', '/order/modeladd', [
        'controller' => 'App\Controllers\ModeloController',
        'action' => 'postAddModeloAction',
        'auth' => true
]);
$map->get('getListModelo', '/order/modellist', [
        'controller' => 'App\Controllers\ModeloController',
        'action' => 'getListModelo',
        'auth' => true
]);
$map->post('postDelModelo', '/order/modeldel', [
        'controller' => 'App\Controllers\ModeloController',
        'action' => 'postUpdDelModelo',
        'auth' => true
]);
$map->get('getModeloUpdate', '/order/modelupdate', [
        'controller' => 'App\Controllers\ModeloController',
        'action' => 'getUpdateModelo',
        'auth' => true
]);
$map->post('postModeloUpdate', '/order/modelupdate', [
        'controller' => 'App\Controllers\ModeloController',
        'action' => 'getUpdateModelo',
        'auth' => true
]);

//Rutas Compras
$map->get('getAddCompra', '/order/buyadd', [
        'controller' => 'App\Controllers\CompraController',
        'action' => 'getAddCompraAction',
        'auth' => true
]);
$map->post('postAddCompra', '/order/buyadd', [
        'controller' => 'App\Controllers\CompraController',
        'action' => 'postAddCompraAction',
        'auth' => true
]);
$map->get('getListCompra', '/order/buylist', [
        'controller' => 'App\Controllers\CompraController',
        'action' => 'getListCompra',
        'auth' => true
]);
$map->post('postDelCompra', '/order/buydel', [
        'controller' => 'App\Controllers\CompraController',
        'action' => 'postUpdDelCompra',
        'auth' => true
]);
$map->get('getCompraUpdate', '/order/buyupdate', [
        'controller' => 'App\Controllers\CompraController',
        'action' => 'getUpdateCompra',
        'auth' => true
]);
$map->post('postCompraUpdate', '/order/buyupdate', [
        'controller' => 'App\Controllers\CompraController',
        'action' => 'postUpdateCompra',
        'auth' => true
]);

//Rutas Pedido 
$map->get('getAddPedido', '/order/pedidoadd', [
        'controller' => 'App\Controllers\PedidoController',
        'action' => 'getAddPedidoAction',
        'auth' => true
]);
$map->get('getListAddPedido', '/order/pedidolistadd', [
        'controller' => 'App\Controllers\PedidoController',
        'action' => 'getListAddPedidoAction',
        'auth' => true
]);
$map->post('postListAddPedido', '/order/pedidolistadd', [
        'controller' => 'App\Controllers\PedidoController',
        'action' => 'postListAddPedidoAction',
        'auth' => true
]);
$map->post('postAddPedido', '/order/pedidoadd', [
        'controller' => 'App\Controllers\PedidoController',
        'action' => 'postAddPedidoAction',
        'auth' => true
]);
$map->get('getListPedido', '/order/pedidolist', [
        'controller' => 'App\Controllers\PedidoController',
        'action' => 'getListPedido',
        'auth' => true
]);
$map->post('postDelPedido', '/order/pedidodel', [
        'controller' => 'App\Controllers\UpdatePedidoController',
        'action' => 'postUpdDelPedido',
        'auth' => true
]);
$map->get('getPedidoUpdate', '/order/pedidoupdate', [
        'controller' => 'App\Controllers\UpdatePedidoController',
        'action' => 'getUpdatePedido',
        'auth' => true
]);
$map->post('postPedidoUpdate', '/order/pedidoupdate', [
        'controller' => 'App\Controllers\UpdatePedidoController',
        'action' => 'getUpdatePedido',
        'auth' => true
]);
$map->get('getPdfPedido', '/order/pedidopdf', [
        'controller' => 'App\Controllers\PedidoController',
        'action' => 'getPdf',
        'auth' => true
]);

//Rutas OrdenProduccion
$map->get('getAddOrden', '/order/orderadd', [
        'controller' => 'App\Controllers\OrdenProduccionController',
        'action' => 'getAddOrdenAction',
        'auth' => true
]);
$map->post('postAddOrden', '/order/orderadd', [
        'controller' => 'App\Controllers\OrdenProduccionController',
        'action' => 'postAddOrdenAction',
        'auth' => true
]);
$map->get('getListOrden', '/order/orderlist', [
        'controller' => 'App\Controllers\OrdenProduccionController',
        'action' => 'getListOrden',
        'auth' => true
]);
$map->get('getListAddOrden', '/order/orderlistadd', [
        'controller' => 'App\Controllers\OrdenProduccionController',
        'action' => 'getListAddOrdenAction',
        'auth' => true
]);
$map->get('getCode', '/order/code', [
        'controller' => 'App\Controllers\OrdenProduccionController',
        'action' => 'getCode',
        'auth' => true
]);
$map->post('postDelOrden', '/order/orderdel', [
        'controller' => 'App\Controllers\UpdateOrdenProduccionController',
        'action' => 'postUpdDelOrden',
        'auth' => true
]);
$map->post('postOrdenUpdate', '/order/orderupdate', [
        'controller' => 'App\Controllers\UpdateOrdenProduccionController',
        'action' => 'postUpdateOrden',
        'auth' => true
]);



//Rutas TareaOperario
$map->get('getListTareaOperario', '/order/tarealist', [
        'controller' => 'App\Controllers\TareaOperarioController',
        'action' => 'getListTareaOperario',
        'auth' => true
]);
$map->post('postDelTareaOperario', '/order/tareadel', [
        'controller' => 'App\Controllers\TareaOperarioController',
        'action' => 'postUpdDelTareaOperario',
        'auth' => true
]);
$map->get('getTareaOperarioUpdate', '/order/tareaupdate', [
        'controller' => 'App\Controllers\TareaOperarioController',
        'action' => 'getUpdateTareaOperario',
        'auth' => true
]);
$map->post('postTareaOperarioUpdate', '/order/tareaupdate', [
        'controller' => 'App\Controllers\TareaOperarioController',
        'action' => 'getUpdateTareaOperario',
        'auth' => true
]);

//Rutas Nomina
$map->get('getAddNomina', '/order/rosteradd', [
        'controller' => 'App\Controllers\NominaController',
        'action' => 'getAddNominaAction',
        'auth' => true
]);
$map->post('postAddNomina', '/order/rosteradd', [
        'controller' => 'App\Controllers\NominaController',
        'action' => 'postAddNominaAction',
        'auth' => true
]);
$map->get('getListNomina', '/order/rosterlist', [
        'controller' => 'App\Controllers\NominaController',
        'action' => 'getListNomina',
        'auth' => true
]);
$map->post('postListAddNomina', '/order/rosterlistadd', [
        'controller' => 'App\Controllers\NominaController',
        'action' => 'postListAddNominaAction',
        'auth' => true
]);
$map->post('postQueryNomina', '/order/rosterquery', [
        'controller' => 'App\Controllers\NominaController',
        'action' => 'postQueryNominaAction',
        'auth' => true
]);

//Rutas Reporte Nomina de operario ya asignado 
$map->post('postListpayroll', '/order/payrollList', [
        'controller' => 'App\Controllers\NominaController',
        'action' => 'postListNominaPorOperarioYaAsignado',
        'auth' => true
]);

$map->post('postPayroll', '/order/payroll', [
        'controller' => 'App\Controllers\NominaController',
        'action' => 'postPagarNomina',
        'auth' => true
]);



//Rutas Reporte Nomina Individual 
$map->get('getlistReportNominaIndividual', '/order/reportrosterind', [
        'controller' => 'App\Controllers\ReportesController',
        'action' => 'getListNominaIndividual',
        'auth' => true
]);
$map->post('postQueryReportNominaIndividual', '/order/queryrosterind', [
        'controller' => 'App\Controllers\ReportesController',
        'action' => 'postQueryNominaIndividualAction',
        'auth' => true
]);

//Ruta Reporte Nomina Total
$map->get('getlistReportNominaTotal', '/order/reportrosterall', [
        'controller' => 'App\Controllers\ReportesController',
        'action' => 'getListNominaTotal',
        'auth' => true
]);

//Rutas Reporte Nomina Individual 
$map->get('getlistReportPedidoEstado', '/order/reportepedidoest', [
        'controller' => 'App\Controllers\ReportesController',
        'action' => 'getListPedidoEstado',
        'auth' => true
]);
$map->post('postQueryReportPedidoEstado', '/order/querypedidoest', [
        'controller' => 'App\Controllers\ReportesController',
        'action' => 'postQueryPedidoEstado',
        'auth' => true
]);







//Rutas que validan el login, dan acceso o denega acceso
$map->get('loginForm', '/order/login', [
        'controller' => 'App\Controllers\AuthController',
        'action' => 'getLogin'
]);
$map->post('auth', '/order/auth', [
        'controller' => 'App\Controllers\AuthController',
        'action' => 'postLogin'
]);
$map->get('admin', '/order/admin', [
        'controller' => 'App\Controllers\AdminController',
        'action' => 'getIndex',
        'auth' => true
]);
$map->get('logout', '/order/logout', [
        'controller' => 'App\Controllers\AuthController',
        'action' => 'getLogout'
]);
$map->get('noRoute', '/order/noRoute', [
        'controller' => 'App\Controllers\NoRouteController',
        'action' => 'getNoRoute'
]);


$matcher = $routerContainer->getMatcher();
$route = $matcher->match($request);


?>