<?php 

namespace App\Controllers;

use App\Models\Personas;
use Respect\Validation\Validator as v;
use Zend\Diactoros\Response\RedirectResponse;

class PersonasController extends BaseController{
	public function getAddPersonasAction($request){
		return $this->renderHTML('addPersonas.twig');
	}

	//Registra la Persona
	public function postAddPersonasAction($request){
		$responseMessage = null;
		
		if($request->getMethod()=='POST'){
			$postData = $request->getParsedBody();
			
			$personasValidator = v::key('nombre', v::stringType()->length(1, 30)->notEmpty())
					->key('cedula', v::numeric()->positive()->length(1, 12)->notEmpty())
					->key('telefono', v::numeric()->positive()->length(1, 12)->notEmpty());
			
			if($_SESSION['userId']){
				try{
					$personasValidator->assert($postData);
					$postData = $request->getParsedBody();
					
					$people = new Personas();
					$people->cedula = $postData['cedula'];
					$people->nombre = $postData['nombre'];
					$people->apellido = $postData['apellido'];
					$people->telefono = $postData['telefono'];
					$people->observacion = $postData['observacion'];
					$people->idUserRegister = $_SESSION['userId'];
					$people->idUserUpdate = $_SESSION['userId'];
					$people->save();
					
					$responseMessage = 'Registrado';
				}catch(\Exception $e){
					$prevMessage = substr($e->getMessage(), 0, 15);
					if ($prevMessage =="These rules mus") {
						$responseMessage = 'Error,      La cedula y el telefono debe tener de 1 a 12 digitos.';
					}
				}
			}
		}

		//Retorna a la pagina de registro con un mensaje $responseMessage
		return $this->renderHTML('addPersonas.twig',[
				'responseMessage' => $responseMessage
		]);
	}

	//Lista todas la Personas Ordenando por posicion
	public function getListPersonas(){
		$responseMessage = null;
		//$people = Personas::all();
		$people = Personas::orderBy('nombre')->get();

		return $this->renderHTML('listPersonas.twig', [
			'peoples' => $people
		]);
	}

	/*Al seleccionar uno de los dos botones (Eliminar o Actualizar) llega a esta accion y verifica cual de los dos botones oprimio si eligio el boton eliminar(del) elimina el registro de where $id Pero
	Si elige actualizar(upd) cambia la ruta del renderHTML y guarda una consulta de los datos del registro a modificar para mostrarlos en formulario de actualizacion llamado updateActOperario.twig y cuando modifica los datos y le da guardar a ese formulaio regresa a esta class y elige la accion getUpdateActivity()*/
	public function postUpdDelPersonas($request){
		$responseMessage = null;
		$quiereActualizar = false;
		$ruta='listPersonas.twig';

		if($request->getMethod()=='POST'){
			$postData = $request->getParsedBody();
			
			$id = $postData['id'] ?? false;
			if ($id) {
				if($postData['boton']=='del'){
				  try{
					$people = new Personas();
					$people->destroy($id);
					$responseMessage = "Se elimino el registro del operario";
				  }catch(\Exception $e){
				  	//$responseMessage = $e->getMessage();
				  	$prevMessage = substr($e->getMessage(), 0, 53);
					if ($prevMessage =="SQLSTATE[23000]: Integrity constraint violation: 1451") {
						$responseMessage = 'Error, No se puede eliminar, esta persona esta siendo usada.';
					}
				  }
				}elseif ($postData['boton']=='upd') {
					$quiereActualizar=true;
				}
			}else{
				$responseMessage = 'Debe Seleccionar un operario';
			}
		}
		
		if ($quiereActualizar){
			//si quiere actualizar hace una consulta where id=$id y la envia por el array del renderHtml
			$peoples = Personas::find($id);
			$ruta='updatePersonas.twig';
		}else{
			$peoples = Personas::orderBy('nombre')->get();
		}
		return $this->renderHTML($ruta, [
			'peoples' => $peoples,
			'idUpdate' => $id,
			'responseMessage' => $responseMessage
		]);
	}

	//en esta accion se registra las modificaciones del registro
	public function getUpdatePersonas($request){

		$responseMessage = null;
				
		if($request->getMethod()=='POST'){
			$postData = $request->getParsedBody();

			$personasValidator = v::key('nombre', v::stringType()->length(1, 30)->					notEmpty())
					->key('cedula', v::numeric()->positive()->length(1, 12)->notEmpty())
					->key('telefono', v::numeric()->positive()->length(1, 12)->notEmpty());

			
			if($_SESSION['userId']){
				try{
					$personasValidator->assert($postData);
					$postData = $request->getParsedBody();

					//la siguiente linea hace una consulta en la DB y trae el registro where id=$id y lo guarda en actOpe y posteriormente remplaza los valores y con el ->save() guarda la modificacion en la DB
					$people = Personas::find($postData['id']);
					$people->cedula = $postData['cedula'];
					$people->nombre = $postData['nombre'];
					$people->apellido = $postData['apellido'];
					$people->telefono = $postData['telefono'];
					$people->observacion = $postData['observacion'];
					//Si el check esta ON envia un 1 si no 0
					$activoCheck = $postData['activoCheck'] ?? false;
					if ($activoCheck) {
						$people->activoCheck = 1;
					}else{
						$people->activoCheck = 0;
					}
					$people->idUserUpdate = $_SESSION['userId'];
					$people->save();
					$responseMessage = 'Actualizado';
				}catch(\Exception $e){
					$prevMessage = substr($e->getMessage(), 0, 15);
					if ($prevMessage =="These rules mus") {
						$responseMessage = 'Error, La cedula y el telefono debe tener de 1 a 12 digitos.';
					}
				}
			}
		}

		$peoples = Personas::orderBy('nombre')->get();
		return $this->renderHTML('listPersonas.twig',[
				'peoples' => $peoples,
				'responseMessage' => $responseMessage
		]);
	}
}

?>
