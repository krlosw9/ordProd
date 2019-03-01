<?php 

namespace App\Controllers;

use App\Models\{Pedido, ModelosInfo, Clientes, Ciudad, Tallas, MaterialModelos, PedidoModelo, ActividadTarea};
use Respect\Validation\Validator as v;
use Zend\Diactoros\Response\RedirectResponse;
use Picqer\Barcode\BarcodeGeneratorHTML;

class PedidoController extends BaseController{
	
	//estos dos valores son los que se cambian, para modificar la cantidad de registros listados y el maximo numero en paginacion
	private $articulosPorPagina=20;
	private $limitePaginacion=20;


	public function getAddPedidoAction($request){
		$pedido = Pedido::Join("clientesProvedores","pedido.idCliente","=","clientesProvedores.id")
		->select('pedido.*', 'clientesProvedores.nombre')
		->latest('id')
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
		$model=null;
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
		$sumatoria=0; $sumaActividades=0; $valorTroquelada=0;
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

					$querytallas = Tallas::all();
					$tallasUltimo = $querytallas->last();
					$tallasUltimoId = $tallasUltimo->id+1;
					$idTallas = $tallasUltimoId;

					$queryActividades = ActividadTarea::all();
					foreach ($queryActividades as $actividad) {
						$sumaActividades += $actividad->valorPorPar;
						if ($actividad->id == 7) {
							$valorTroquelada = $actividad->valorPorPar;
						}
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

					if ($postData['tipoTallas'.$iterador]==1) {
						$tallas = new Tallas();
				  		$tallas->id = $idTallas;  
				  		$tallas->t35 = $postData[$iterador.'t35'];	
				  		$tallas->t36 = $postData[$iterador.'t36'];
				  		$tallas->t37 = $postData[$iterador.'t37'];
				  		$tallas->t38 = $postData[$iterador.'t38'];
				  		$tallas->t39 = $postData[$iterador.'t39'];
				  		$tallas->t40 = $postData[$iterador.'t40'];
				  		$tallas->t41 = $postData[$iterador.'t41'];
				  		$tallas->t42 = $postData[$iterador.'t42'];
				  		$tallas->t43 = $postData[$iterador.'t43'];
				  		$tallas->t44 = $postData[$iterador.'t44'];
				  		$tallas->idUserRegister=$_SESSION['userId'];
						$tallas->idUserUpdate=$_SESSION['userId'];
						$tallas->save();
						$sumatoria= $postData[$iterador.'t35']+$postData[$iterador.'t36']+$postData[$iterador.'t37']+$postData[$iterador.'t38']+$postData[$iterador.'t39']+$postData[$iterador.'t40']+$postData[$iterador.'t41']+$postData[$iterador.'t42']+$postData[$iterador.'t43']+$postData[$iterador.'t44'];
					}elseif ($postData['tipoTallas'.$iterador]==2) {
						$tallas = new Tallas();
				  		$tallas->id = $idTallas;  
				  		$tallas->t32 = $postData[$iterador.'t32'];	
				  		$tallas->t33 = $postData[$iterador.'t33'];
				  		$tallas->t34 = $postData[$iterador.'t34'];
				  		$tallas->t35 = $postData[$iterador.'t35'];
				  		$tallas->t36 = $postData[$iterador.'t36'];
				  		$tallas->t37 = $postData[$iterador.'t37'];
				  		$tallas->t38 = $postData[$iterador.'t38'];
				  		$tallas->t39 = $postData[$iterador.'t39'];
				  		$tallas->t40 = $postData[$iterador.'t40'];
				  		$tallas->t41 = $postData[$iterador.'t41'];
				  		$tallas->idUserRegister=$_SESSION['userId'];
						$tallas->idUserUpdate=$_SESSION['userId'];
						$tallas->save();
						$sumatoria= $postData[$iterador.'t32']+$postData[$iterador.'t33']+$postData[$iterador.'t34']+$postData[$iterador.'t35']+$postData[$iterador.'t36']+$postData[$iterador.'t37']+$postData[$iterador.'t38']+$postData[$iterador.'t39']+$postData[$iterador.'t40']+$postData[$iterador.'t41'];
					}elseif ($postData['tipoTallas'.$iterador]==3) {
						$tallas = new Tallas();
				  		$tallas->id = $idTallas;  
				  		$tallas->t26 = $postData[$iterador.'t26'];	
				  		$tallas->t27 = $postData[$iterador.'t27'];
				  		$tallas->t28 = $postData[$iterador.'t28'];
				  		$tallas->t29 = $postData[$iterador.'t29'];
				  		$tallas->t30 = $postData[$iterador.'t30'];
				  		$tallas->t31 = $postData[$iterador.'t31'];
				  		$tallas->t32 = $postData[$iterador.'t32'];
				  		$tallas->t33 = $postData[$iterador.'t33'];
				  		$tallas->t34 = $postData[$iterador.'t34'];
				  		$tallas->t35 = $postData[$iterador.'t35'];
				  		$tallas->idUserRegister=$_SESSION['userId'];
						$tallas->idUserUpdate=$_SESSION['userId'];
						$tallas->save();
						$sumatoria= $postData[$iterador.'t26']+$postData[$iterador.'t27']+$postData[$iterador.'t28']+$postData[$iterador.'t29']+$postData[$iterador.'t30']+$postData[$iterador.'t31']+$postData[$iterador.'t32']+$postData[$iterador.'t33']+$postData[$iterador.'t34']+$postData[$iterador.'t35'];
					}elseif ($postData['tipoTallas'.$iterador]==4) {
						$tallas = new Tallas();
				  		$tallas->id = $idTallas;  
				  		$tallas->t20 = $postData[$iterador.'t20'];	
				  		$tallas->t21 = $postData[$iterador.'t21'];
				  		$tallas->t22 = $postData[$iterador.'t22'];
				  		$tallas->t23 = $postData[$iterador.'t23'];
				  		$tallas->t24 = $postData[$iterador.'t24'];
				  		$tallas->t25 = $postData[$iterador.'t25'];
				  		$tallas->t26 = $postData[$iterador.'t26'];
				  		$tallas->t27 = $postData[$iterador.'t27'];
				  		$tallas->t28 = $postData[$iterador.'t28'];
				  		$tallas->t29 = $postData[$iterador.'t29'];
				  		$tallas->idUserRegister=$_SESSION['userId'];
						$tallas->idUserUpdate=$_SESSION['userId'];
						$tallas->save();
						$sumatoria= $postData[$iterador.'t20']+$postData[$iterador.'t21']+$postData[$iterador.'t22']+$postData[$iterador.'t23']+$postData[$iterador.'t24']+$postData[$iterador.'t25']+$postData[$iterador.'t26']+$postData[$iterador.'t27']+$postData[$iterador.'t28']+$postData[$iterador.'t29'];
					}elseif ($postData['tipoTallas'.$iterador]==5) {
						$tallas = new Tallas();
				  		$tallas->id = $idTallas;  
				  		$tallas->t15 = $postData[$iterador.'t15'];
				  		$tallas->t16 = $postData[$iterador.'t16'];
				  		$tallas->t17 = $postData[$iterador.'t17'];
				  		$tallas->t18 = $postData[$iterador.'t18'];
				  		$tallas->t19 = $postData[$iterador.'t19'];
				  		$tallas->t20 = $postData[$iterador.'t20'];
				  		$tallas->t21 = $postData[$iterador.'t21'];
				  		$tallas->t22 = $postData[$iterador.'t22'];
				  		$tallas->t23 = $postData[$iterador.'t23'];
				  		$tallas->t24 = $postData[$iterador.'t24'];
				  		$tallas->idUserRegister=$_SESSION['userId'];
						$tallas->idUserUpdate=$_SESSION['userId'];
						$tallas->save();
						$sumatoria= $postData[$iterador.'t15']+$postData[$iterador.'t16']+$postData[$iterador.'t17']+$postData[$iterador.'t18']+$postData[$iterador.'t19']+$postData[$iterador.'t20']+$postData[$iterador.'t21']+$postData[$iterador.'t22']+$postData[$iterador.'t23']+$postData[$iterador.'t24'];
					}else{
						$responseMessage2=' Tallas NO registradas';
					}
					
					$precioVenta = $postData['precioVenta'.$iterador];

					$pedidoModelo = new PedidoModelo();
					$pedidoModelo->idPedido = $idPedido;
					$pedidoModelo->idModelo=$postData['idModelo'.$iterador];
					$pedidoModelo->idTallas = $idTallas;
					$pedidoModelo->cantidadPedMod=$sumatoria;
					$pedidoModelo->cantRestPedMod=$sumatoria;
					$pedidoModelo->precioVenta = $precioVenta;
					$pedidoModelo->observacion = $postData['observacion'.$iterador];
					$pedidoModelo->idUserRegister = $_SESSION['userId'];
					$pedidoModelo->idUserUpdate = $_SESSION['userId'];
					$pedidoModelo->save();
					

					$informes = MaterialModelos::Join("inventarioMaterial","materialModelos.idInventarioMaterial","=","inventarioMaterial.id")
					->select('materialModelos.*', 'inventarioMaterial.nombre', 'inventarioMaterial.unidadMedida', 'inventarioMaterial.precio')
					->where("materialModelos.idModeloInfo","=",$postData['idModelo'.$iterador])
					->get();

$totalValorEstimadoMaterial=0;
foreach ($informes as $material ) {
	$consumoPorMaterial = $material->consumoPorPar *$sumatoria;
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

$nominaModelo = $sumatoria*$sumaActividades;
$totalValorEstimado = $totalValorEstimadoMaterial + $nominaModelo;
$totalPrecioVenta = $sumatoria * $precioVenta;
$ganancias = $totalPrecioVenta - $totalValorEstimado;

echo "</table>
<div class='row'>
	  <div class='col-md-6'>
		<table class='table table-bordered'>
";

foreach ($queryActividades as $actividad) {
	$nameActividad = $actividad->nombre;
	$nominaActividad = $actividad->valorPorPar * $sumatoria;

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
	    <th style='width: 100px'>Total costo</th>
	    <th style='width: 100px'>Precio Venta</th>
	    <th style='width: 100px'>Ganancia estimada</th>
	  </tr>
	  <tr>
		<td> $ $nominaModelo </td>
		<td> $ $totalValorEstimado </td>
		<td> $ $totalPrecioVenta </td>
		<td> $ $ganancias </td>
	  </tr>
	</table>
  </div>
</div>
<br>
";

					$idTallas ++;
					$sumatoriaPedido += $sumatoria;

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
				'cantPares' => $sumatoria
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

	/*Al seleccionar uno de los dos botones (Eliminar o Actualizar) llega a esta accion y verifica cual de los dos botones oprimio si eligio el boton eliminar(del) elimina el registro de where $id Pero
	Si elige actualizar(upd) cambia la ruta del renderHTML y guarda una consulta de los datos del registro a modificar para mostrarlos en formulario de actualizacion llamado updateActOperario.twig y cuando modifica los datos y le da guardar a ese formulaio regresa a esta class y elige la accion getUpdateActivity()*/
	public function postUpdDelPedido($request){
		$responseMessage = null;
		$quiereActualizar = false;
		$provider = null;
		$ruta='listPedido.twig';

		if($request->getMethod()=='POST'){
			$postData = $request->getParsedBody();
			
			$id = $postData['id'] ?? false;
			if ($id) {
				if($postData['boton']=='del'){
				  try{
					$shape = new Pedido();
					$shape->destroy($id);
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
			$shapes = Pedido::find($id);
			$provider = Clientes::where("tipo","=",1)->orderBy('nombre')->get();
			$ruta='updatePedido.twig';
		}else{
			$shapes = Pedido::orderBy('referencia')->get();
		}
		return $this->renderHTML($ruta, [
			'shapes' => $shapes,
			'idUpdate' => $id,
			'providers' => $provider,
			'responseMessage' => $responseMessage
		]);
	}

	//en esta accion se registra las modificaciones del registro
	public function getUpdatePedido($request){

		$responseMessage = null;
				
		if($request->getMethod()=='POST'){
			$postData = $request->getParsedBody();

			$pedidoValidator = v::key('referencia', v::stringType()->length(1, 12)->notEmpty());

			
			if($_SESSION['userId']){
				try{
					$pedidoValidator->assert($postData);
					$postData = $request->getParsedBody();

					//la siguiente linea hace una consulta en la DB y trae el registro where id=$id y lo guarda en actOpe y posteriormente remplaza los valores y con el ->save() guarda la modificacion en la DB
					$shape = Pedido::find($postData['id']);
					$shape->referencia = $postData['referencia'];
					$shape->genero = $postData['genero'];
					$shape->color = $postData['color'];
					$shape->idProveedor = $postData['idProveedor'];
					$shape->observacion = $postData['observacion'];
					$shape->idUserUpdate = $_SESSION['userId'];
					$shape->save();
					$responseMessage = 'Actualizado';
				}catch(\Exception $e){
					$prevMessage = substr($e->getMessage(), 0, 15);
					
					if ($prevMessage =="All of the requ") {
						$responseMessage = 'Error, la referencia debe tener de 1 a 12 digitos.';
					}else{
						$responseMessage = substr($e->getMessage(), 0, 50);
					}
				}
			}
		}

		$shapes = Pedido::orderBy('referencia')->get();
		return $this->renderHTML('listPedido.twig',[
				'shapes' => $shapes,
				'responseMessage' => $responseMessage
		]);
	}

}

?>
