<?php 

namespace App\Controllers;

use App\Models\{InventarioMaterial};
use Respect\Validation\Validator as v;
use Zend\Diactoros\Response\RedirectResponse;

class InventarioMaterialController extends BaseController{
	
	//estos dos valores son los que se cambian, para modificar la cantidad de registros listados por pagina y el maximo numero en paginacion
	private $articulosPorPagina=15;
	private $limitePaginacion=20;

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
					$inventory->precio=$postData['precio'];
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
		$inventory=null; $iniciar=0;

		$numeroDeFilas = InventarioMaterial::selectRaw('count(*) as query_count')
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
		
		$inventory = InventarioMaterial::orderBy('nombre')
		->limit($this->articulosPorPagina)->offset($iniciar)
		->get();
		
		return $this->renderHTML('listInventarioMaterial.twig', [
			'inventorys' => $inventory,
			'numeroDePaginas' => $numeroDePaginas,
			'paginaActual' => $paginaActual
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
				  try{
					$inventory = new InventarioMaterial();
					$inventory->destroy($id);
					$responseMessage = "Se elimino el material";
				  }catch(\Exception $e){
				  	//$responseMessage = $e->getMessage();
				  	$prevMessage = substr($e->getMessage(), 0, 53);
					if ($prevMessage =="SQLSTATE[23000]: Integrity constraint violation: 1451") {
						$responseMessage = 'Error, No se puede eliminar, este material esta siendo usado.';
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
			$inventorys = InventarioMaterial::find($id);
			$ruta='updateInventarioMaterial.twig';
		}else{
			$iniciar=0;
			$inventorys = InventarioMaterial::orderBy('nombre')
			->limit($this->articulosPorPagina)->offset($iniciar)
			->get();
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
					$inventory->precio = $postData['precio'];
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

		$iniciar=0;
		$inventorys = InventarioMaterial::orderBy('nombre')
		->limit($this->articulosPorPagina)->offset($iniciar)
		->get();

		return $this->renderHTML('listInventarioMaterial.twig',[
				'inventorys' => $inventorys,
				'responseMessage' => $responseMessage
		]);
	}
}

?>
