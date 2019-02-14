<?php 

namespace App\Controllers;

use App\Models\{InfoOrdenProduccion, Pedido, ModelosInfo, ActividadTarea, Tallas,TareaOperario, MaterialModelos};
use Respect\Validation\Validator as v;
use Zend\Diactoros\Response\RedirectResponse;
use Picqer\Barcode\BarcodeGeneratorHTML;

class OrdenProduccionController extends BaseController{
	public function getAddOrdenAction(){
		$pedido = null; $refModelo=null; $tallas=null; $actividad=null;

		//$shape = Hormas::orderBy('referencia')->get();
		//$part = Pieza::orderBy('nombre')->get();
		//$inventory = InventarioMaterial::orderBy('nombre')->get();

		$idPedido=$_GET['?'] ?? null;
		$pedido = Pedido::Join("clientesProvedores","pedido.idCliente","=","clientesProvedores.id")
		->Join("modelosInfo","pedido.idModeloInfo","=","modelosInfo.id")
		->select('pedido.*', 'clientesProvedores.nombre', 'modelosInfo.referenciaMod', 'modelosInfo.imagenUrl')
		->where("pedido.id","=",$idPedido)
		->get();

		foreach ($pedido as $modelo => $value) {
			$refModelo = $value->referenciaMod;
		}
		$tallas = ModelosInfo::where("referenciaMod","=",$refModelo)->get();
		$actividad = ActividadTarea::latest('posicion')->get();

		return $this->renderHTML('addOrden.twig',[
				'pedidos' => $pedido,
				'actividads' => $actividad,
				'tallas' => $tallas
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

					$queryorden = InfoOrdenProduccion::all();
					$ordenUltimo = $queryorden->last();
					$ordenUltimoId = $ordenUltimo->id+1;

					$referenciaOrd=$postData['referenciaOrd'];
					$fechaRegistro=$postData['fechaRegistro'];
					$fechaEntrega=$postData['fechaEntrega'];
					$modeloRef=$postData['modelo'];
					$modeloImg=$postData['modeloImg'];
					$cliente=$postData['cliente'];


					$order = new InfoOrdenProduccion();
					$order->id=$ordenUltimoId;
					$order->referenciaOrd=$referenciaOrd;
					$order->idPedido = $postData['idPedido'];
					$order->idTallas=$tallasUltimoId;
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
						$tarea->idUserRegister = $_SESSION['userId'];
						$tarea->idUserUpdate = $_SESSION['userId'];
						$tarea->save();
					}


					$cantRestante=$postData['cantidadPedido']-$sumatoria;
					$pedido = Pedido::find($postData['idPedido']);
					$pedido->cantRestante=$cantRestante;
					$pedido->save();

					$registroExitoso=true;
					$responseMessage = 'Registrado';
				}catch(\Exception $e){
					$prevMessage = substr($e->getMessage(), 0, 15);
					
					if ($prevMessage =="All of the requ") {
						$responseMessage = 'Error, la referencia debe tener de 1 a 12 digitos.';
					}if ($prevMessage =="SQLSTATE[23000]") {
						$responseMessage = 'Error, esa referencia ya existe.';
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
	<title>Orden/Produccion</title>
	

	<link rel='stylesheet' href='https://stackpath.bootstrapcdn.com/bootstrap/4.2.1/css/bootstrap.min.css' integrity='sha384-GJzZqFGwb1QTTN6wy59ffF1BuGJpLSa9DkKMp0DgiMDm4iYMj70gZWKYbI706tWS' crossorigin='anonymous'>
</head>
<body onload='window.print();'>
<div class='wrapper'>
  <!-- Main content -->
  <section class='invoice'>
    <!-- title row -->
    <div class='row'>
      <div class='col-xs-12'>
        <h2 class='page-header'>
          <i class='fa fa-globe'></i>..Verde Menta,
          <small class='pull-right'>Orden: #$referenciaOrd</small>
        </h2>
      </div>
      <!-- /.col -->
    </div>
    <!-- info row -->
    <div class='row invoice-info'>
      <div class='col-sm-4 invoice-col'>
        
        <address>
          <strong>Cliente: $cliente</strong><br>
          Registro: $fechaRegistro<br>
          Entrega: $fechaEntrega<br>
          Modelo: $modeloRef<br>
        </address>
      </div>
      <!-- /.col -->
      <div class='col-sm-4 invoice-col'>
        
        <address>
          <strong>Tallas</strong><br>
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
      <div class='col-sm-4 invoice-col'>     
        <img src='./uploads/$modeloImg' alt='Imagen/modelo' width='120' height='120'>
      </div>
      <!-- /.col -->
    </div>
    
    <!-- /.row -->

    <!-- Table row -->
    <div class='row'>
      <div class='col-xs-12 table-responsive'>
        <table class='table table-striped'>
          <thead>
          <tr>
            <th></th>
            <th>Material #</th>
            <th>Consumo</th>
            <th>Medida</th>
          </tr>
          </thead>
          <tbody>
          "; 

foreach ($materiales as $key => $val) {
echo "
          <tr>
            <td></td>
            <td>$val->nombre</td>
            <td>$val->consumoPorPar</td>
            <td>$val->unidadMedida</td>
          </tr>
          "; 
}

echo"
          </tbody>
        </table>
      </div>
      <!-- /.col -->
    </div>
    <!-- /.row -->
<h3>Tareas</h3>
    <!-- Segunda tabla -->
    <!-- Table row -->
    <div class='row'>
      <div class='col-xs-12 table-responsive'>
        <table class='table table-striped'>
          <thead>
          <tr>
          	<th></th>
            <th>Orden</th>
            <th>Modelo</th>
            <th>Actividad</th>
            <th>Codigo</th>
            <th>Costo</th>
          </tr>
          </thead>
          <tbody>
          "; 

foreach ($tareas as $tarea => $value) {
	$code = $Bar->getBarcode($value->id,$Bar::TYPE_CODE_128);
	$sumaTarea = $value->valorTarea * $sumatoria;
	echo "
            <tr>
          	<td></td>
            <td>$referenciaOrd</td>
            <td>$modeloRef</td>
            <td>$value->nombre</td>
            <td>$code</td>
            <td>$sumatoria x $value->valorTarea = $ $sumaTarea</td>
            </tr>
          "; 
	
}
          echo "
          </tbody>
        </table>
      </div>
      <!-- /.col -->
    </div>
    <!-- /.row -->

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
		
		$pedido = Pedido::orderBy('referencia')->get();

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
