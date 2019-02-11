<?php 

namespace App\Controllers;

use App\Models\{Pedido, ModelosInfo, Clientes, Ciudad, Tallas, MaterialModelos,fpdf};
use Respect\Validation\Validator as v;
use Zend\Diactoros\Response\RedirectResponse;
use Picqer\Barcode\BarcodeGeneratorHTML;

class PedidoController extends BaseController{
	public function getAddPedidoAction($request){
		$provider=null;
		
		$customer = Clientes::where("tipo","=",0)->orderBy('nombre')->get();
		$city = Ciudad::orderBy('nombre')->get();	

		$idModelo = $_GET['?'] ?? null;
		$model = ModelosInfo::where("id","=",$idModelo)->orderBy('referenciaMod')->get();

		return $this->renderHTML('addPedido.twig',[
				'models' => $model,
				'customers' => $customer,
				'citys' => $city
		]);
	}

	//Registra la Persona
	public function postAddPedidoAction($request){
		$sumatoria=0;
		$registro=false;
		$responseMessage = null;
		$responseMessage2 = null;
		
		if($request->getMethod()=='POST'){
			$postData = $request->getParsedBody();
			
			$pedidoValidator = v::key('referencia', v::stringType()->length(1, 12)->notEmpty());
			
			if($_SESSION['userId']){
				try{
					$pedidoValidator->assert($postData);
					$postData = $request->getParsedBody();
					
					$querytallas = Tallas::all();
					$tallasUltimo = $querytallas->last();
					$tallasUltimoId = $tallasUltimo->id+1;
					

					if ($postData['tipoTallas']==1) {
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

					$pedido = new Pedido();
					$pedido->referencia = $postData['referencia'];
					$pedido->idCliente = $postData['idCliente'];
					$pedido->idCiudad = $postData['idCiudad'];
					$pedido->idModeloInfo=$postData['idModeloInfo'];
					$pedido->idTallas=$tallasUltimoId;
					$pedido->cantidad=$sumatoria;
					$pedido->cantRestante=$sumatoria;
					$pedido->fechaPedido=$postData['fechaPedido'];
					$pedido->fechaEntrega=$postData['fechaEntrega'];
					$pedido->observacion = $postData['observacion'];
					$pedido->idUserRegister = $_SESSION['userId'];
					$pedido->idUserUpdate = $_SESSION['userId'];
					$pedido->save();

					//***informe de consumo de material***
					$informes = MaterialModelos::Join("inventarioMaterial","materialModelos.idInventarioMaterial","=","inventarioMaterial.id")
						->select('materialModelos.*', 'inventarioMaterial.nombre', 'inventarioMaterial.unidadMedida', 'inventarioMaterial.existencia')
						->where("materialModelos.idModeloInfo","=",$postData['idModeloInfo'])
						->get();

					//$Bar = new BarcodeGeneratorHTML();
					//$code = $Bar->getBarcode("123456",$Bar::TYPE_CODE_128);
					//echo $code;

					$pdf = new FPDF();
					$numero=$postData['referencia'];
					$pdf->AliasNbPages();
					$pdf->AddPage();
					$pdf->SetFont('Arial','B',15);
					//Movernos a la derecha
					$pdf->Cell(50);
					//Título
					$pdf->Cell(80,10,'Consumo pedido #'.$numero,1,0,'C');
					//Salto de línea
					$pdf->Ln(20);
					    
					$header=array('Material','Consumo','Existencia','Medida','Por Comprar');
					$pdf->SetY(36);
					$pdf->SetFont('Arial','',12);
					
					foreach($header as $titulo){
					   $pdf->Cell(36,7,$titulo,1);
					}
					$pdf->Ln();

					foreach ($informes as $informe => $value) {
						$nombre=$value->nombre;
						$consumo=$value->consumoPorPar*$sumatoria;
						$existencia=$value->existencia;
						$unidadMedida=$value->unidadMedida;
						$porComprar = $consumo - $value->existencia;
						$pdf->Cell(36,5,$nombre,1);
						$pdf->Cell(36,5,$consumo,1);
						$pdf->Cell(36,5,$existencia,1);
						$pdf->Cell(36,5,$unidadMedida,1);
						$pdf->Cell(36,5,$porComprar,1);
						$pdf->Ln();
					}

					$pdf->Output();

					$registro=true;
					
					$responseMessage = 'Registrado';
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
		if ($responseMessage2) {
			$responseMessage .= $responseMessage2;
		}
		//Retorna a la pagina de registro con un mensaje $responseMessage
		if ($registro==false) {
			return $this->renderHTML('listPedido.twig',[
				'responseMessage' => $responseMessage
			]);
		}
	}

	public function getPdf(){
		
		//require('./fpdf/fpdf.php');

		

	}

	//Lista todas la pedido Ordenando por posicion
	public function getListPedido(){
		
		$pedido = Pedido::Join("clientesProvedores","pedido.idCliente","=","clientesProvedores.id")
		->Join("modelosInfo","pedido.idModeloInfo","=","modelosInfo.id")
		->select('pedido.*', 'clientesProvedores.nombre', 'modelosInfo.referenciaMod')
		->latest('id')
		->get();
		
		$model = ModelosInfo::orderBy('referenciaMod')->get();

		return $this->renderHTML('listPedido.twig', [
			'models' => $model,
			'pedidos'=> $pedido
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
					$shape = new Pedido();
					$shape->destroy($id);
					$responseMessage = "Se elimino el pedido";
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
