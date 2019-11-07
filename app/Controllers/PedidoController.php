<?php 

namespace App\Controllers;

use App\Models\{Pedido, ModelosInfo, Clientes, Ciudad, Tallas, MaterialModelos, PedidoModelo, ActividadTarea, ActividadTareaModelo};
use Respect\Validation\Validator as v;
use Zend\Diactoros\Response\RedirectResponse;
use Picqer\Barcode\BarcodeGeneratorHTML;

class PedidoController extends BaseController{
	
	//estos dos valores son los que se cambian, para modificar la cantidad de registros listados y el maximo numero en paginacion
	private $articulosPorPagina=20;
	private $limitePaginacion=20;


	public function getAddPedidoAction($request){
		$iniciar=0;
		$pedido = Pedido::Join("clientesProvedores","pedido.idCliente","=","clientesProvedores.id")
		->select('pedido.*', 'clientesProvedores.nombre')
		->latest('id')
		->limit($this->articulosPorPagina)->offset($iniciar)
		->get();
		
		$model = ModelosInfo::orderBy('referenciaMod')->get();

		return $this->renderHTML('listPedido.twig', [
			'pedidos'=> $pedido
		]);
	}


	public function getListAddPedidoAction($request){
		
		$cantModelos = $_GET['?'] ?? null;
		$models = ModelosInfo::latest('referenciaMod')->get();


		return $this->renderHTML('listAddPedido.twig',[
				'cantModelos' => $cantModelos,
				'models' => $models
		]);
	}

	public function postListAddPedidoAction($request){
		$model=null; $customer=null; $citys=null;
		$customer = Clientes::where("tipo","=",0)->orderBy('nombre')->get();
		$city = Ciudad::orderBy('nombre')->get();	

		$cantModelos = $_POST['cantModelos'];

		
		$models = array();

		for ($i=1; $i <= $cantModelos; $i++) { 
			$divideCadena = explode(" ", $_POST['modelo'.$i]);
			$idModelo=$divideCadena[0];
			$referenciaMod=$divideCadena[1];
			$tallas=$divideCadena[2];

			$models += [
			  $i => [
			    'idModelo' => $idModelo,
			    'referenciaMod' => $referenciaMod,
			    'tallas' => $tallas
			  ]
			];

		}
		
		return $this->renderHTML('addPedido.twig',[
				'models' => $models,
				'customers' => $customer,
				'citys' => $city
		]);
	}



	//Registra la Persona
	public function postAddPedidoAction($request){
		$cantidadPedMod=0; $sumaActividades=0;
		$sumatoriaPedido=0; $nominaModelo=0; $nominaActividad=0;
		$refPedido ='';
		$materiales = array(); $j=0;
		$cantidades = array();
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
					
					$queryPedido = Pedido::all();
					$pedidoUltimo = $queryPedido->last();
					$idPedido = $pedidoUltimo->id+1;

					$pedido = new Pedido();
					$pedido->id = $idPedido;
					$pedido->referencia = $postData['referencia'];
					$pedido->idCliente = $postData['idCliente'];
					$pedido->idCiudad = $postData['idCiudad'];
					$pedido->fechaPedido=$postData['fechaPedido'];
					$pedido->fechaEntrega=$postData['fechaEntrega'];
					$pedido->observacion = $postData['observacion'];
					$pedido->idUserRegister = $_SESSION['userId'];
					$pedido->idUserUpdate = $_SESSION['userId'];
					$pedido->save();

					$refPedido = $postData['referencia'];
					$observacionGeneral = $postData['observacion'];

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

$queryActividades = ActividadTareaModelo::Join("actividadTarea","actividadTareaModelo.idActividadTarea","=","actividadTarea.id")
	->select('actividadTareaModelo.idModeloInf','actividadTareaModelo.idActividadTarea','actividadTareaModelo.valorPorPar', 'actividadTarea.id', 'actividadTarea.nombre', 'actividadTarea.activoCheck', 'actividadTarea.posicion')
	->where("actividadTarea.activoCheck","=",1)
	->where("actividadTareaModelo.idModeloInf","=", $postData['idModelo'.$iterador])
	->latest('actividadTarea.posicion')
	->get();

if($queryActividades->isEmpty()){
	$queryActividades = ActividadTarea::where("activoCheck","=",1)->latest('posicion')->get();
}

foreach ($queryActividades as $actividad) {
	$sumaActividades += $actividad->valorPorPar;
}

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
					$pedidoModelo = new PedidoModelo();
					$pedidoModelo->idPedido = $idPedido;
					$pedidoModelo->idModelo=$postData['idModelo'.$iterador];
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
					
					$updPedido = Pedido::find($idPedido);
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
				'materiales' => $materiales,
				'cantidades' => $cantidades,
				'refPedido' => $refPedido,
				'cantPares' => $cantidadPedMod
			]);
		
	}

	public function getPdf(){
		$materiales=array();
		$sumatoria = 10;

		for ($iterador=4; $iterador <= 6 ; $iterador++) { 
			$query = MaterialModelos::Join("inventarioMaterial","materialModelos.idInventarioMaterial","=","inventarioMaterial.id")
			->select('materialModelos.*', 'inventarioMaterial.nombre', 'inventarioMaterial.unidadMedida', 'inventarioMaterial.precio')
			->where("materialModelos.idModeloInfo","=",$iterador)
			->get();
			
			foreach ($query as $value) {
				$informe[$value->id]['nameMaterial'] = $value->nombre;
				$informe[$value->id]['idModeloInfo'] = $value->idModeloInfo;
				$informe[$value->id]['cantidad']= $sumatoria;
				//$informe[$value->id]['consumo'] = $value->consumoPorPar;
				
			}

			/*$informe = [
				'idModelo' => $iterador,
				'mate' => 'material'.$iterador,
				'consumoPorPar' => 'consumo'.$iterador,
				'unidadMedida' => 'unidadMedida'.$iterador
			];*/

			$sumatoria++;
			/*$materiales[$iterador]['varios'] = $informe;
			$materiales[$iterador]['cantPares'] = $sumatoria;*/
		}



		return $this->renderHTML('pdfpedido.twig', [
			'materiales'=> $informe
		]);
	}


	//Lista todas la pedido Ordenando por posicion
	public function getListPedido(){
		$responseMessage = null; $iniciar=0;
		
		$numeroDeFilas = Pedido::selectRaw('count(*) as query_count')
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

		$pedido = Pedido::Join("clientesProvedores","pedido.idCliente","=","clientesProvedores.id")
		->select('pedido.*', 'clientesProvedores.nombre')
		->latest('id')
		->limit($this->articulosPorPagina)->offset($iniciar)
		->get();
		
		$model = ModelosInfo::orderBy('referenciaMod')->get();

		return $this->renderHTML('listPedido.twig', [
			'pedidos'=> $pedido,
			'numeroDePaginas' => $numeroDePaginas,
			'paginaActual' => $paginaActual
		]);
	}


}

?>
