<?php 

namespace App\Controllers;

use App\Models\{InfoOrdenProduccion, Pedido, ModelosInfo, ActividadTarea, Tallas,TareaOperario, MaterialModelos, PedidoModelo, TallasModelo, TallasOrden};
use Respect\Validation\Validator as v;
use Zend\Diactoros\Response\RedirectResponse;
use Picqer\Barcode\BarcodeGeneratorHTML;

class OrdenProduccionController extends BaseController{
	//estos dos valores son los que se cambian, para modificar la cantidad de registros listados y el maximo numero en paginacion
	private $articulosPorPagina=20;
	private $limitePaginacion=20;


	public function getAddOrdenAction(){
		$pedido = null; $actividad=null; 
		$responseMessage = null; $models = null;
		$ruta = 'addOrden.twig'; $tallas=null;

		//$shape = Hormas::orderBy('referencia')->get();
		//$part = Pieza::orderBy('nombre')->get();
		//$inventory = InventarioMaterial::orderBy('nombre')->get();

		$idPedido=$_GET['?'] ?? null;
		$idPedidoModelo=$_GET['i'] ?? null; 
		if ($idPedidoModelo) {

			$pedido = Pedido::Join("clientesProvedores","pedido.idCliente","=","clientesProvedores.id")
			->select('pedido.*', 'clientesProvedores.nombre' , 'clientesProvedores.apellido')
			->where("pedido.id","=",$idPedido)
			->get();

			$models = PedidoModelo::Join("modelosInfo","pedidoModelo.idModelo","=","modelosInfo.id")
			->select('pedidoModelo.*', 'modelosInfo.referenciaMod', 'modelosInfo.tallas', 'modelosInfo.imagenUrl')
			->where("pedidoModelo.id","=",$idPedidoModelo)
			->get();

			
			$lastestModel = $models->last() ?? null;
			$idModelo = $lastestModel->idModelo ?? null;
			

			$tallas = TallasModelo::Join("talla","tallasModelo.idtalla","=","talla.id")
			->select('tallasModelo.*', 'talla.nombreTalla')
			->where("tallasModelo.idModeloInf","=",$idModelo)
			->orderBy('nombreTalla')
			->get();

			$actividad = ActividadTarea::where("activoCheck","=",1)->latest('posicion')->get();
		
		}else{
			$responseMessage = 'Debe seleccionar un modelo';
			$ruta = 'listAddOrden.twig';

			$models = PedidoModelo::Join("modelosInfo","pedidoModelo.idModelo","=","modelosInfo.id")
			->select('pedidoModelo.*', 'modelosInfo.referenciaMod')
			->where("pedidoModelo.idPedido","=",$idPedido)
			->get();
		}
		

		return $this->renderHTML($ruta ,[
				'pedidos' => $pedido,
				'actividads' => $actividad,
				'models' => $models,
				'tallas' => $tallas,
				'responseMessage' => $responseMessage
		]);
	}



	public function getListAddOrdenAction($request){
		$idPedido=$_GET['?'] ?? null;

		$models = PedidoModelo::Join("modelosInfo","pedidoModelo.idModelo","=","modelosInfo.id")
		->select('pedidoModelo.*', 'modelosInfo.referenciaMod')
		->where("pedidoModelo.idPedido","=",$idPedido)
		->get();

		return $this->renderHTML('listAddOrden.twig',[
				'idpedido' => $idPedido,
				'models' => $models
		]);
	}



	//Registra la Orden 
	public function postAddOrdenAction($request){
		$registroExitoso=false; $sumatoria=0;
		$tallaInicio=0;
		$tallaFin=0;
		$responseMessage = null;
		$provider = null;
		$imgName = null;
		$cantPiezas=$_GET['numPart'] ?? null;
		$nombreEmpresa = $_SESSION['companyName'];

		if($request->getMethod()=='POST'){
			$postData = $request->getParsedBody();
			
			$ordenValidator = v::key('referenciaOrd', v::stringType()->length(1, 12)->noWhitespace()->notEmpty());
			
			
			if($_SESSION['userId']){
				try{
					$ordenValidator->assert($postData);
					$postData = $request->getParsedBody();

					/* Consulta el ultimo id de InfoOrdenProduccion */
					$queryorden = InfoOrdenProduccion::all();
					$ordenUltimo = $queryorden->last();
					$ordenUltimoId = $ordenUltimo->id+1;

					/* registra InfoOrdenProduccion */
					$referenciaOrd=$postData['referenciaOrd'];
					$fechaRegistro=$postData['fechaRegistro'];
					$fechaEntrega=$postData['fechaEntrega'];
					$modeloRef=$postData['modelo'];
					$modeloImg=$postData['modeloImg'];
					$cliente=$postData['cliente'];
					$observacion1 = $postData['observacion1'];

					$order = new InfoOrdenProduccion();
					$order->id=$ordenUltimoId;
					$order->referenciaOrd=$referenciaOrd;
					$order->idPedido = $postData['idPedido'];
					$order->idPedidoModelo = $postData['idPedidoModelo'];
					$order->observacion1 = $observacion1;
					$order->fechaRegistro=$fechaRegistro;
					$order->fechaEntrega=$fechaEntrega;
					$order->idUserRegister = $_SESSION['userId'];
					$order->idUserUpdate = $_SESSION['userId'];
					$order->save();

					//Registra las tallas de la orden de produccion
					$arrayIdTalla = $postData['idTalla'] ?? null;

					foreach ($arrayIdTalla as $talla) {
						
						if ($postData[$talla]) {
							$cantParesTalla = $postData[$talla];	
						}else{
							$cantParesTalla=0;
						}
						
						$tallas = new TallasOrden();
						$tallas->idInfoOrden = $ordenUltimoId;
						$tallas->idTalla = $talla;
						$tallas->cantPares = $cantParesTalla;
						$tallas->idUserRegister=$_SESSION['userId'];
						$tallas->idUserUpdate=$_SESSION['userId'];
						$tallas->save();

						$sumatoria += $cantParesTalla;
					}
					
					for($i=0;$i<$postData['cantActividades'];$i++){
						$tarea = new TareaOperario();
						$tarea->idActTarea=$postData['idActividad'.$i];
						$tarea->idInfoOrdenProduccion=$ordenUltimoId;
						$tarea->valorTarea=$postData['valorActividad'.$i];
						$tarea->cantidadPares= $sumatoria;
						$tarea->pagaCheck= 0;
						$tarea->idUserRegister = $_SESSION['userId'];
						$tarea->idUserUpdate = $_SESSION['userId'];
						$tarea->save();
					}

					/****Descuenta la cantidad de la orden de produccion 
					al pedido*/
					$cantRestante=$postData['cantidadPedido']-$sumatoria;
					$pedido = Pedido::find($postData['idPedido']);
					$pedido->cantRestante=$cantRestante;
					$pedido->save();

					/****Descuenta la cantidad de la orden de produccion 
					al pedidoModelo*/
					$cantRestantePedMod=$postData['cantRestantePedModelo']-$sumatoria;
					$pedidoModelo = PedidoModelo::find($postData['idPedidoModelo']);
					$pedidoModelo->cantRestPedMod=$cantRestantePedMod;
					$pedidoModelo->save();

					$registroExitoso=true;
					$responseMessage = 'Registrado';
				}catch(\Exception $e){
					$prevMessage = substr($e->getMessage(), 0, 15);
					
					if ($prevMessage =="All of the requ") {
						$responseMessage = 'Error, la referencia debe tener de 1 a 12 digitos y no debe tener espacios en blanco.';
					}if ($prevMessage =="SQLSTATE[23000]") {
						$responseMessage = 'Error, el numero de la orden ya existe.';
					}else{
						//$responseMessage = $e->getMessage();
						$responseMessage = 'Error, '.substr($e->getMessage(), 0, 50);
					}
				}
			}
		}
		if ($registroExitoso==true) {

			$idModelo=null;
			$Bar = new BarcodeGeneratorHTML();
			$code2 = $Bar->getBarcode(99,$Bar::TYPE_CODE_93);
		
			$modelo = ModelosInfo::where("referenciaMod","=",$modeloRef)->select('id')->get();

			foreach ($modelo as $key => $value) {
				$idModelo=$value->id;
			}

			$materiales = MaterialModelos::Join("inventarioMaterial","materialModelos.idInventarioMaterial","=","inventarioMaterial.id")
			->Join("piezaModeloMaterial","materialModelos.idPieza","=","piezaModeloMaterial.id")
			->select('materialModelos.*', 'inventarioMaterial.nombre', 'inventarioMaterial.unidadMedida', 'piezaModeloMaterial.piezaNombre')
			->where("materialModelos.idModeloInfo","=",$idModelo)
			->get();
			
			$tareas = TareaOperario::Join("actividadTarea","tareaOperario.idActTarea","=","actividadTarea.id")
			->select('tareaOperario.*', 'actividadTarea.nombre', 'actividadTarea.posicion')
			->where("idInfoOrdenProduccion","=",$ordenUltimoId)
			->latest('posicion')->get();
			

echo 
"<!DOCTYPE html>
<html>
<head>
  <title>Orden/Produccion #$referenciaOrd</title>
  <link rel='stylesheet' href='https://stackpath.bootstrapcdn.com/bootstrap/4.2.1/css/bootstrap.min.css' integrity='sha384-GJzZqFGwb1QTTN6wy59ffF1BuGJpLSa9DkKMp0DgiMDm4iYMj70gZWKYbI706tWS' crossorigin='anonymous'>
</head>
<body onload='window.print();'>
<div class='col-md-6' style='margin-left: 35px; margin-top: 5px; margin-right: 20px;'>
  <!-- Main content -->
  <section class='invoice'>
    <!-- title row -->
    <div class='row'>
      <div class='col-md-12'>
        <h2 class='page-header'>
          <i class='fa fa-globe'></i>$nombreEmpresa,
          <small class='pull-right'>Orden: #$referenciaOrd</small>
        </h2>
      </div>
      <!-- /.col -->
    </div>
    
    <!-- info row -->
    <div class='row invoice-info'>
      <div class='col-md-12 invoice-col' style='font-size: 25px;'>
        <strong>Cliente:</strong> $cliente<br>
      </div>
      <div class='col-md-5 invoice-col' style='font-size: 18px;'>
        <address>
          <strong>Registro:</strong> $fechaRegistro<br>
          <strong>Entrega:</strong> $fechaEntrega<br>
          <strong>Modelo:</strong> $modeloRef<br>
        </address>
      </div>

      <div class='col-md-6 invoice-col'>     
	    <img src='./uploads/$modeloImg' alt='Imagen/modelo' width='220' height='220'>
	  </div>
	  <div class='col-md-5 invoice-col' style='font-size: 30px;'>
        <strong>Tallas:</strong>
      </div>
      <!-- /.col -->
      <div class='col-md-12 invoice-col' style='font-size: 30px;'>
        <address>
          <!--<strong>Tallas</strong><br>-->
          <table border='1'>
  <thead>
    <tr>";
    foreach ($arrayIdTalla as $talla) {
		if ($postData['nombreTalla'.$talla]) {
			$nombreTalla = $postData['nombreTalla'.$talla] ;
		}else{
			$nombreTalla = null;
		}
		echo "
	    	<th> $nombreTalla </th>
	     ";					
	}

    echo "
    <th> : </th>
  	<th> T </th>
    </tr>
  </thead>
  <tbody>
    <tr>"; 
	foreach ($arrayIdTalla as $talla) {					
		if ($postData[$talla]) {
			$cantParesTalla = $postData[$talla];	
		}else{
			$cantParesTalla=0;
		}

		echo "
	    	<th> $cantParesTalla </th>
	     ";
	}
    echo "
      <td> : </td>
      <td> $sumatoria </td>
    </tr>
  </tbody>
</table>
          
        </address>
      </div>
      <!-- /.col -->
      <!-- /.col -->
    </div>
    <!-- /.row -->
  <div class='row'>
    <div class='col-md-6'>
      <label><strong>Observaciones:</strong></label>
    </div>
  </div>
  <div class='row'>
    <div class='col-md-11' style='border: 1px black solid; word-wrap: break-word; height: 147px; font-size: 20px;'>$observacion1
    </div>
  </div>
<div class='col-md-5 invoice-col' style='font-size: 30px;'>
  <strong>Tickets:</strong>
</div>
	<!-- Inicio de los Tickets -->
    <div class='row'>
    "; 

foreach ($tareas as $tarea => $value) {
	$code = $Bar->getBarcode($value->id,$Bar::TYPE_CODE_93);
	$sumaTarea = $value->valorTarea * $sumatoria;
	echo "
  <div class='col-md-12'>
  ---------------------------------------------------------------------------------
  </div>
  <div class='col-md-12' style='font-size: 22px; height: 100px; padding-top: 10px; padding-left: 7px; padding-right: 10px;'>
    <div class='row'>
      <div class='col-md-4'>
        <strong>Orden:</strong>$referenciaOrd<br>
        <strong>Mod:</strong>$modeloRef
      </div>
      <div class='col-md-4' style='padding-top: 20px;'>
        $code
      </div>
      <div class='col-md-4' style='font-size: 20px;'>
        <strong>$value->nombre</strong><br>
        <p style='font-size: 16px;'>$sumatoria x $value->valorTarea = $ $sumaTarea </p>
      </div>
    </div>
  </div>"; 
}
          echo "

    </div>
    <!-- Fin de los Tickets -->
  </section>
  <!-- /.content -->
</div>
<!-- ./wrapper -->
</body>

</body>
</html>";

				return $this->renderHTML('codeBar.php');



			}else{
				return $this->renderHTML('listOrden.twig',[
					'responseMessage' => $responseMessage,
					'cantPiezas' => $cantPiezas
				]);
			}
	}

	//Lista todas los modelos Ordenando por posicion
	public function getListOrden(){
		$responseMessage = null; $iniciar=0;
		
		$numeroDeFilas= InfoOrdenProduccion::selectRaw('count(*) as query_count')
		->first();

		
		$totalFilasDb = $numeroDeFilas->query_count;
		$numeroDePaginas = $totalFilasDb/$this->articulosPorPagina;
		$numeroDePaginas = ceil($numeroDePaginas);

		//No permite que haya muchos botones de paginar y de esa forma va a traer una cantidad limitada de registro, no queremos que se pagine hasta el infinito, porque tambien puede ser molesto.
		if ($numeroDePaginas > $this->limitePaginacion) {
			$numeroDePaginas=$this->limitePaginacion;
		}

		$paginaActual = $_GET['pag'] ?? null;
		if ($paginaActual) {
			if ($paginaActual > $numeroDePaginas or $paginaActual < 1) {
				$paginaActual = 1;
			}
			$iniciar = ($paginaActual-1)*$this->articulosPorPagina;
		}

		$orden = InfoOrdenProduccion::Join("pedido","infoOrdenProduccion.idPedido","=","pedido.id")
		->select('infoOrdenProduccion.*', 'pedido.referencia')
		->latest('id')
		->limit($this->articulosPorPagina)->offset($iniciar)
		->get();

		//esta consulta es para el select del registro de una nueva orden de produccion(Seleccione el pedido de la orden a registrar)
		$pedido = Pedido::Join("clientesProvedores","pedido.idCliente","=","clientesProvedores.id")
		->select('pedido.*', 'clientesProvedores.nombre', 'clientesProvedores.apellido')
		->latest('id')
		->get();

		return $this->renderHTML('listOrden.twig', [
			'ordens' => $orden,
			'pedidos' => $pedido,
			'numeroDePaginas' => $numeroDePaginas,
			'paginaActual' => $paginaActual
		]);
	}

	

	
	public function getCode(){
	

	}
}

?>
