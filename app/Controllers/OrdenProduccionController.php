<?php 

namespace App\Controllers;

use App\Models\{InfoOrdenProduccion, Pedido, ModelosInfo, ActividadTarea, Tallas,TareaOperario};
use Respect\Validation\Validator as v;
use Zend\Diactoros\Response\RedirectResponse;

class OrdenProduccionController extends BaseController{
	public function getAddOrdenAction(){
		$pedido = null; $refModelo=null; $tallas=null; $actividad=null;

		//$shape = Hormas::orderBy('referencia')->get();
		//$part = Pieza::orderBy('nombre')->get();
		//$inventory = InventarioMaterial::orderBy('nombre')->get();

		$idPedido=$_GET['?'] ?? null;
		$pedido = Pedido::Join("clientesProvedores","pedido.idCliente","=","clientesProvedores.id")
		->Join("modelosInfo","pedido.idModeloInfo","=","modelosInfo.id")
		->select('pedido.*', 'clientesProvedores.nombre', 'modelosInfo.referenciaMod')
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

					$queryorden = Tallas::all();
					$ordenUltimo = $queryorden->last();
					$ordenUltimoId = $ordenUltimo->id+1;

					$order = new InfoOrdenProduccion();
					$order->id=$ordenUltimoId;
					$order->referenciaOrd=$postData['referenciaOrd'];
					$order->idPedido = $postData['idPedido'];
					$order->idTallas=$tallasUltimoId;
					$order->fechaRegistro=$postData['fechaRegistro'];
					$order->fechaEntrega=$postData['fechaEntrega'];
					$order->idUserRegister = $_SESSION['userId'];
					$order->idUserUpdate = $_SESSION['userId'];
					$order->save();

					
					for($i=0;$i<$postData['cantActividades'];$i++){
						$tarea = new TareaOperario();
						$tarea->idActTarea=$postData['idActividad'.$i];
						$tarea->idInfoOrdenProduccion=$ordenUltimoId;
						$tarea->valorTarea=$postData['valorActividad'.$i];
						$tarea->idUserRegister = $_SESSION['userId'];
						$tarea->idUserUpdate = $_SESSION['userId'];
						$tarea->save();
					}


					$cantRestante=$postData['cantidadPedido']-$sumatoria;
					$pedido = Pedido::find($postData['idPedido']);
					$pedido->cantRestante=$cantRestante;
					$pedido->save();

					$responseMessage = 'Registrado';
				}catch(\Exception $e){
					$prevMessage = substr($e->getMessage(), 0, 15);
					
					if ($prevMessage =="All of the requ") {
						$responseMessage = 'Error, la referencia debe tener de 1 a 12 digitos.';
					}else{
						//$responseMessage = $e->getMessage();
						$responseMessage = substr($e->getMessage(), 0, 50);
					}
				}
			}
		}

		return $this->renderHTML('listOrden.twig',[
				'responseMessage' => $responseMessage,
				'cantPiezas' => $cantPiezas
		]);
	}

	//Lista todas los modelos Ordenando por posicion
	public function getListOrden(){
		$responseMessage = null;
		
		$pedido = Pedido::orderBy('referencia')->get();

		$orden = InfoOrdenProduccion::Join("pedido","infoOrdenProduccion.idPedido","=","pedido.id")
		->select('infoOrdenProduccion.*', 'pedido.referencia')
		->get();

		return $this->renderHTML('listOrden.twig', [
			'ordens' => $orden,
			'pedidos' => $pedido
		]);
		

		//return $this->renderHTML('listHormas.twig');
	}

	/*Al seleccionar uno de los dos botones (Eliminar o Actualizar) llega a esta accion y verifica cual de los dos botones oprimio si eligio el boton eliminar(del) elimina el registro de where $id Pero
	Si elige actualizar(upd) cambia la ruta del renderHTML y guarda una consulta de los datos del registro a modificar para mostrarlos en formulario de actualizacion llamado updateActOperario.twig y cuando modifica los datos y le da guardar a ese formulaio regresa a esta class y elige la accion getUpdateActivity()*/
	public function postUpdDelOrden($request){
		$shape = null; $part=null; $inventory=null; $material=null;
		$responseMessage = null;
		$quiereActualizar = false;
		$ordens = null;
		$ruta='listOrden.twig';

		if($request->getMethod()=='POST'){
			$postData = $request->getParsedBody();
			
			$id = $postData['id'] ?? false;
			if ($id) {
				if($postData['boton']=='del'){
					$orden = new ModelosInfo();
					$orden->destroy($id);
					$responseMessage = "Se elimino el modelo";
				}elseif ($postData['boton']=='upd') {
					$quiereActualizar=true;
				}
			}else{
				$responseMessage = 'Debe Seleccionar un modelo';
			}
		}
		
		if ($quiereActualizar){
			//si quiere actualizar hace una consulta where id=$id y la envia por el array del renderHtml
			$ordens = ModelosInfo::find($id);
			$material = MaterialModelos::where("idModeloInfo","=",$id)->get();

			$shape = Hormas::orderBy('referencia')->get();
			$part = Pieza::orderBy('nombre')->get();
			$inventory = InventarioMaterial::orderBy('nombre')->get();
			$ruta='updateOrden.twig';
		}else{
			$ordens = ModelosInfo::orderBy('referenciaMod')->get();
		}
		return $this->renderHTML($ruta, [
			'ordens' => $ordens,
			'materiales' => $material,
			'idUpdate' => $id,
			'shapes' => $shape,
			'parts' => $part,
			'inventorys' => $inventory,
			'responseMessage' => $responseMessage
		]);
	}

	//en esta accion se registra las modificaciones del registro utiliza metodo post no get
	public function getUpdateOrden($request){
		$imgName = null;
		$responseMessage = null;
				
		if($request->getMethod()=='POST'){
			$postData = $request->getParsedBody();

			$ordenValidator = v::key('referenciaMod', v::stringType()->length(1, 12)->notEmpty());

			
			if($_SESSION['userId']){
				try{
					$ordenValidator->assert($postData);
					$postData = $request->getParsedBody();

					

					//la siguiente linea hace una consulta en la DB y trae el registro where id=$id y lo guarda en actOpe y posteriormente remplaza los valores y con el ->save() guarda la modificacion en la DB
					$idOrden = $postData['id'];
					$orden = ModelosInfo::find($idOrden);
					
					$orden->referenciaMod=$postData['referenciaMod'];
					$orden->idHorma = $postData['idHorma'];
					$orden->tallas = $postData['tallas'];
					$orden->linea = $postData['linea'];
					if ($imgName) {
						$orden->imagenUrl = $imgName;
					}
					$orden->idUserUpdate = $_SESSION['userId'];
					$orden->save();

					for ($i=0; $i < $postData['cantPiezas']; $i++) { 
						$material = MaterialModelos::find($postData['idMaterial'.$i]);
						$material->idModeloInfo=$idOrden;
						$material->idPieza = $postData['idPieza'.$i];
						$material->idInventarioMaterial = $postData['idInventarioMaterial'.$i];
						$material->consumoPorPar = $postData['consumoPorPar'.$i];
						$material->observacion = $postData['observacion'.$i];
						$material->idUserRegister = $_SESSION['userId'];
						$material->idUserUpdate = $_SESSION['userId'];
						$material->save();
					}

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

		$ordens = ModelosInfo::Join("hormas","modelosInfo.idHorma","=","hormas.id")
		->select('modelosInfo.*', 'hormas.referencia')
		->get();
		return $this->renderHTML('listOrden.twig',[
				'ordens' => $ordens,
				'responseMessage' => $responseMessage
		]);
	}
}

?>
