<?php 
 
namespace App\Controllers;

use App\Models\{InfoOrdenProduccion, Pedido, ModelosInfo, ActividadTarea, Tallas,TareaOperario, MaterialModelos, PedidoModelo, Proveedores, Talla, TallasOrden, TallasModelo};
use Respect\Validation\Validator as v;
use Zend\Diactoros\Response\RedirectResponse;
use Picqer\Barcode\BarcodeGeneratorHTML;

class UpdateOrdenProduccionController extends BaseController{
	
	//estos dos valores son los que se cambian, para modificar la cantidad de registros listados y el maximo numero en paginacion
	private $articulosPorPagina=20;
	private $limitePaginacion=20;

	public function postUpdDelOrden($request){
		$responseMessage = null; $orden=null; $tareas=null; $cantidadParesTarea=0;
		$quiereActualizar = false; $model=null; $pedidoModelo=null; $hayOperario = false;
		$tallas = null; $pedido=null; $tallasOrden=null;
		$ruta='listOrden.twig';

		if($request->getMethod()=='POST'){
			$postData = $request->getParsedBody();
			
			$id = $postData['id'] ?? false;
			if ($id) {
				if($postData['boton']=='del'){
				  try{
				  	//En esta consulta y en el foreach verifica si tiene operarios asignados a las tareas de esta orden de produccion si ya tiene operario asignado no lo elimina
				  	$tareaOperario = TareaOperario::where("idInfoOrdenProduccion","=",$id)->get();
				  	foreach ($tareaOperario as $tarea) {
				  		if ($tarea->idOperario) {
				  			$hayOperario=true;
				  		}
				  	}

				  	if ($hayOperario == false) {
						
					  	$tareas = TareaOperario::where("idInfoOrdenProduccion","=",$id)->get();
					  	$tareaDel = new TareaOperario();
					  	foreach ($tareas as $tarea) {				  		
							$tareaDel->destroy($tarea->id);
							$cantidadParesTarea=$tarea->cantidadPares;
					  	}
					  	$orden = InfoOrdenProduccion::find($id);

					  	$tallasOrden = TallasOrden::where("idInfoOrden","=",$id)->get();
						$tallaDel = new TallasOrden();
						foreach ($tallasOrden as $tallaOrd) {
							$tallaDel->destroy($tallaOrd->id);
						}

						$orderDel = new InfoOrdenProduccion();
						$orderDel->destroy($id);

						// Al eliminar la orden de produccion, se debe actualizar la cantidad en el pedido y pedidoModelo sumandole la cantidad de pares que tenia esa ordenProduccion (la cantidad de pares se saca de la tarea) 
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
				  	}else{
				  		$responseMessage = "Error, No se puede eliminar, esta orden de produccion ya tiene operarios asignados";
				  	}

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

				$idPedidoModelo = $orden->idPedidoModelo ?? null;
				
				if ($idPedidoModelo) {
					$pedidoModelo = PedidoModelo::find($orden->idPedidoModelo);
					
					$pedido = Pedido::Join("clientesProvedores","pedido.idCliente","=","clientesProvedores.id")
					->select('pedido.*', 'clientesProvedores.nombre', 'clientesProvedores.apellido')
					->where("pedido.id","=",$pedidoModelo->idPedido)
					->get();

					$model = ModelosInfo::find($pedidoModelo->idModelo);
				}

				$tallas = TallasModelo::Join("talla","tallasModelo.idtalla","=","talla.id")
				->select('tallasModelo.*', 'talla.nombreTalla')
				->where("tallasModelo.idModeloInf","=",$pedidoModelo->idModelo)
				->orderBy('nombreTalla')
				->get();
				$tallasOrden = TallasOrden::where("idInfoOrden","=",$id)->orderBy('idTalla')->get();

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
			'tallasOrden' => $tallasOrden,
			'idUpdate' => $id,
			'responseMessage' => $responseMessage
		]);
	}

	//en esta accion se registra las modificaciones del registro 
	public function postUpdateOrden($request){

		$registroExitoso=false;
		$tallaInicio=0;
		$tallaFin=0;
		$responseMessage = null; $sumatoria=0;
		$provider = null;
		$imgName = null;
		$cantPiezas=$_GET['numPart'] ?? null;
		$nombreEmpresa = $_SESSION['companyName'];

		if($request->getMethod()=='POST'){
			$postData = $request->getParsedBody();
			
			$ordenValidator = v::key('referenciaOrd', v::stringType()->length(1, 12)->notEmpty());
			
			
			if($_SESSION['userId']){
				try{
					$ordenValidator->assert($postData);
					$postData = $request->getParsedBody();
					
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


					/* actualiza InfoOrdenProduccion */
					$order = InfoOrdenProduccion::find($idOrden);
					$order->referenciaOrd=$referenciaOrd;
					$order->observacion1 = $observacion1;
					$order->fechaRegistro=$fechaRegistro;
					$order->fechaEntrega=$fechaEntrega;
					$order->idUserUpdate = $_SESSION['userId'];
					$order->save();

					//Actualiza las tallas de la orden de produccion
					$arrayIdTalla = $postData['idTalla'] ?? null;

					foreach ($arrayIdTalla as $talla) {
						
						$idTallasOrden = $postData['idTallasOrden'.$talla] ?? null;

						if ($postData[$talla]) {
							$valorTalla = $postData[$talla]; 
						}else{
							$valorTalla = 0;
						}
						
						if ($idTallasOrden) {
							try{
								$tallaOrdenUpd = TallasOrden::find($idTallasOrden);
								$tallaOrdenUpd->cantPares=$valorTalla;
								$tallaOrdenUpd->idUserUpdate = $_SESSION['userId'];
								$tallaOrdenUpd->save();
								
								$sumatoria += $valorTalla;
							}catch(\Exception $e){
								//$responseMessage = $e->getMessage();
								$responseMessage = substr($e->getMessage(), 0, 53);
							}
						}else{
							if ($valorTalla != 0) {
								try{
									$tallaOrdenNew = new TallasOrden();
									$tallaOrdenNew->idInfoOrden = $idOrden;
									$tallaOrdenNew->idTalla = $talla;
									$tallaOrdenNew->cantPares=$valorTalla;
									$tallaOrdenNew->idUserRegister=$_SESSION['userId'];
									$tallaOrdenNew->idUserUpdate=$_SESSION['userId'];
									$tallaOrdenNew->save();

									$sumatoria += $valorTalla;
								}catch(\Exception $e){
									//$responseMessage = $e->getMessage();
									$responseMessage = substr($e->getMessage(), 0, 53);
								}
								
							}
						}
					}
					
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
					$responseMessage .= 'Registrado';
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
          <i class='fa fa-globe'></i> $nombreEmpresa,
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
}

?>
