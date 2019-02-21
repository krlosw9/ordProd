<?php 

namespace App\Controllers; 

use App\Models\Proveedores;
use Respect\Validation\Validator as v;
use Zend\Diactoros\Response\RedirectResponse;

class ProveedoresController extends BaseController{
	public function getAddProveedoresAction($request){
		return $this->renderHTML('addProveedores.twig');
	}

	//Registra el Proveedores
	public function postAddProveedoresAction($request){
		$responseMessage = null;
		
		if($request->getMethod()=='POST'){
			$postData = $request->getParsedBody();
			
			$proveedoresValidator = v::key('nombre', v::stringType()->length(1, 30)->notEmpty())
					->key('telefono', v::numeric()->positive()->length(1, 12)->notEmpty());
			
			if($_SESSION['userId']){

				try{

					$proveedoresValidator->assert($postData);
					$postData = $request->getParsedBody();
					
					$provider = new Proveedores();
					
					$provider->nombre = $postData['nombre'];
					$provider->apellido = $postData['apellido'];
					$provider->tipo = '1';
					$provider->telefono = $postData['telefono'];
					$provider->direccion = $postData['direccion'];
					$provider->observacion = $postData['observacion'];
					$provider->idUserRegister = $_SESSION['userId'];
					$provider->idUserUpdate = $_SESSION['userId'];
					$provider->save();
					
					$responseMessage = 'Registrado';
				}catch(\Exception $e){
					$prevMessage = substr($e->getMessage(), 0, 15);
					if ($prevMessage =="These rules mus") {
						$responseMessage = 'Error, El telefono debe tener de 1 a 12 digitos y el Nombre 30 digitos maximo.';
					}
				}
			}
		}

		//Retorna a la pagina de registro con un mensaje $responseMessage
		return $this->renderHTML('addProveedores.twig',[
				'responseMessage' => $responseMessage
		]);
	}

	//Lista todas la Proveedores Ordenando por posicion
	public function getListProveedores(){
		$responseMessage = null;
		
		//Hace una query donde solo regresa los tipo 1(proveedores) y los ordena por el nombre
		$provider = Proveedores::where("tipo","=",1)->orderBy('nombre')->get();


		return $this->renderHTML('listProveedores.twig', [
			'providers' => $provider
		]);
	}

	/*Al seleccionar uno de los dos botones (Eliminar o Actualizar) llega a esta accion y verifica cual de los dos botones oprimio si eligio el boton eliminar(del) elimina el registro de where $id Pero
	Si elige actualizar(upd) cambia la ruta del renderHTML y guarda una consulta de los datos del registro a modificar para mostrarlos en formulario de actualizacion llamado updateActOperario.twig y cuando modifica los datos y le da guardar a ese formulaio regresa a esta class y elige la accion getUpdateActivity()*/
	public function postUpdDelProveedores($request){
		$responseMessage = null;
		$quiereActualizar = false;
		$ruta='listProveedores.twig';

		if($request->getMethod()=='POST'){
			$postData = $request->getParsedBody();
			
			$id = $postData['id'] ?? false;
			if ($id) {
				if($postData['boton']=='del'){
				  try{
					$provider = new Proveedores();
					$provider->destroy($id);
					$responseMessage = "Se elimino el registro del operario";
				  }catch(\Exception $e){
				  	//$responseMessage = $e->getMessage();
				  	$prevMessage = substr($e->getMessage(), 0, 53);
					if ($prevMessage =="SQLSTATE[23000]: Integrity constraint violation: 1451") {
						$responseMessage = 'Error, No se puede eliminar, este proveedor esta siendo usado.';
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
			$providers = Proveedores::find($id);
			$ruta='updateProveedores.twig';
		}else{
			$providers = Proveedores::where("tipo","=",1)->orderBy('nombre')->get();
		}
		return $this->renderHTML($ruta, [
			'providers' => $providers,
			'idUpdate' => $id,
			'responseMessage' => $responseMessage
		]);
	}

	//en esta accion se registra las modificaciones del registro
	public function getUpdateProveedores($request){

		$responseMessage = null;
				
		if($request->getMethod()=='POST'){
			$postData = $request->getParsedBody();

			$proveedoresValidator = v::key('nombre', v::stringType()->length(1, 30)->notEmpty())
					->key('telefono', v::numeric()->positive()->length(1, 12)->notEmpty());

			
			if($_SESSION['userId']){
				try{
					$proveedoresValidator->assert($postData);
					$postData = $request->getParsedBody();

					//la siguiente linea hace una consulta en la DB y trae el registro where id=$id y lo guarda en actOpe y posteriormente remplaza los valores y con el ->save() guarda la modificacion en la DB
					$provider = Proveedores::find($postData['id']);
					$provider->nombre = $postData['nombre'];
					$provider->apellido = $postData['apellido'];
					$provider->telefono = $postData['telefono'];
					$provider->direccion = $postData['direccion'];
					$provider->observacion = $postData['observacion'];
					$provider->idUserUpdate = $_SESSION['userId'];
					$provider->save();
					$responseMessage = 'Actualizado';
				}catch(\Exception $e){
					$prevMessage = substr($e->getMessage(), 0, 15);
					if ($prevMessage =="These rules mus") {
						$responseMessage = 'Error, El telefono debe tener de 1 a 12 digitos y el Nombre 30 digitos maximo.';
					}
				}
			}
		}

		$providers = Proveedores::orderBy('nombre')->get();
		$providers = Proveedores::where("tipo","=",1)->orderBy('nombre')->get();
		return $this->renderHTML('listProveedores.twig',[
				'providers' => $providers,
				'responseMessage' => $responseMessage
		]);
	}
}

?>
