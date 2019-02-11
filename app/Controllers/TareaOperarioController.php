<?php 

namespace App\Controllers;

use App\Models\{TareaOperario,InfoOrdenProduccion, Personas};
use Respect\Validation\Validator as v;
use Zend\Diactoros\Response\RedirectResponse;

class TareaOperarioController extends BaseController{
	

	//Lista todas la Personas Ordenando por posicion
	public function getListTareaOperario(){

		return $this->renderHTML('listTareaOperario.twig');
	}

	
	public function postUpdDelTareaOperario($request){
		$responseMessage = null; $refOrden=null;
		$ruta = 'listTareaOperario.twig';

		if($request->getMethod()=='POST'){
			$postData = $request->getParsedBody();
			
			$boton = $postData['boton'] ?? false;
			if ($boton) {
				if($boton=='code'){
					$tareas = TareaOperario::Join("actividadTarea","tareaOperario.idActTarea","=","actividadTarea.id")
					->Join("infoOrdenProduccion","tareaOperario.idInfoOrdenProduccion","=","infoOrdenProduccion.id")
					->select('tareaOperario.*', 'actividadTarea.nombre', 'infoOrdenProduccion.referenciaOrd')
					->where("tareaOperario.id","=",$postData['idCode'])
					->get();
				$ruta = 'updateTareaOperario.twig';

				}elseif ($boton=='ref') {
					$refOrden= $postData['refOrden'];
					$orden = InfoOrdenProduccion::where("referenciaOrd","=",$refOrden)->select('id')->get();
					foreach ($orden as $key => $value) {
						$idOrden = $value->id;
					}


					$tareas = TareaOperario::Join("actividadTarea","tareaOperario.idActTarea","=","actividadTarea.id")
					->select('tareaOperario.*', 'actividadTarea.nombre')
					->where("idInfoOrdenProduccion","=",$idOrden)
					->get();
				$ruta = 'updateTareaOperarioMultiple.twig';
				}
			}else{
				$responseMessage = 'Debe Seleccionar uno de los botones';
			}
		}
		
		$peoples = Personas::where("activoCheck","=",1)->orderBy('nombre')->get();
		return $this->renderHTML($ruta, [
			'tareas' => $tareas,
			'peoples'=> $peoples,
			'refOrdens'=> $refOrden,
			'responseMessage' => $responseMessage
		]);
	}

	//en esta accion se registra las modificaciones del registro
	public function getUpdateTareaOperario($request){

		$responseMessage = null;
				
		if($request->getMethod()=='POST'){
			$postData = $request->getParsedBody();

			
			if($_SESSION['userId']){
				try{					
					$postData = $request->getParsedBody();

					if ($postData['multiple'] == 0) {
					  $tarea=TareaOperario::find($postData['id']);
					  $tarea->idOperario = $postData['idOperario'];
					  $tarea->idUserUpdate = $_SESSION['userId'];
					  $tarea->save();
					}elseif ($postData['multiple'] == 1) {
						for ($i=0; $i < $postData['cantTareas'] ; $i++) { 
						  $tarea=TareaOperario::find($postData['id'.$i]);
						  $tarea->idOperario = $postData['idOperario'.$i];
						  $tarea->idUserUpdate = $_SESSION['userId'];
						  $tarea->save();
						}
					}
					
					
					$responseMessage = 'Registrado';
				}catch(\Exception $e){
					$prevMessage = substr($e->getMessage(), 0, 15);
					if ($prevMessage =="These rules mus") {
						$responseMessage = 'Error, La cedula y el telefono debe tener de 1 a 12 digitos.';
					}
				}
			}
		}

		return $this->renderHTML('listTareaOperario.twig',[
				'responseMessage' => $responseMessage
		]);
	}
}

?>
