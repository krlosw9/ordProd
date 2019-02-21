<?php 
 
namespace App\Controllers;

use App\Models\{InfoOrdenProduccion, Pedido, ModelosInfo, ActividadTarea, Tallas,TareaOperario, MaterialModelos, PedidoModelo, Proveedores};
use Respect\Validation\Validator as v;
use Zend\Diactoros\Response\RedirectResponse;
use Picqer\Barcode\BarcodeGeneratorHTML;

class UpdateOrdenProduccionController extends BaseController{
	
	public function postUpdDelOrden($request){
		$responseMessage = null;
		$quiereActualizar = false;
		$provider = null;
		$ruta='listOrden.twig';

		if($request->getMethod()=='POST'){
			$postData = $request->getParsedBody();
			
			$id = $postData['id'] ?? false;
			if ($id) {
				if($postData['boton']=='del'){
				  try{
					$shape = new InfoOrdenProduccion();
					$shape->destroy($id);
					$responseMessage = "Se elimino la Orden";
				  }catch(\Exception $e){
				  	//$responseMessage = $e->getMessage();
				  	$prevMessage = substr($e->getMessage(), 0, 53);
					if ($prevMessage =="SQLSTATE[23000]: Integrity constraint violation: 1451") {
						$responseMessage = 'Error, No se puede eliminar, esta Orden de Produccion esta siendo usada.';
					}
				  }
				}elseif ($postData['boton']=='upd') {
					$quiereActualizar=true;
				}
			}else{
				$responseMessage = 'Debe Seleccionar una orden de produccion';
			}
		}
		
		if ($quiereActualizar){
			//si quiere actualizar hace una consulta where id=$id y la envia por el array del renderHtml
			$shapes = InfoOrdenProduccion::find($id);
			$provider = Proveedores::where("tipo","=",1)->orderBy('nombre')->get();
			$ruta='updateOrden.twig';
		}else{
			$shapes = InfoOrdenProduccion::Join("clientesProvedores","hormas.idProveedor","=","clientesProvedores.id")
			->select('hormas.*', 'clientesProvedores.nombre')
			->get();
		}
		return $this->renderHTML($ruta, [
			'shapes' => $shapes,
			'idUpdate' => $id,
			'providers' => $provider,
			'responseMessage' => $responseMessage
		]);
	}

	//en esta accion se registra las modificaciones del registro
	public function postUpdateOrden($request){

		$responseMessage = null;
				
		if($request->getMethod()=='POST'){
			$postData = $request->getParsedBody();

			$OrdenValidator = v::key('referencia', v::stringType()->length(1, 12)->notEmpty());

			
			if($_SESSION['userId']){
				try{
					$OrdenValidator->assert($postData);
					$postData = $request->getParsedBody();

					//la siguiente linea hace una consulta en la DB y trae el registro where id=$id y lo guarda en actOpe y posteriormente remplaza los valores y con el ->save() guarda la modificacion en la DB
					$shape = InfoOrdenProduccion::find($postData['id']);
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

		$shapes = InfoOrdenProduccion::Join("clientesProvedores","hormas.idProveedor","=","clientesProvedores.id")
		->select('hormas.*', 'clientesProvedores.nombre')
		->get();



		return $this->renderHTML('listOrden.twig',[
				'shapes' => $shapes,
				'responseMessage' => $responseMessage
		]);
	}
}

?>
