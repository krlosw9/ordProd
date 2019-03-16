<?php 
 
namespace App\Controllers;

use App\Models\{Pedido, PedidoModelo, ModelosInfo, Tallas, Clientes, Ciudad, ActividadTarea, MaterialModelos};
use Respect\Validation\Validator as v;
use Zend\Diactoros\Response\RedirectResponse;

class UpdatePedidoController extends BaseController{
	
	//estos dos valores son los que se cambian, para modificar la cantidad de registros listados y el maximo numero en paginacion
	private $articulosPorPagina=20;
	private $limitePaginacion=20;

	public function postUpdDelPedido($request){
		$responseMessage = null; $pedidoModelos=null; $models=null; $tallas=null;
		$quiereActualizar = false; $citys=null; $customers=null; $pedidos=null;
		$ruta='listPedido.twig';

		if($request->getMethod()=='POST'){
			$postData = $request->getParsedBody();
			
			$id = $postData['id'] ?? false;
			if ($id) {
				if($postData['boton']=='del'){
				  try{
					$pedidoModelos = PedidoModelo::where("idPedido","=",$id)->get();
					foreach ($pedidoModelos as $modelo) {
						$modeloDel = new PedidoModelo();
						$modeloDel->destroy($modelo->id);
					}

					$pedido = new Pedido();
					$pedido->destroy($id);
					$responseMessage = "Se elimino el pedido";
				  }catch(\Exception $e){
				  	//$responseMessage = $e->getMessage();
				  	$prevMessage = substr($e->getMessage(), 0, 53);
					if ($prevMessage =="SQLSTATE[23000]: Integrity constraint violation: 1451") {
						$responseMessage = 'Error, No se puede eliminar, este pedido esta siendo usado.';
					}
				  }
				}elseif ($postData['boton']=='upd') {
					$quiereActualizar=true;
				}
			}else{
				$responseMessage = 'Debe Seleccionar un pedido';
			}
		}
		
		if ($quiereActualizar){
			//si quiere actualizar hace una consulta where id=$id y la envia por el array del renderHtml
			$pedidos = Pedido::find($id);
			$models = PedidoModelo::Join("modelosInfo","pedidoModelo.idModelo","=","modelosInfo.id")
			->select('pedidoModelo.*', 'modelosInfo.referenciaMod')
			->where("idPedido","=",$id)->get();

			$customers = Clientes::where("tipo","=",0)->orderBy('nombre')->get();
			$citys = Ciudad::orderBy('nombre')->get();

			$ruta='updatePedido.twig';
		}else{
			$iniciar=0;
			$pedidos = Pedido::Join("clientesProvedores","pedido.idCliente","=","clientesProvedores.id")
			->select('pedido.*', 'clientesProvedores.nombre')
			->latest('id')
			->limit($this->articulosPorPagina)->offset($iniciar)
			->get();
		}
		return $this->renderHTML($ruta, [
			'pedidos' => $pedidos,
			'pedidoModelos' => $pedidoModelos,
			'customers' => $customers,
			'citys' => $citys,
			'models' => $models,
			'tallas' => $tallas,
			'idUpdate' => $id,
			'responseMessage' => $responseMessage
		]);
	}


	//en esta accion se registra las modificaciones del registro
	public function getUpdatePedido($request){
		$cantidadPedMod=0; $sumaActividades=0;
		$sumatoriaPedido=0; $nominaModelo=0; $nominaActividad=0;
		$refPedido ='';
		$j=0;
		$ruta = 'listPedido.twig';
		
		$responseMessage = null;
		$responseMessage2 = null;
		
		if($request->getMethod()=='POST'){
			$postData = $request->getParsedBody();
			
			$pedidoValidator = v::key('referencia', v::stringType()->length(1, 12)->notEmpty());
			
			if($_SESSION['userId']){
				try{
					$pedidoValidator->assert($postData);
					$postData = $request->getParsedBody();

					$pedido = Pedido::find($postData['id']);
					$pedido->referencia = $postData['referencia'];
					$pedido->idCliente = $postData['idCliente'];
					$pedido->idCiudad = $postData['idCiudad'];
					$pedido->fechaPedido=$postData['fechaPedido'];
					$pedido->fechaEntrega=$postData['fechaEntrega'];
					$pedido->observacion = $postData['observacion'];
					$pedido->idUserUpdate = $_SESSION['userId'];
					$pedido->save();



					$refPedido = $postData['referencia'];
					$observacionGeneral = $postData['observacion'];

					$queryActividades = ActividadTarea::all();
					foreach ($queryActividades as $actividad) {
						$sumaActividades += $actividad->valorPorPar;
					}

//todo lo que esta al lado izquierdo es de lo que depende el html y el html
					echo "
<!DOCTYPE html>
<html>
<head>
  <title>Consumo Pedido #$refPedido </title>
  <link rel='stylesheet' href='https://stackpath.bootstrapcdn.com/bootstrap/4.2.1/css/bootstrap.min.css' integrity='sha384-GJzZqFGwb1QTTN6wy59ffF1BuGJpLSa9DkKMp0DgiMDm4iYMj70gZWKYbI706tWS' crossorigin='anonymous'>
</head>
<body onload='window.print();'>
<div class='wrapper' style='margin-left: 40px; margin-top: 5px; margin-right: 35px;'>
  <section>
<h1>
    Consumo
    <small>Pedido #: $refPedido</small>
  </h1>
  
</section>
<section class='content'>
  <div class='row'>
    <!-- left column -->
    <div class='col-md-12'>
     <div class='box' style='font-size: 25px;'>
      <div class='box-header with-border'>
        
      </div>
      <!-- /.box-header -->
      <div class='box-body'>
        <div class='row'>
		  <div class='col-md-12' style='word-wrap: break-word;'>
			<strong>Observacion general: </strong>$observacionGeneral
		  </div>
		</div>
					";
					
					for ($iterador=0; $iterador < $postData['iterador']; $iterador++) { 

$refModelo = $postData['refModelo'.$iterador];
$observacionModelo = $postData['observacion'.$iterador];			
echo "
<div class='row'>
  <div class='col-md-10'>
	<h3><strong>Modelo $refModelo</strong></h3>
  </div>
</div>
<div class='row'>
  <div class='col-md-12' style='word-wrap: break-word;'>
	<strong>Observacion: </strong>$observacionModelo
  </div>
</div>
<br>
<table class='table table-bordered'>
  <tr>
    <th style='width: 10px'>Material</th>
    <th style='width: 100px'>Medida</th>
    <th style='width: 100px'>Consumo</th>
    <th style='width: 100px'>Valor estimado</th>
  </tr>
";				
					
$precioVenta = $postData['precioVenta'.$iterador];
$cantidadPedMod= $postData['cantidadPedMod'.$iterador];

$informes = MaterialModelos::Join("inventarioMaterial","materialModelos.idInventarioMaterial","=","inventarioMaterial.id")
->select('materialModelos.*', 'inventarioMaterial.nombre', 'inventarioMaterial.unidadMedida', 'inventarioMaterial.precio')
->where("materialModelos.idModeloInfo","=",$postData['idModelo'.$iterador])
->get();

$totalValorEstimadoMaterial=0;
foreach ($informes as $material ) {
	$consumoPorMaterial = $material->consumoPorPar *$cantidadPedMod;
	$valorPorMaterial = $consumoPorMaterial*$material->precio;
	$totalValorEstimadoMaterial += $valorPorMaterial;
	
	echo "
<tr>
  <td> $material->nombre </td>
  <td> $material->unidadMedida </td>
  <td> $consumoPorMaterial </td>
  <td> $valorPorMaterial </td>
</tr>
	";
}

$nominaModelo = $cantidadPedMod*$sumaActividades;
$totalValorEstimado = $totalValorEstimadoMaterial + $nominaModelo;
$totalPrecioVenta = $cantidadPedMod * $precioVenta;
$ganancias = $totalPrecioVenta - $totalValorEstimado;

echo "</table>
<div class='row'>
	  <div class='col-md-6'>
		<table class='table table-bordered'>
";

foreach ($queryActividades as $actividad) {
	$nameActividad = $actividad->nombre;
	$nominaActividad = $actividad->valorPorPar * $cantidadPedMod;

	echo "
	<tr>
		<th>$nameActividad:</th> 
		<td>$ $nominaActividad </td>
	</tr>
	  ";
}
echo "
		</table>
	</div>

  <div class='col-md-12'>
	<table class='table table-bordered'>
	  <tr>
	    <th style='width: 70px'>Nomina</th>
	    <th style='width: 70px'>Material</th>
	    <th style='width: 100px'>Total costo</th>
	    <th style='width: 100px'>Precio Venta</th>
	    <th style='width: 100px'>Ganancia estimada</th>
	  </tr>
	  <tr>
		<td> $ $nominaModelo </td>
		<td> $ $totalValorEstimadoMaterial </td>
		<td> $ $totalValorEstimado </td>
		<td> $ $totalPrecioVenta </td>
		<td> $ $ganancias </td>
	  </tr>
	</table>
  </div>
</div>
<br>
";
					$pedidoModelo = PedidoModelo::find($postData['idPedidoModelo'.$iterador]);
					$pedidoModelo->cantidadPedMod=$postData['cantidadPedMod'.$iterador];
					$pedidoModelo->cantRestPedMod=$postData['cantidadPedMod'.$iterador];
					$pedidoModelo->precioVenta = $precioVenta;
					$pedidoModelo->costoNomina = $nominaModelo;
					$pedidoModelo->costoMaterial = $totalValorEstimadoMaterial;
					$pedidoModelo->observacion = $postData['observacion'.$iterador];
					$pedidoModelo->idUserRegister = $_SESSION['userId'];
					$pedidoModelo->idUserUpdate = $_SESSION['userId'];
					$pedidoModelo->save();

					$sumatoriaPedido += $cantidadPedMod;

					}//fin for()
					
					$updPedido = Pedido::find($postData['id']);//este id es el id de pedido-> me dio pereza cambiarle de nombre :)
					$updPedido->cantidad=$sumatoriaPedido;
					$updPedido->cantRestante=$sumatoriaPedido;
					$updPedido->save();

						
					$ruta = 'pdfpedido.twig';
					
echo "</div>
      <!-- /.box-body -->
      </div>
    </div>
</div>
<!-- ./wrapper -->
</section>
</div>
</body>

</body>
</html>";
					
					$responseMessage = 'Registrado';
				}catch(\Exception $e){
					$prevMessage = substr($e->getMessage(), 0, 47);
					
					if ($prevMessage =="SQLSTATE[23000]: Integrity constraint violation") {
						$responseMessage = 'Error, el numero del pedido ya existe.';
					}else{
						$responseMessage = substr($e->getMessage(), 0, 50);
					}
				}
			}
		}
		if ($responseMessage2) {
			$responseMessage .= $responseMessage2;
		}
		//Retorna a la pagina de registro con un mensaje $responseMessage
		
			return $this->renderHTML($ruta ,[
				'responseMessage' => $responseMessage,
				'refPedido' => $refPedido,
				'cantPares' => $cantidadPedMod
			]);
	}

	





}

?>
