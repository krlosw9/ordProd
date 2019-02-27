<?php 

namespace App\Controllers;

use App\Models\{InfoOrdenProduccion, Pedido, ModelosInfo, ActividadTarea, Tallas,TareaOperario, MaterialModelos, PedidoModelo};
use Respect\Validation\Validator as v;
use Zend\Diactoros\Response\RedirectResponse;
use Picqer\Barcode\BarcodeGeneratorHTML;

class OrdenProduccionController extends BaseController{
	public function getAddOrdenAction(){
		$pedido = null; $actividad=null; 
		$responseMessage = null; $models = null;
		$ruta = 'addOrden.twig';

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

			$actividad = ActividadTarea::latest('posicion')->get();
		
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



	//Registra la Persona
	public function postAddOrdenAction($request){
		$registroExitoso=false;
		$tallaInicio=0;
		$tallaFin=0;
		$responseMessage = null;
		$provider = null;
		$imgName = null;
		$cantPiezas=$_GET['numPart'] ?? null;

		if($request->getMethod()=='POST'){
			$postData = $request->getParsedBody();
			
			$ordenValidator = v::key('referenciaOrd', v::stringType()->length(1, 12)->notEmpty());
			
			
			if($_SESSION['userId']){
				try{
					$ordenValidator->assert($postData);
					$postData = $request->getParsedBody();

					$querytallas = Tallas::all();
					$tallasUltimo = $querytallas->last();
					$tallasUltimoId = $tallasUltimo->id+1;


					if ($postData['tipoTallas']==1) {
						$tallaInicio=35;
						$tallaFin=44;
						$tallas = new Tallas();
				  		$tallas->id = $tallasUltimoId;
				  		$tallas->t35 = $postData['35'];	
				  		$tallas->t36 = $postData['36'];
				  		$tallas->t37 = $postData['37'];
				  		$tallas->t38 = $postData['38'];
				  		$tallas->t39 = $postData['39'];
				  		$tallas->t40 = $postData['40'];
				  		$tallas->t41 = $postData['41'];
				  		$tallas->t42 = $postData['42'];
				  		$tallas->t43 = $postData['43'];
				  		$tallas->t44 = $postData['44'];
				  		$tallas->idUserRegister=$_SESSION['userId'];
						$tallas->idUserUpdate=$_SESSION['userId'];
						$tallas->save();
						$sumatoria= $postData['35']+$postData['36']+$postData['37']+$postData['38']+$postData['39']+$postData['40']+$postData['41']+$postData['42']+$postData['43']+$postData['44'];
					}elseif ($postData['tipoTallas']==2) {
						$tallaInicio=32;
						$tallaFin=41;
						$tallas = new Tallas();
				  		$tallas->id = $tallasUltimoId;  
				  		$tallas->t32 = $postData['32'];	
				  		$tallas->t33 = $postData['33'];
				  		$tallas->t34 = $postData['34'];
				  		$tallas->t35 = $postData['35'];
				  		$tallas->t36 = $postData['36'];
				  		$tallas->t37 = $postData['37'];
				  		$tallas->t38 = $postData['38'];
				  		$tallas->t39 = $postData['39'];
				  		$tallas->t40 = $postData['40'];
				  		$tallas->t41 = $postData['41'];
				  		$tallas->idUserRegister=$_SESSION['userId'];
						$tallas->idUserUpdate=$_SESSION['userId'];
						$tallas->save();
						$sumatoria= $postData['32']+$postData['33']+$postData['34']+$postData['35']+$postData['36']+$postData['37']+$postData['38']+$postData['39']+$postData['40']+$postData['41'];
					}elseif ($postData['tipoTallas']==3) {
						$tallaInicio=26;
						$tallaFin=35;
						$tallas = new Tallas();
				  		$tallas->id = $tallasUltimoId;  
				  		$tallas->t26 = $postData['26'];	
				  		$tallas->t27 = $postData['27'];
				  		$tallas->t28 = $postData['28'];
				  		$tallas->t29 = $postData['29'];
				  		$tallas->t30 = $postData['30'];
				  		$tallas->t31 = $postData['31'];
				  		$tallas->t32 = $postData['32'];
				  		$tallas->t33 = $postData['33'];
				  		$tallas->t34 = $postData['34'];
				  		$tallas->t35 = $postData['35'];
				  		$tallas->idUserRegister=$_SESSION['userId'];
						$tallas->idUserUpdate=$_SESSION['userId'];
						$tallas->save();
						$sumatoria= $postData['26']+$postData['27']+$postData['28']+$postData['29']+$postData['30']+$postData['31']+$postData['32']+$postData['33']+$postData['34']+$postData['35'];
					}elseif ($postData['tipoTallas']==4) {
						$tallaInicio=20;
						$tallaFin=29;
						$tallas = new Tallas();
				  		$tallas->id = $tallasUltimoId;  
				  		$tallas->t20 = $postData['20'];	
				  		$tallas->t21 = $postData['21'];
				  		$tallas->t22 = $postData['22'];
				  		$tallas->t23 = $postData['23'];
				  		$tallas->t24 = $postData['24'];
				  		$tallas->t25 = $postData['25'];
				  		$tallas->t26 = $postData['26'];
				  		$tallas->t27 = $postData['27'];
				  		$tallas->t28 = $postData['28'];
				  		$tallas->t29 = $postData['29'];
				  		$tallas->idUserRegister=$_SESSION['userId'];
						$tallas->idUserUpdate=$_SESSION['userId'];
						$tallas->save();
						$sumatoria= $postData['20']+$postData['21']+$postData['22']+$postData['23']+$postData['24']+$postData['25']+$postData['26']+$postData['27']+$postData['28']+$postData['29'];
					}elseif ($postData['tipoTallas']==5) {
						$tallaInicio=15;
						$tallaFin=24;
						$tallas = new Tallas();
				  		$tallas->id = $tallasUltimoId;  
				  		$tallas->t15 = $postData['15'];
				  		$tallas->t16 = $postData['16'];
				  		$tallas->t17 = $postData['17'];
				  		$tallas->t18 = $postData['18'];
				  		$tallas->t19 = $postData['19'];
				  		$tallas->t20 = $postData['20'];
				  		$tallas->t21 = $postData['21'];
				  		$tallas->t22 = $postData['22'];
				  		$tallas->t23 = $postData['23'];
				  		$tallas->t24 = $postData['24'];
				  		$tallas->idUserRegister=$_SESSION['userId'];
						$tallas->idUserUpdate=$_SESSION['userId'];
						$tallas->save();
						$sumatoria= $postData['15']+$postData['16']+$postData['17']+$postData['18']+$postData['19']+$postData['20']+$postData['21']+$postData['22']+$postData['23']+$postData['24'];
					}else{
						$responseMessage2=' Tallas NO registradas';
					}
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
					$order->idTallas=$tallasUltimoId;
					$order->observacion1 = $observacion1;
					$order->fechaRegistro=$fechaRegistro;
					$order->fechaEntrega=$fechaEntrega;
					$order->idUserRegister = $_SESSION['userId'];
					$order->idUserUpdate = $_SESSION['userId'];
					$order->save();

					
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
						$responseMessage = 'Error, la referencia debe tener de 1 a 12 digitos.';
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
			$code2 = $Bar->getBarcode(99,$Bar::TYPE_CODE_128);
		
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
          <i class='fa fa-globe'></i>..Verde Menta,
          <small class='pull-right'>Orden: #$referenciaOrd</small>
        </h2>
      </div>
      <!-- /.col -->
    </div>
    
    <!-- info row -->
    <div class='row invoice-info'>
      <div class='col-md-12 invoice-col' style='font-size: 20px;'>
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
for ($i=$tallaInicio; $i <= $tallaFin ; $i++) { 
	echo "

      <th> $i </th>
      ";	
}

    echo "
    <th> : </th>
  	<th> T </th>
    </tr>
  </thead>
  <tbody>
    <tr>"; 

for ($i=$tallaInicio; $i <= $tallaFin ; $i++) {
	echo "<td>";
	echo $postData[$i];
	echo "</td>";
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
    <div class='col-md-11' style='border: 1px black solid; word-wrap: break-word; height: 120px;'>$observacion1
    </div>
  </div>
<div class='col-md-5 invoice-col' style='font-size: 30px;'>
  <strong>Tickets:</strong>
</div>
	<!-- Inicio de los Tickets -->
    <div class='row'>
    <div class='col-md-12'>
  ---------------------------------------------------------------------------------
  </div>
  <div class='col-md-12' style='font-size: 22px; height: 100px; padding-top: 10px; padding-left: 7px; padding-right: 10px;'>
    <div class='row'>
      <div class='col-md-4'>
        <strong>Orden:</strong> 0<br>
        <strong>Mod:</strong> 0
      </div>
      <div class='col-md-4' style='padding-top: 20px;'>
        $code2
      </div>
      <div class='col-md-4' style='font-size: 20px;'>
        <strong>VACIO</strong><br>
        <p style='font-size: 16px;'>0 x 0 = $ 0 </p>
      </div>
    </div>
  </div>
    "; 

foreach ($tareas as $tarea => $value) {
	$code = $Bar->getBarcode($value->id,$Bar::TYPE_CODE_128);
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
		$responseMessage = null;
		$pedido = Pedido::Join("clientesProvedores","pedido.idCliente","=","clientesProvedores.id")
		->select('pedido.*', 'clientesProvedores.nombre', 'clientesProvedores.apellido')
		->latest('id')
		->get();

		$orden = InfoOrdenProduccion::Join("pedido","infoOrdenProduccion.idPedido","=","pedido.id")
		->select('infoOrdenProduccion.*', 'pedido.referencia')
		->latest('id')
		->get();

		return $this->renderHTML('listOrden.twig', [
			'ordens' => $orden,
			'pedidos' => $pedido
		]);
		

		//return $this->renderHTML('listHormas.twig');
	}

	

	
	public function getCode(){
	

	}
}

?>
