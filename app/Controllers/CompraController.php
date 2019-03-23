<?php 

namespace App\Controllers;

use App\Models\{ComprasInfo, ArticuloCompra,InventarioMaterial, Proveedores};
use Respect\Validation\Validator as v;
use Zend\Diactoros\Response\RedirectResponse;

class CompraController extends BaseController{
	public function getAddCompraAction(){
		$provider = null; $inventory=null;

		$provider = Proveedores::where("tipo","=",1)->orderBy('nombre')->get();
		$inventory = InventarioMaterial::orderBy('nombre')->get();
		
		$cantPiezas=$_GET['numPart'] ?? null;

		return $this->renderHTML('addCompra.twig',[
				'providers' => $provider,
				'inventorys' => $inventory,
				'cantPiezas' => $cantPiezas
		]);
	}

	//Registra la Persona
	public function postAddCompraAction($request){
		$sumatoria=0; $cantidadxPrecio=0;
		$responseMessage = null;
		$provider = null;
		$cantPiezas=$_GET['numPart'] ?? null;

		if($request->getMethod()=='POST'){
			$postData = $request->getParsedBody();
			
			
			$compraValidator = v::key('refFactura', v::stringType()->length(1, 12)->notEmpty());
			
			
			if($_SESSION['userId']){
				try{
					$compraValidator->assert($postData);
					$postData = $request->getParsedBody();
					

					$idCompra = ComprasInfo::all();
					$compraUltimo = $idCompra->last();
					$compraUltimoId = $compraUltimo->id+1;
					
					$compra = new ComprasInfo();
					$compra->id = $compraUltimoId;
					$compra->refFactura=$postData['refFactura'];
					$compra->fechaCompra = $postData['fechaCompra'];
					$compra->idProveedor = $postData['idProveedor'];
					$compra->observacion = $postData['observacion'];
					$compra->idUserRegister = $_SESSION['userId'];
					$compra->idUserUpdate = $_SESSION['userId'];
					$compra->save();

					
					for ($i=1; $i <= $postData['cantPiezas']; $i++) { 
						//se pasa por el value del select de idInventarioMaterial el id y la existencia separada por un espacio y aca en el controlador se hace explode para dividir
						$divideCadena = explode(" ", $postData['idInventarioMaterial'.$i]);
						$idMaterial=$divideCadena[0];
						$existenciaMaterial=$divideCadena[1];
						$existenciaMaterial+=$postData['cantidad'.$i];
						$articulo = new ArticuloCompra();
						$articulo->idInventarioMaterial=$idMaterial;
						$articulo->idCompraInfo=$compraUltimoId;
						$articulo->cantidad=$postData['cantidad'.$i];
						$articulo->precio=$postData['precio'.$i];
						$articulo->idUserRegister=$_SESSION['userId'];
						$articulo->idUserUpdate=$_SESSION['userId'];
						$articulo->save();

						$materialUpdate=InventarioMaterial::find($idMaterial);
						$materialUpdate->existencia=$existenciaMaterial;
						$materialUpdate->save();

						$cantidadxPrecio = $postData['cantidad'.$i] * $postData['precio'.$i];
						$sumatoria += $cantidadxPrecio;
					}

					$compraUpdate=ComprasInfo::find($compraUltimoId);
					$compraUpdate->total=$sumatoria;
					$compraUpdate->save();

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

		return $this->renderHTML('listCompra.twig',[
				'responseMessage' => $responseMessage,
				'cantPiezas' => $cantPiezas
		]);
	}

	//Lista todas las compras Ordenando por posicion
	public function getListCompra(){
		$responseMessage = null;
		
		//$compra = CompraInfo::orderBy('referencia')->get();

		$compra = ComprasInfo::Join("clientesProvedores","comprasInfo.idProveedor","=","clientesProvedores.id")
		->select('comprasInfo.*', 'clientesProvedores.nombre')
		->get();

		return $this->renderHTML('listCompra.twig', [
			'compras' => $compra
		]);
		

		//return $this->renderHTML('listHormas.twig');
	}

	/*Al seleccionar uno de los dos botones (Eliminar o Actualizar) llega a esta accion y verifica cual de los dos botones oprimio si eligio el boton eliminar(del) elimina el registro de where $id Pero
	Si elige actualizar(upd) cambia la ruta del renderHTML y guarda una consulta de los datos del registro a modificar para mostrarlos en formulario de actualizacion llamado updateActOperario.twig y cuando modifica los datos y le da guardar a ese formulaio regresa a esta class y elige la accion getUpdateActivity()*/
	public function postUpdDelCompra($request){
		$shape = null; $part=null; $inventory=null; $articulo=null;
		$responseMessage = null;
		$quiereActualizar = false;
		$compras = null;
		$ruta='listCompra.twig';

		if($request->getMethod()=='POST'){
			$postData = $request->getParsedBody();
			
			$id = $postData['id'] ?? false;
			if ($id) {
				if($postData['boton']=='del'){
				  try{
					$compra = new ComprasInfo();
					$compra->destroy($id);
					$responseMessage = "Se elimino la compra";
				  }catch(\Exception $e){
				  	//$responseMessage = $e->getMessage();
				  	$prevMessage = substr($e->getMessage(), 0, 53);
					if ($prevMessage =="SQLSTATE[23000]: Integrity constraint violation: 1451") {
						$responseMessage = 'Error, No se puede eliminar, esta compra esta siendo usada.';
					}
				  }
				}elseif ($postData['boton']=='upd') {
					$quiereActualizar=true;
				}
			}else{
				$responseMessage = 'Debe Seleccionar una compra';
			}
		}
		
		if ($quiereActualizar){
			//si quiere actualizar hace una consulta where id=$id y la envia por el array del renderHtml
			$compras = ComprasInfo::find($id);
			$articulo = ArticuloCompra::where("idCompraInfo","=",$id)->get();

			/*
			$shape = Hormas::orderBy('referencia')->get();
			$part = Pieza::orderBy('nombre')->get();
			$inventory = InventarioMaterial::orderBy('nombre')->get();
			*/
			$ruta='updateCompra.twig';
		}else{
			$compras = ComprasInfo::orderBy('referenciaMod')->get();
		}
		return $this->renderHTML($ruta, [
			'compras' => $compras,
			'articulos' => $articulo,
			'idUpdate' => $id,
			'shapes' => $shape,
			'parts' => $part,
			'inventorys' => $inventory,
			'responseMessage' => $responseMessage
		]);
	}

	//en esta accion se registra las modificaciones del registro utiliza metodo post no get
	public function postUpdateCompra($request){
		$imgName = null;
		$responseMessage = null;
				
		if($request->getMethod()=='POST'){
			$postData = $request->getParsedBody();

			$compraValidator = v::key('refFactura', v::stringType()->length(1, 12)->notEmpty());

			
			if($_SESSION['userId']){
				try{
					$compraValidator->assert($postData);
					$postData = $request->getParsedBody();

					$files = $request->getUploadedFiles();
					$fileImg = $files['fileImg'];
					
					if($fileImg->getError() == UPLOAD_ERR_OK){
						$fileName = $fileImg->getClientFilename();
						$imgName = $postData['referenciaMod'].$fileName;
						$fileImg->moveTo("uploads/$imgName");
					}

					//la siguiente linea hace una consulta en la DB y trae el registro where id=$id y lo guarda en actOpe y posteriormente remplaza los valores y con el ->save() guarda la modificacion en la DB
					$idCompra = $postData['id'];
					$compra = ComprasInfo::find($idCompra);
					
					$compra->referenciaMod=$postData['referenciaMod'];
					$compra->idHorma = $postData['idHorma'];
					$compra->tallas = $postData['tallas'];
					$compra->linea = $postData['linea'];
					if ($imgName) {
						$compra->imagenUrl = $imgName;
					}
					$compra->idUserUpdate = $_SESSION['userId'];
					//$compra->save();

					for ($i=0; $i < $postData['cantPiezas']; $i++) { 
						$articulo = ArticuloCompra::find($postData['idCompraInfo'.$i]);
						$articulo->idCompraInfo=$idCompra;
						$articulo->idPieza = $postData['idPieza'.$i];
						$articulo->idInventarioMaterial = $postData['idInventarioMaterial'.$i];
						$articulo->consumoPorPar = $postData['consumoPorPar'.$i];
						$articulo->observacion = $postData['observacion'.$i];
						$articulo->idUserRegister = $_SESSION['userId'];
						$articulo->idUserUpdate = $_SESSION['userId'];
						//$articulo->save();
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

		$compras = ComprasInfo::Join("clientesProvedores","comprasInfo.idProveedor","=","clientesProvedores.id")
		->select('comprasInfo.*', 'clientesProvedores.nombre')
		->get();

		return $this->renderHTML('listCompra.twig',[
				'compras' => $compras,
				'responseMessage' => $responseMessage
		]);
	}
}

?>
