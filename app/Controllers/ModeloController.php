<?php 

namespace App\Controllers;

use App\Models\{ModelosInfo, MaterialModelos, Tallas};
use Respect\Validation\Validator as v;
use Zend\Diactoros\Response\RedirectResponse;

class ModeloController extends BaseController{
	public function getAddModeloAction($request){
		$provider = null;
		//$provider = Proveedores::where("tipo","=",1)->orderBy('nombre')->get();	

		$cantPiezas=$_GET['numPart'] ?? null;

		return $this->renderHTML('addModelo.twig',[
				'providers' => $provider,
				'cantPiezas' => $cantPiezas
		]);
	}

	//Registra la Persona
	public function postAddModeloAction($request){
		$responseMessage = null;
		$provider = null;
		$imgName = null;
		$cantPiezas=$_GET['numPart'] ?? null;

		if($request->getMethod()=='POST'){
			$postData = $request->getParsedBody();
			
			$modeloValidator = v::key('referenciaMod', v::stringType()->length(1, 12)->notEmpty());
			
			
			if($_SESSION['userId']){
				try{
					$modeloValidator->assert($postData);
					$postData = $request->getParsedBody();
					
					$files = $request->getUploadedFiles();
					$fileImg = $files['fileImg'];
					
					if($fileImg->getError() == UPLOAD_ERR_OK){
						$fileName = $fileImg->getClientFilename();
						$imgName = $postData['referenciaMod'].$fileName;
						$fileImg->moveTo("uploads/$imgName");
					}

					$idModelo = ModelosInfo::all();
					$modelUltimo = $idModelo->last();
					$modelUltimoId = $modelUltimo->id+1;
					
					$model = new ModelosInfo();
					$model->id = $modelUltimoId;
					$model->referenciaMod=$postData['referenciaMod'];
					$model->idHorma = $postData['idHorma'];
					$model->tallas = $postData['tallas'];
					$model->linea = $postData['linea'];
					$model->imagenUrl = $imgName;
					$model->idUserRegister = $_SESSION['userId'];
					$model->idUserUpdate = $_SESSION['userId'];
					$model->save();

					$material = new MaterialModelos();
					$material->idModeloInfo=$modelUltimoId;
					$material->idPieza = $postData['idPieza'];
					$material->idInventarioMaterial = $postData['idInventarioMaterial'];
					$material->consumoPorPar = $postData['consumoPorPar'];
					$material->observacion = $postData['observacion'];
					$material->idUserRegister = $_SESSION['userId'];
					$material->idUserUpdate = $_SESSION['userId'];
					$material->save();
					

					$responseMessage = 'Registrado';
				}catch(\Exception $e){
					$prevMessage = substr($e->getMessage(), 0, 15);
					
					if ($prevMessage =="All of the requ") {
						$responseMessage = 'Error, la referencia debe tener de 1 a 12 digitos.';
					}else{
						$responseMessage = $e->getMessage();
						//$responseMessage = substr($e->getMessage(), 0, 50);
					}
				}
			}
		}
		//$provider = Proveedores::where("tipo","=",1)->orderBy('nombre')->get();	

		//Retorna a la pagina de registro con un mensaje $responseMessage
		return $this->renderHTML('addModelo.twig',[
				'responseMessage' => $responseMessage,
				'providers' => $provider,
				'cantPiezas' => $cantPiezas
		]);
	}

	//Lista todas los modelos Ordenando por posicion
	public function getListModelo(){
		$responseMessage = null;
		
		//$model = ModelosInfo::orderBy('referencia')->get();

		$model = ModelosInfo::Join("hormas","modelosInfo.idHorma","=","hormas.id")
		->select('modelosInfo.*', 'hormas.referencia')
		->get();

		return $this->renderHTML('listModelo.twig', [
			'models' => $model
		]);
		

		//return $this->renderHTML('listHormas.twig');
	}

	/*Al seleccionar uno de los dos botones (Eliminar o Actualizar) llega a esta accion y verifica cual de los dos botones oprimio si eligio el boton eliminar(del) elimina el registro de where $id Pero
	Si elige actualizar(upd) cambia la ruta del renderHTML y guarda una consulta de los datos del registro a modificar para mostrarlos en formulario de actualizacion llamado updateActOperario.twig y cuando modifica los datos y le da guardar a ese formulaio regresa a esta class y elige la accion getUpdateActivity()*/
	public function postUpdDelModelo($request){
		$responseMessage = null;
		$quiereActualizar = false;
		$provider = null;
		$ruta='listModelo.twig';

		if($request->getMethod()=='POST'){
			$postData = $request->getParsedBody();
			
			$id = $postData['id'] ?? false;
			if ($id) {
				if($postData['boton']=='del'){
					$shape = new ModelosInfo();
					$shape->destroy($id);
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
			$shapes = ModelosInfo::find($id);
			$provider = Proveedores::where("tipo","=",1)->orderBy('nombre')->get();
			$ruta='updateModelo.twig';
		}else{
			$shapes = ModelosInfo::orderBy('referenciaMod')->get();
		}
		return $this->renderHTML($ruta, [
			'shapes' => $shapes,
			'idUpdate' => $id,
			'providers' => $provider,
			'responseMessage' => $responseMessage
		]);
	}

	//en esta accion se registra las modificaciones del registro
	public function getUpdateModelo($request){

		$responseMessage = null;
				
		if($request->getMethod()=='POST'){
			$postData = $request->getParsedBody();

			$modeloValidator = v::key('referencia', v::stringType()->length(1, 12)->notEmpty());

			
			if($_SESSION['userId']){
				try{
					$modeloValidator->assert($postData);
					$postData = $request->getParsedBody();

					//la siguiente linea hace una consulta en la DB y trae el registro where id=$id y lo guarda en actOpe y posteriormente remplaza los valores y con el ->save() guarda la modificacion en la DB
					$shape = ModelosInfo::find($postData['id']);
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

		$shapes = ModelosInfo::orderBy('referencia')->get();
		return $this->renderHTML('listModelo.twig',[
				'shapes' => $shapes,
				'responseMessage' => $responseMessage
		]);
	}
}

?>
