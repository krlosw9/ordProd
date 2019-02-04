<?php 

namespace App\Controllers;

use App\Models\Hormas;
use Respect\Validation\Validator as v;
use Zend\Diactoros\Response\RedirectResponse;

class HormasController extends BaseController{
	public function getAddHormasAction($request){
		return $this->renderHTML('addHormas.twig');
	}

	//Registra la Persona
	public function postAddHormasAction($request){
		$responseMessage = null;
		
		if($request->getMethod()=='POST'){
			$postData = $request->getParsedBody();
			
			$hormasValidator = v::key('referencia', v::stringType()->length(1, 12)->notEmpty());
			
			if($_SESSION['userId']){
				try{
					$hormasValidator->assert($postData);
					$postData = $request->getParsedBody();
					
					$shape = new Hormas();
					$shape->referencia = $postData['referencia'];
					$shape->genero = $postData['genero'];
					$shape->color = $postData['color'];
					$shape->idProveedor = $postData['idProveedor'];
					$shape->observacion = $postData['observacion'];
					$shape->idUserRegister = $_SESSION['userId'];
					$shape->idUserUpdate = $_SESSION['userId'];
					$shape->save();
					
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

		//Retorna a la pagina de registro con un mensaje $responseMessage
		return $this->renderHTML('addHormas.twig',[
				'responseMessage' => $responseMessage
		]);
	}

	//Lista todas la Hormas Ordenando por posicion
	public function getListHormas(){
		$responseMessage = null;
		
		$shape = Hormas::orderBy('referencia')->get();

		return $this->renderHTML('listHormas.twig', [
			'shapes' => $shape
		]);
		

		//return $this->renderHTML('listHormas.twig');
	}

	/*Al seleccionar uno de los dos botones (Eliminar o Actualizar) llega a esta accion y verifica cual de los dos botones oprimio si eligio el boton eliminar(del) elimina el registro de where $id Pero
	Si elige actualizar(upd) cambia la ruta del renderHTML y guarda una consulta de los datos del registro a modificar para mostrarlos en formulario de actualizacion llamado updateActOperario.twig y cuando modifica los datos y le da guardar a ese formulaio regresa a esta class y elige la accion getUpdateActivity()*/
	public function postUpdDelHormas($request){
		$responseMessage = null;
		$quiereActualizar = false;
		$ruta='listHormas.twig';

		if($request->getMethod()=='POST'){
			$postData = $request->getParsedBody();
			
			$id = $postData['id'] ?? false;
			if ($id) {
				if($postData['boton']=='del'){
					$shape = new Hormas();
					$shape->destroy($id);
					$responseMessage = "Se elimino la horma";
				}elseif ($postData['boton']=='upd') {
					$quiereActualizar=true;
				}
			}else{
				$responseMessage = 'Debe Seleccionar una horma';
			}
		}
		
		if ($quiereActualizar){
			//si quiere actualizar hace una consulta where id=$id y la envia por el array del renderHtml
			$shapes = Hormas::find($id);
			$ruta='updateHormas.twig';
		}else{
			$shapes = Hormas::orderBy('referencia')->get();
		}
		return $this->renderHTML($ruta, [
			'shapes' => $shapes,
			'idUpdate' => $id,
			'responseMessage' => $responseMessage
		]);
	}

	//en esta accion se registra las modificaciones del registro
	public function getUpdateHormas($request){

		$responseMessage = null;
				
		if($request->getMethod()=='POST'){
			$postData = $request->getParsedBody();

			$hormasValidator = v::key('referencia', v::stringType()->length(1, 12)->notEmpty());

			
			if($_SESSION['userId']){
				try{
					$hormasValidator->assert($postData);
					$postData = $request->getParsedBody();

					//la siguiente linea hace una consulta en la DB y trae el registro where id=$id y lo guarda en actOpe y posteriormente remplaza los valores y con el ->save() guarda la modificacion en la DB
					$shape = Hormas::find($postData['id']);
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

		$shapes = Hormas::orderBy('referencia')->get();
		return $this->renderHTML('listHormas.twig',[
				'shapes' => $shapes,
				'responseMessage' => $responseMessage
		]);
	}
}

?>
