<?php 

namespace App\Controllers;

use App\Models\{ModelosInfo, MaterialModelos,InventarioMaterial, Hormas, Pieza, Linea};
use Respect\Validation\Validator as v;
use Zend\Diactoros\Response\RedirectResponse;

class ModeloController extends BaseController{
	
	//estos dos valores son los que se cambian, para modificar la cantidad de registros listados por pagina y el maximo numero en paginacion
	private $articulosPorPagina=15;
	private $limitePaginacion=20;

	public function getAddModeloAction(){
		$shape = null; $inventory=null;

		$shape = Hormas::orderBy('referencia')->get();
		$line = Linea::orderBy('nombreLinea')->get();
		$inventory = InventarioMaterial::orderBy('nombre')->get();

		$cantPiezas=$_GET['numPart'] ?? null;

		return $this->renderHTML('addModelo.twig',[
				'shapes' => $shape,
				'lines' => $line,
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
			
			$modeloValidator = v::key('referenciaMod', v::stringType()->noWhitespace()->length(1, 10)->notEmpty());
			
			
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
					$modelo->observacion = $postData['observacionMod'];
					$modelo->idUserRegister = $_SESSION['userId'];
					$modelo->idUserUpdate = $_SESSION['userId'];
					$modelo->save();

					
					for ($i=1; $i <= $postData['cantPiezas']; $i++) { 
						
						$consumoPorPar=$postData['consumoPorPar'.$i] ?? null;

						if ($consumoPorPar) {
							$material = new MaterialModelos();
							$material->idModeloInfo=$modeloUltimoId;
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
						$responseMessage = 'Error, la referencia no puede contener espacios en blanco.';
					}elseif ($prevMessage =="SQLSTATE[23000]") {
						$responseMessage = 'Error, Las referencias deben ser diferentes y esta referencia ya existe';
					}else{
						//$responseMessage = $e->getMessage();
						$responseMessage = substr($e->getMessage(), 0, 50);
					}
				}
			}
		}
		$iniciar=0;
		$modelo = ModelosInfo::Join("hormas","modelosInfo.idHorma","=","hormas.id")
		->Join("linea","modelosInfo.linea","=","linea.id")
		->select('modelosInfo.*', 'hormas.referencia', 'linea.nombreLinea')
		->latest('id')
		->limit($this->articulosPorPagina)->offset($iniciar)
		->get();

		return $this->renderHTML('listModelo.twig',[
				'responseMessage' => $responseMessage,
				'cantPiezas' => $cantPiezas,
				'modelos' => $modelo
		]);
	}

	//Lista todas los modelos Ordenando por posicion
	public function getListModelo(){
		$responseMessage = null; $iniciar=0;
		
		$numeroDeFilas = ModelosInfo::selectRaw('count(*) as query_count')
		->first();
		
		$totalFilasDb = $numeroDeFilas->query_count;
		$numeroDePaginas = $totalFilasDb/$this->articulosPorPagina;
		$numeroDePaginas = ceil($numeroDePaginas);

		//No permite que haya muchos botones de paginar y de esa forma va a traer una cantidad limitada de registro, no queremos que se pagine hasta el infinito, porque tambien puede ser molesto.
		if ($numeroDePaginas > $this->limitePaginacion) {
			$numeroDePaginas=$this->limitePaginacion;
		}

		$paginaActual = $_GET['pag'] ?? null;
		if ($paginaActual) {
			if ($paginaActual > $numeroDePaginas or $paginaActual < 1) {
				$paginaActual = 1;
			}
			$iniciar = ($paginaActual-1)*$this->articulosPorPagina;
		}

		$modelo = ModelosInfo::Join("hormas","modelosInfo.idHorma","=","hormas.id")
		->Join("linea","modelosInfo.linea","=","linea.id")
		->select('modelosInfo.*', 'hormas.referencia', 'linea.nombreLinea')
		->latest('id')
		->limit($this->articulosPorPagina)->offset($iniciar)
		->get();

		return $this->renderHTML('listModelo.twig', [
			'modelos' => $modelo,
			'numeroDePaginas' => $numeroDePaginas,
			'paginaActual' => $paginaActual
		]);
		
	}

	/*Al seleccionar uno de los dos botones (Eliminar o Actualizar) llega a esta accion y verifica cual de los dos botones oprimio si eligio el boton eliminar(del) elimina el registro de where $id Pero
	Si elige actualizar(upd) cambia la ruta del renderHTML y guarda una consulta de los datos del registro a modificar para mostrarlos en formulario de actualizacion llamado updateActOperario.twig y cuando modifica los datos y le da guardar a ese formulaio regresa a esta class y elige la accion getUpdateActivity()*/
	public function postUpdDelModelo($request){
		$shape = null; $part=null; $inventory=null; $material=null;
		$responseMessage = null; $line=null;
		$quiereActualizar = false;
		$modelos = null;
		$ruta='listModelo.twig';

		if($request->getMethod()=='POST'){
			$postData = $request->getParsedBody();
			
			$id = $postData['id'] ?? false;
			if ($id) {
				if($postData['boton']=='del'){
				  try{
					$materiales = MaterialModelos::where("idModeloInfo","=",$id)->get();
					foreach ($materiales as $material) {
						$materialDel = new MaterialModelos();
						$materialDel->destroy($material->id);
					}
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
			$line = Linea::orderBy('nombreLinea')->get();
			$inventory = InventarioMaterial::orderBy('nombre')->get();
			$ruta='updateModelo.twig';
		}else{
			//$modelos = ModelosInfo::orderBy('referenciaMod')->get();

			$iniciar=0;
			$modelos = ModelosInfo::Join("hormas","modelosInfo.idHorma","=","hormas.id")
			->Join("linea","modelosInfo.linea","=","linea.id")
			->select('modelosInfo.*', 'hormas.referencia', 'linea.nombreLinea')
			->latest('id')
			->limit($this->articulosPorPagina)->offset($iniciar)
			->get();
		}
		return $this->renderHTML($ruta, [
			'modelos' => $modelos,
			'materiales' => $material,
			'idUpdate' => $id,
			'shapes' => $shape,
			'lines' => $line,
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

			$modeloValidator = v::key('referenciaMod', v::stringType()->length(1, 10)->notEmpty());

			
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
					$modelo->observacion = $postData['observacionMod'];
					if ($imgName) {
						$modelo->imagenUrl = $imgName;
					}elseif ($cambioImagen == false) {
						$modelo->imagenUrl = $postData['imagenUrl'];
					}

					$modelo->idUserUpdate = $_SESSION['userId'];
					$modelo->save();

					for ($i=0; $i < $postData['cantPiezas']; $i++) { 
						if ($postData['eliminarMaterial'.$i]==0) {
							$material = MaterialModelos::find($postData['idMaterial'.$i]);
							$material->idModeloInfo=$idModelo;
							$material->idInventarioMaterial = $postData['idInventarioMaterial'.$i];
							$material->consumoPorPar = $postData['consumoPorPar'.$i];
							$material->observacion = $postData['observacion'.$i];
							$material->idUserRegister = $_SESSION['userId'];
							$material->idUserUpdate = $_SESSION['userId'];
							$material->save();	
						}elseif ($postData['eliminarMaterial'.$i]==1) {
							$materialDel = new MaterialModelos();
							$materialDel->destroy($postData['idMaterial'.$i]);
						}
					}

					//De esta forma se registran los 3 nuevos materiales y si consuPorPar queda vacio, no se registra
					for ($iterador=1; $iterador <= $postData['cantPiezasNew']; $iterador++) { 
						
						$consumoPorPar=$postData['consumoPorParNew'.$iterador] ?? null;

						if ($consumoPorPar) {
							$material = new MaterialModelos();
							$material->idModeloInfo=$idModelo;
							$material->idInventarioMaterial = $postData['idInventarioMaterialNew'.$iterador];
							$material->consumoPorPar = $consumoPorPar;
							$material->observacion = $postData['observacionNew'.$iterador];
							$material->idUserRegister = $_SESSION['userId'];
							$material->idUserUpdate = $_SESSION['userId'];
							$material->save();
						}
					}

					$responseMessage = 'Actualizado';
				}catch(\Exception $e){
					$prevMessage = substr($e->getMessage(), 0, 15);
					
					if ($prevMessage =="All of the requ") {
						$responseMessage = 'Error, la referencia debe tener de 1 a 10 digitos.';
					}elseif ($prevMessage =="SQLSTATE[23000]") {
						$responseMessage = 'Error, Las referencias deben ser diferentes y esta referencia ya existe';
					}else{
						$responseMessage = substr($e->getMessage(), 0, 40);
					}
				}
			}
		}


		$iniciar=0;
		$modelos = ModelosInfo::Join("hormas","modelosInfo.idHorma","=","hormas.id")
		->Join("linea","modelosInfo.linea","=","linea.id")
		->select('modelosInfo.*', 'hormas.referencia', 'linea.nombreLinea')
		->latest('id')
		->limit($this->articulosPorPagina)->offset($iniciar)
		->get();

		return $this->renderHTML('listModelo.twig',[
				'modelos' => $modelos,
				'responseMessage' => $responseMessage
		]);
	}
}

?>
