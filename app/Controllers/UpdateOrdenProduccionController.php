<?php 
 
namespace App\Controllers;

use App\Models\{InfoOrdenProduccion, Pedido, ModelosInfo, ActividadTarea, Tallas,TareaOperario, MaterialModelos, PedidoModelo, Proveedores};
use Respect\Validation\Validator as v;
use Zend\Diactoros\Response\RedirectResponse;
use Picqer\Barcode\BarcodeGeneratorHTML;

class UpdateOrdenProduccionController extends BaseController{
	
	//estos dos valores son los que se cambian, para modificar la cantidad de registros listados y el maximo numero en paginacion
	private $articulosPorPagina=20;
	private $limitePaginacion=20;

	public function postUpdDelOrden($request){
		$responseMessage = null; $orden=null; $tareas=null; $cantidadParesTarea=0;
		$quiereActualizar = false; $model=null; $pedidoModelo=null;
		$tallas = null; $pedido=null;
		$ruta='listOrden.twig';

		if($request->getMethod()=='POST'){
			$postData = $request->getParsedBody();
			
			$id = $postData['id'] ?? false;
			if ($id) {
				if($postData['boton']=='del'){
				  try{
				  	$tareas = TareaOperario::where("idInfoOrdenProduccion","=",$id)->get();
				  	$tareaDel = new TareaOperario();
				  	foreach ($tareas as $tarea) {				  		
						$tareaDel->destroy($tarea->id);
						$cantidadParesTarea=$tarea->cantidadPares;
				  	}
				  	$orden = InfoOrdenProduccion::find($id);

					$orderDel = new InfoOrdenProduccion();
					$orderDel->destroy($id);
					
					$tallaDel = new Tallas();
					$tallaDel->destroy($orden->idTallas);

					/* Al eliminar la orden de produccion, se debe actualizar la cantidad en el pedido y pedidoModelo sumandole la cantidad de pares que tenia esa ordenProduccion (la cantidad de pares se saca de la tarea) */
					$idPedidoModelo = $orden->idPedidoModelo ?? null;
					if ($idPedidoModelo) {
						$pedidoModelo = PedidoModelo::find($idPedidoModelo);
						$newCantRestPedMod = $pedidoModelo->cantRestPedMod +$cantidadParesTarea;
						$pedidoModelo->cantRestPedMod = $newCantRestPedMod;
						$pedidoModelo->save();

						$pedido = Pedido::find($pedidoModelo->idPedido);
						$newCantRestPedido = $pedido->cantRestante +$cantidadParesTarea;
						$pedido->cantRestante = $newCantRestPedido;
						$pedido->save();	
					}

					$responseMessage = "Se elimino la Orden";
				  }catch(\Exception $e){
				  	//$responseMessage = $e->getMessage();
				  	$prevMessage = substr($e->getMessage(), 0, 53);
					if ($prevMessage =="SQLSTATE[23000]: Integrity constraint violation: 1451") {
						$responseMessage = 'Error, No se puede eliminar, esta Orden de Produccion esta siendo usada.';
					}
				  }
				}elseif ($postData['boton']=='upd') {
					$quiereActualizar=true;
				}
			}else{
				$responseMessage = 'Debe Seleccionar una orden de produccion';
			}
		}
		
		if ($quiereActualizar){
			try{
				//si quiere actualizar hace una consulta where id=$id y la envia por el array del renderHtml
				$orden = InfoOrdenProduccion::find($id);
				$tareas = TareaOperario::Join("actividadTarea","tareaOperario.idActTarea","=","actividadTarea.id")
				->select('tareaOperario.*', 'actividadTarea.nombre', 'actividadTarea.posicion')
				->where("idInfoOrdenProduccion","=",$id)
				->latest('posicion')->get();

				$tallas = Tallas::find($orden->idTallas);

				$idPedidoModelo = $orden->idPedidoModelo ?? null;
				
				if ($idPedidoModelo) {
					$pedidoModelo = PedidoModelo::find($orden->idPedidoModelo);
					
					$pedido = Pedido::Join("clientesProvedores","pedido.idCliente","=","clientesProvedores.id")
					->select('pedido.*', 'clientesProvedores.nombre', 'clientesProvedores.apellido')
					->where("pedido.id","=",$pedidoModelo->idPedido)
					->get();

					$model = ModelosInfo::find($pedidoModelo->idModelo);
				}

				$ruta='updateOrden.twig';
			}catch(\Exception $e){
				$responseMessage = $e->getMessage();
			}
		}else{
			$iniciar=0;
			$orden = InfoOrdenProduccion::Join("pedido","infoOrdenProduccion.idPedido","=","pedido.id")
			->select('infoOrdenProduccion.*', 'pedido.referencia')
			->latest('id')
			->limit($this->articulosPorPagina)->offset($iniciar)
			->get();
		}
		return $this->renderHTML($ruta, [
			'ordens' => $orden,
			'tareas' => $tareas,
			'pedidoModelo' => $pedidoModelo,
			'pedidos' => $pedido,
			'model' => $model,
			'tallas' => $tallas,
			'idUpdate' => $id,
			'responseMessage' => $responseMessage
		]);
	}

	//en esta accion se registra las modificaciones del registro 
	public function postUpdateOrden($request){

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

					$tallas = Tallas::find($postData['idTallas']);
					if ($postData['tipoTallas']==1) {
						$tallaInicio=35;
						$tallaFin=44;
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
						$tallas->idUserUpdate=$_SESSION['userId'];
						$tallas->save();
						$sumatoria= $postData['35']+$postData['36']+$postData['37']+$postData['38']+$postData['39']+$postData['40']+$postData['41']+$postData['42']+$postData['43']+$postData['44'];
					}elseif ($postData['tipoTallas']==2) {
						$tallaInicio=32;
						$tallaFin=41;
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
						$tallas->idUserUpdate=$_SESSION['userId'];
						$tallas->save();
						$sumatoria= $postData['32']+$postData['33']+$postData['34']+$postData['35']+$postData['36']+$postData['37']+$postData['38']+$postData['39']+$postData['40']+$postData['41'];
					}elseif ($postData['tipoTallas']==3) {
						$tallaInicio=26;
						$tallaFin=35;
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
						$tallas->idUserUpdate=$_SESSION['userId'];
						$tallas->save();
						$sumatoria= $postData['26']+$postData['27']+$postData['28']+$postData['29']+$postData['30']+$postData['31']+$postData['32']+$postData['33']+$postData['34']+$postData['35'];
					}elseif ($postData['tipoTallas']==4) {
						$tallaInicio=20;
						$tallaFin=29;
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
						$tallas->idUserUpdate=$_SESSION['userId'];
						$tallas->save();
						$sumatoria= $postData['20']+$postData['21']+$postData['22']+$postData['23']+$postData['24']+$postData['25']+$postData['26']+$postData['27']+$postData['28']+$postData['29'];
					}elseif ($postData['tipoTallas']==5) {
						$tallaInicio=15;
						$tallaFin=24;
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
						$tallas->idUserUpdate=$_SESSION['userId'];
						$tallas->save();
						$sumatoria= $postData['15']+$postData['16']+$postData['17']+$postData['18']+$postData['19']+$postData['20']+$postData['21']+$postData['22']+$postData['23']+$postData['24'];
					}else{
						$responseMessage2=' Tallas NO registradas';
					}

					
					$referenciaOrd=$postData['referenciaOrd'];
					$fechaRegistro=$postData['fechaRegistro'];
					$fechaEntrega=$postData['fechaEntrega'];
					$modeloRef=$postData['modelo'];
					$modeloImg=$postData['modeloImg'];
					$cliente=$postData['cliente'];
					$observacion1 = $postData['observacion1'];
					$idOrden = $postData['idOrden'];

					//consulta la cantidad de pares que tenia esa tarea antes de actualizarla para luego actualizar la cantidad restante en el pedido y pedido modelo
					$queryTareas = TareaOperario::where("idInfoOrdenProduccion","=",$idOrden)->get();
					$ultimaTarea = $queryTareas->last();
					$prevCantParesTarea = $ultimaTarea->cantidadPares;


					/* registra InfoOrdenProduccion */
					$order = InfoOrdenProduccion::find($idOrden);
					$order->referenciaOrd=$referenciaOrd;
					$order->observacion1 = $observacion1;
					$order->fechaRegistro=$fechaRegistro;
					$order->fechaEntrega=$fechaEntrega;
					$order->idUserUpdate = $_SESSION['userId'];
					$order->save();

					
					for($i=0;$i<$postData['cantActividades'];$i++){
						$tarea = TareaOperario::find($postData['idTarea'.$i]);
						$tarea->valorTarea=$postData['valorTarea'.$i];
						$tarea->cantidadPares= $sumatoria;
						$tarea->idUserUpdate = $_SESSION['userId'];
						$tarea->save();
					}

					/* Obtengo la diferencia entre la cantidad de pares que acaba de modificar y la cantidad de pares que tenia antes esa tarea y por ende esa orden de produccion */
					$diferencia = $prevCantParesTarea-$sumatoria;
					
					/****Descuenta la cantidad de la orden de produccion 
					al pedido*/
					$pedido = Pedido::find($postData['idPedido']);
					$newCantRestante = $pedido->cantRestante + ($diferencia);
					$pedido->cantRestante=$newCantRestante;
					$pedido->save();
					

					/****Descuenta la cantidad de la orden de produccion 
					al pedidoModelo*/
					$pedidoModelo = PedidoModelo::find($postData['idPedidoModelo']);
					$newCantRestantePedMod = $pedidoModelo->cantRestPedMod + ($diferencia);
					$pedidoModelo->cantRestPedMod=$newCantRestantePedMod;
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
			->where("idInfoOrdenProduccion","=",$idOrden)
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
}

?>
