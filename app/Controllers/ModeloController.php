<?php 

namespace App\Controllers;

use App\Models\{ModelosInfo, MaterialModelos,InventarioMaterial, Hormas, Pieza};
use Respect\Validation\Validator as v;
use Zend\Diactoros\Response\RedirectResponse;

class ModeloController extends BaseController{
	public function getAddModeloAction(){
		$shape = null; $inventory=null;

		$shape = Hormas::orderBy('referencia')->get();
		$inventory = InventarioMaterial::orderBy('nombre')->get();

		$cantPiezas=$_GET['numPart'] ?? null;

		return $this->renderHTML('addModelo.twig',[
				'shapes' => $shape,
				'inventorys' => $inventory,
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
					$modeloUltimo = $idModelo->last();
					$modeloUltimoId = $modeloUltimo->id+1;
					
					$modelo = new ModelosInfo();
					$modelo->id = $modeloUltimoId;
					$modelo->referenciaMod=$postData['referenciaMod'];
					$modelo->idHorma = $postData['idHorma'];
					$modelo->tallas = $postData['tallas'];
					$modelo->linea = $postData['linea'];
					$modelo->imagenUrl = $imgName;
					$modelo->idUserRegister = $_SESSION['userId'];
					$modelo->idUserUpdate = $_SESSION['userId'];
					$modelo->save();

					
					for ($i=1; $i <= $postData['cantPiezas']; $i++) { 
						
						$consumoPorPar=$postData['consumoPorPar'.$i] ?? null;

						if ($consumoPorPar) {
							$material = new MaterialModelos();
							$material->idModeloInfo=$modeloUltimoId;
							$material->idPieza = 5;
							$material->idInventarioMaterial = $postData['idInventarioMaterial'.$i];
							$material->consumoPorPar = $consumoPorPar;
							$material->observacion = $postData['observacion'.$i];
							$material->idUserRegister = $_SESSION['userId'];
							$material->idUserUpdate = $_SESSION['userId'];
							$material->save();
						}
					}

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

		return $this->renderHTML('listModelo.twig',[
				'responseMessage' => $responseMessage,
				'cantPiezas' => $cantPiezas
		]);
	}

	//Lista todas los modelos Ordenando por posicion
	public function getListModelo(){
		$responseMessage = null;
		
		//$modelo = ModelosInfo::orderBy('referencia')->get();

		$modelo = ModelosInfo::Join("hormas","modelosInfo.idHorma","=","hormas.id")
		->select('modelosInfo.*', 'hormas.referencia')
		->latest('id')
		->get();

		return $this->renderHTML('listModelo.twig', [
			'modelos' => $modelo
		]);
		

		//return $this->renderHTML('listHormas.twig');
	}

	/*Al seleccionar uno de los dos botones (Eliminar o Actualizar) llega a esta accion y verifica cual de los dos botones oprimio si eligio el boton eliminar(del) elimina el registro de where $id Pero
	Si elige actualizar(upd) cambia la ruta del renderHTML y guarda una consulta de los datos del registro a modificar para mostrarlos en formulario de actualizacion llamado updateActOperario.twig y cuando modifica los datos y le da guardar a ese formulaio regresa a esta class y elige la accion getUpdateActivity()*/
	public function postUpdDelModelo($request){
		$shape = null; $part=null; $inventory=null; $material=null;
		$responseMessage = null;
		$quiereActualizar = false;
		$modelos = null;
		$ruta='listModelo.twig';

		if($request->getMethod()=='POST'){
			$postData = $request->getParsedBody();
			
			$id = $postData['id'] ?? false;
			if ($id) {
				if($postData['boton']=='del'){
				  try{
					$modelo = new ModelosInfo();
					$modelo->destroy($id);
					$responseMessage = "Se elimino el modelo";
				  }catch(\Exception $e){
				  	//$responseMessage = $e->getMessage();
				  	$prevMessage = substr($e->getMessage(), 0, 53);
					if ($prevMessage =="SQLSTATE[23000]: Integrity constraint violation: 1451") {
						$responseMessage = 'Error, No se puede eliminar, este modelo esta siendo usado.';
					}
				  }
				}elseif ($postData['boton']=='upd') {
					$quiereActualizar=true;
				}
			}else{
				$responseMessage = 'Debe Seleccionar un modelo';
			}
		}
		
		if ($quiereActualizar){
			//si quiere actualizar hace una consulta where id=$id y la envia por el array del renderHtml
			$modelos = ModelosInfo::find($id);
			$material = MaterialModelos::where("idModeloInfo","=",$id)->get();

			$shape = Hormas::orderBy('referencia')->get();
			$inventory = InventarioMaterial::orderBy('nombre')->get();
			$ruta='updateModelo.twig';
		}else{
			$modelos = ModelosInfo::orderBy('referenciaMod')->get();
		}
		return $this->renderHTML($ruta, [
			'modelos' => $modelos,
			'materiales' => $material,
			'idUpdate' => $id,
			'shapes' => $shape,
			'inventorys' => $inventory,
			'responseMessage' => $responseMessage
		]);
	}

	//en esta accion se registra las modificaciones del registro utiliza metodo post no get
	public function getUpdateModelo($request){
		$imgName = null; $cambioImagen = false;
		$responseMessage = null;
				
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
						$cambioImagen = true;
					}

					//la siguiente linea hace una consulta en la DB y trae el registro where id=$id y lo guarda en actOpe y posteriormente remplaza los valores y con el ->save() guarda la modificacion en la DB
					$idModelo = $postData['id'];
					$modelo = ModelosInfo::find($idModelo);
					
					$modelo->referenciaMod=$postData['referenciaMod'];
					$modelo->idHorma = $postData['idHorma'];
					$modelo->tallas = $postData['tallas'];
					$modelo->linea = $postData['linea'];
					if ($imgName) {
						$modelo->imagenUrl = $imgName;
					}elseif ($cambioImagen == false) {
						$modelo->imagenUrl = $postData['imagenUrl'];
					}

					$modelo->idUserUpdate = $_SESSION['userId'];
					$modelo->save();

					for ($i=0; $i < $postData['cantPiezas']; $i++) { 
						$material = MaterialModelos::find($postData['idMaterial'.$i]);
						$material->idModeloInfo=$idModelo;
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

		$modelos = ModelosInfo::Join("hormas","modelosInfo.idHorma","=","hormas.id")
		->select('modelosInfo.*', 'hormas.referencia')
		->get();
		return $this->renderHTML('listModelo.twig',[
				'modelos' => $modelos,
				'responseMessage' => $responseMessage
		]);
	}
}

?>
