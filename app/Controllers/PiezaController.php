<?php 

namespace App\Controllers;

use App\Models\Pieza;
use Respect\Validation\Validator as v;
use Zend\Diactoros\Response\RedirectResponse;

class PiezaController extends BaseController{
	public function getAddPiezaAction($request){
		return $this->renderHTML('addPieza.twig');
	}

	//Registra la Pieza
	public function postAddPiezaAction($request){
		$responseMessage = null;
		
		if($request->getMethod()=='POST'){
			$postData = $request->getParsedBody();
			
			$piezaValidator = v::key('nombre', v::stringType()->length(1, 30)->notEmpty());
			
			if($_SESSION['userId']){
				try{
					$piezaValidator->assert($postData);
					$postData = $request->getParsedBody();
					
					$part = new Pieza();
					$part->piezaNombre = $postData['nombre'];
					$part->observacion = $postData['observacion'];
					$part->idUserRegister = $_SESSION['userId'];
					$part->idUserUpdate = $_SESSION['userId'];
					$part->save();
					
					$responseMessage = 'Registrado';
				}catch(\Exception $e){
					$prevMessage = substr($e->getMessage(), 0, 15);
					if ($prevMessage =="These rules mus") {
						$responseMessage = 'Error, el nombre de la pieza no puede tener mas de 30 digitos.';
					}
				}
			}
		}

		//Retorna a la pagina de registro con un mensaje $responseMessage
		return $this->renderHTML('addPieza.twig',[
				'responseMessage' => $responseMessage
		]);
	}

	//Lista todas la Pieza Ordenando por posicion
	public function getListPieza(){ 
		$responseMessage = null;
		
		$part = Pieza::orderBy('piezaNombre')->get();

		return $this->renderHTML('listPieza.twig', [
			'parts' => $part
		]);
	}

	/*Al seleccionar uno de los dos botones (Eliminar o Actualizar) llega a esta accion y verifica cual de los dos botones oprimio si eligio el boton eliminar(del) elimina el registro de where $id Pero
	Si elige actualizar(upd) cambia la ruta del renderHTML y guarda una consulta de los datos del registro a modificar para mostrarlos en formulario de actualizacion llamado updateActOperario.twig y cuando modifica los datos y le da guardar a ese formulaio regresa a esta class y elige la accion getUpdateActivity()*/
	public function postUpdDelPieza($request){
		$responseMessage = null;
		$quiereActualizar = false;
		$ruta='listPieza.twig';

		if($request->getMethod()=='POST'){
			$postData = $request->getParsedBody();
			
			$id = $postData['id'] ?? false;
			if ($id) {
				if($postData['boton']=='del'){
				  try{
					$part = new Pieza();
					$part->destroy($id);
					$responseMessage = "Se elimino la pieza";
				  }catch(\Exception $e){
				  	//$responseMessage = $e->getMessage();
				  	$prevMessage = substr($e->getMessage(), 0, 53);
					if ($prevMessage =="SQLSTATE[23000]: Integrity constraint violation: 1451") {
						$responseMessage = 'Error, No se puede eliminar, esta pieza esta siendo usada.';
					}
				  }
				}elseif ($postData['boton']=='upd') {
					$quiereActualizar=true;
				}
			}else{
				$responseMessage = 'Debe Seleccionar una pieza';
			}
		}
		
		if ($quiereActualizar){
			//si quiere actualizar hace una consulta where id=$id y la envia por el array del renderHtml
			$parts = Pieza::find($id);
			$ruta='updatePieza.twig';
		}else{
			$parts = Pieza::orderBy('piezaNombre')->get();
		}
		return $this->renderHTML($ruta, [
			'parts' => $parts,
			'idUpdate' => $id,
			'responseMessage' => $responseMessage
		]);
	}

	//en esta accion se registra las modificaciones del registro
	public function getUpdatePieza($request){

		$responseMessage = null;
				
		if($request->getMethod()=='POST'){
			$postData = $request->getParsedBody();

			$piezaValidator = v::key('nombre', v::stringType()->length(1, 30)					->notEmpty());

			
			if($_SESSION['userId']){
				try{
					$piezaValidator->assert($postData);
					$postData = $request->getParsedBody();

					//la siguiente linea hace una consulta en la DB y trae el registro where id=$id y lo guarda en actOpe y posteriormente remplaza los valores y con el ->save() guarda la modificacion en la DB
					$part = Pieza::find($postData['id']);
					$part->piezaNombre = $postData['nombre'];
					$part->observacion = $postData['observacion'];
					$part->idUserUpdate = $_SESSION['userId'];
					$part->save();
					$responseMessage = 'Actualizado';
				}catch(\Exception $e){
					$prevMessage = substr($e->getMessage(), 0, 15);
					if ($prevMessage =="These rules mus") {
						$responseMessage = 'Error, el nombre de la pieza no puede tener mas de 30 digitos.';
					}
				}
			}
		}

		$parts = Pieza::orderBy('piezaNombre')->get();
		return $this->renderHTML('listPieza.twig',[
				'parts' => $parts,
				'responseMessage' => $responseMessage
		]);
	}
}

?>
