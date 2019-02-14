<?php 

namespace App\Controllers;

use App\Models\{InventarioMaterial};
use Respect\Validation\Validator as v;
use Zend\Diactoros\Response\RedirectResponse;

class InventarioMaterialController extends BaseController{
	public function getAddInventarioAction($request){

		return $this->renderHTML('addInventarioMaterial.twig');
	}

	//Registra la Persona
	public function postAddInventarioAction($request){
		$responseMessage = null;
		
		if($request->getMethod()=='POST'){
			$postData = $request->getParsedBody();
			
			$inventarioValidator = v::key('nombre', v::stringType()->length(1, 40)->notEmpty());
			
			if($_SESSION['userId']){
				try{
					$inventarioValidator->assert($postData);
					$postData = $request->getParsedBody();
					
					$inventory = new InventarioMaterial();
					$inventory->nombre = $postData['nombre'];
					$inventory->unidadMedida = $postData['uMedida'];
					$inventory->observacion=$postData['observacion'];
					$inventory->idUserRegister = $_SESSION['userId'];
					$inventory->idUserUpdate = $_SESSION['userId'];
					$inventory->save();
					
					$responseMessage = 'Registrado';
				}catch(\Exception $e){
					$prevMessage = substr($e->getMessage(), 0, 15);
					
					if ($prevMessage =="All of the requ") {
						$responseMessage = 'Error, el nombre debe tener de 1 a 40 digitos.';
					}else{
						$responseMessage = substr($e->getMessage(), 0, 50);
					}
				}
			}
		}
		
		//Retorna a la pagina de registro con un mensaje $responseMessage
		return $this->renderHTML('addInventarioMaterial.twig',[
				'responseMessage' => $responseMessage
		]);
	}

	//Lista todas los materiales Ordenando por posicion
	public function getListInventario(){
		$inventory=null;
		
		$inventory = InventarioMaterial::orderBy('nombre')->get();
		
		return $this->renderHTML('listInventarioMaterial.twig', [
			'inventorys' => $inventory
		]);
	}

	/*Al seleccionar uno de los dos botones (Eliminar o Actualizar) llega a esta accion y verifica cual de los dos botones oprimio si eligio el boton eliminar(del) elimina el registro de where $id Pero
	Si elige actualizar(upd) cambia la ruta del renderHTML y guarda una consulta de los datos del registro a modificar para mostrarlos en formulario de actualizacion llamado updateActOperario.twig y cuando modifica los datos y le da guardar a ese formulaio regresa a esta class y elige la accion getUpdateActivity()*/
	public function postUpdDelInventario($request){
		$responseMessage = null;
		$quiereActualizar = false;
		
		$ruta='listInventarioMaterial.twig';

		if($request->getMethod()=='POST'){
			$postData = $request->getParsedBody();
			
			$id = $postData['id'] ?? false;
			if ($id) {
				if($postData['boton']=='del'){
					$inventory = new InventarioMaterial();
					$inventory->destroy($id);
					$responseMessage = "Se elimino el material";
				}elseif ($postData['boton']=='upd') {
					$quiereActualizar=true;
				}
			}else{
				$responseMessage = 'Debe Seleccionar un modelo';
			}
		}
		
		if ($quiereActualizar){
			//si quiere actualizar hace una consulta where id=$id y la envia por el array del renderHtml
			$inventorys = InventarioMaterial::find($id);
			$ruta='updateInventarioMaterial.twig';
		}else{
			$inventorys = InventarioMaterial::orderBy('nombre')->get();
		}
		return $this->renderHTML($ruta, [
			'inventorys' => $inventorys,
			'idUpdate' => $id,
			'responseMessage' => $responseMessage
		]);
	}

	//en esta accion se registra las modificaciones del registro
	public function getUpdateInventario($request){
		$responseMessage = null;
				
		if($request->getMethod()=='POST'){
			$postData = $request->getParsedBody();

			$inventarioValidator = v::key('nombre', v::stringType()->length(1, 40)->notEmpty());

			
			if($_SESSION['userId']){
				try{
					$inventarioValidator->assert($postData);
					$postData = $request->getParsedBody();

					//la siguiente linea hace una consulta en la DB y trae el registro where id=$id y lo guarda en actOpe y posteriormente remplaza los valores y con el ->save() guarda la modificacion en la DB
					$inventory = InventarioMaterial::find($postData['id']);
					$inventory->nombre = $postData['nombre'];
					$inventory->unidadMedida = $postData['unidadMedida'];
					$inventory->observacion = $postData['observacion'];
					$inventory->idUserUpdate = $_SESSION['userId'];
					$inventory->save();
					$responseMessage = 'Actualizado';
				}catch(\Exception $e){
					$prevMessage = substr($e->getMessage(), 0, 15);
					
					if ($prevMessage =="All of the requ") {
						$responseMessage = 'Error, el referencia debe tener de 1 a 40 digitos.';
					}else{
						$responseMessage = substr($e->getMessage(), 0, 50);
					}
				}
			}
		}

		$inventorys = InventarioMaterial::orderBy('nombre')->get();
		return $this->renderHTML('listInventarioMaterial.twig',[
				'inventorys' => $inventorys,
				'responseMessage' => $responseMessage
		]);
	}
}

?>
