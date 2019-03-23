<?php

namespace App\Controllers;

use App\Models\Talla;
use Respect\Validation\Validator as v;
use Zend\Diactoros\Response\RedirectResponse;

class TallaController extends BaseController{
	
	//estos dos valores son los que se cambian, para modificar la cantidad de registros listados por pagina y el maximo numero en paginacion
	private $articulosPorPagina=15;
	private $limitePaginacion=20;

	public function getAddTallaAction($request){
		return $this->renderHTML('addTalla.twig');
	}

	//Registra la Talla
	public function postAddTallaAction($request){
		$responseMessage = null;
			
		if($request->getMethod()=='POST'){
			$postData = $request->getParsedBody();

			$tallaValidator = v::key('nombreTalla', v::stringType()->length(1, 3)->noWhitespace()->notEmpty());
			if($_SESSION['userId']){
				try{
					$tallaValidator->assert($postData);
					$postData = $request->getParsedBody();
					$size = new Talla();
					$size->nombreTalla = $postData['nombreTalla'];
					$size->idUserRegister = $_SESSION['userId'];
					$size->idUserUpdate = $_SESSION['userId'];
					$size->save();
					
					$responseMessage = 'Registrado';
				}catch(\Exception $e){
					$prevMessage = substr($e->getMessage(), 0, 15);
					if ($prevMessage =="All of the requ") {
						$responseMessage = 'Error, la talla debe ser superior a 0 he inferior a 50.';
					}elseif ($prevMessage =="SQLSTATE[23000]") {
						$responseMessage = 'Error, la talla que coloco ya esta registrada (No puede haber dos tallas iguales).';
					}else{
						//$responseMessage = $e->getMessage();
						$responseMessage = substr($e->getMessage(), 0, 45);
					}
				}
			}
		}
		

		//Retorna a la pagina de registro con un mensaje $responseMessage
		return $this->renderHTML('addTalla.twig',[
				'responseMessage' => $responseMessage
			]);
	}

	//Lista todas la Talla Ordenando por nombre
	public function getListTalla(){
		$iniciar=0;

		$numeroDeFilas = Talla::selectRaw('count(*) as query_count')
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

		$size = Talla::orderBy('nombreTalla')
		->limit($this->articulosPorPagina)->offset($iniciar)
		->get();

		return $this->renderHTML('listTalla.twig', [
			'size' => $size,
			'numeroDePaginas' => $numeroDePaginas,
			'paginaActual' => $paginaActual
		]);
	}

	/*Al seleccionar uno de los dos botones (Eliminar o Actualizar) llega a esta accion y verifica cual de los dos botones oprimio si eligio el boton eliminar(del) elimina el registro de where $id Pero
	Si elige actualizar(upd) cambia la ruta del renderHTML y guarda una consulta de los datos del registro a modificar para mostrarlos en formulario de actualizacion llamado updateActOperario.twig y cuando modifica los datos y le da guardar a ese formulaio regresa a esta class y elige la accion getUpdateActivity()*/
	public function postUpdDelTalla($request){
		$responseMessage = null;
		$quiereActualizar = false;
		$ruta='listTalla.twig';

		if($request->getMethod()=='POST'){
			$postData = $request->getParsedBody();
			
			$id = $postData['id'] ?? false;
			if ($id) {
				if($postData['boton']=='del'){
				  try{
					$size = new Talla();
					$size->destroy($id);
					$responseMessage = "Se elimino la talla";
				  }catch(\Exception $e){
				  	//$responseMessage = $e->getMessage();
				  	$prevMessage = substr($e->getMessage(), 0, 53);
					if ($prevMessage =="SQLSTATE[23000]: Integrity constraint violation: 1451") {
						$responseMessage = 'Error, No se puede eliminar, esta talla esta siendo usada.';
					}else{
						//$responseMessage = $e->getMessage();
						$responseMessage = substr($e->getMessage(), 0, 25);
					}
				  }

				}elseif ($postData['boton']=='upd') {
					$quiereActualizar=true;
				}
			}else{
				$responseMessage = 'Debe Seleccionar una talla';
			}
		}
		
		if ($quiereActualizar){
			//si quiere actualizar hace una consulta where id=$id y la envia por el array del renderHtml
			$size = Talla::find($id);
			$ruta='updateTalla.twig';
		}else{
			$iniciar=0;
			$size = Talla::orderBy('nombreTalla')
			->limit($this->articulosPorPagina)->offset($iniciar)
			->get();
		}
		return $this->renderHTML($ruta, [
			'size' => $size,
			'idUpdate' => $id,
			'responseMessage' => $responseMessage
		]);
	}

	//en esta accion se registra las modificaciones del registro
	public function getUpdateTalla($request){

		$responseMessage = null;
				
		if($request->getMethod()=='POST'){
			$postData = $request->getParsedBody();

			$tallaValidator = v::key('nombreTalla', v::stringType()->notEmpty());

			
			if($_SESSION['userId']){
				try{
					$tallaValidator->assert($postData);
					$postData = $request->getParsedBody();
					
					//la siguiente linea hace una consulta en la DB y trae el registro where id=$id y lo guarda en actOpe y posteriormente remplaza los valores y con el ->save() guarda la modificacion en la DB
					$size = Talla::find($postData['id']);
					$size->nombreTalla = $postData['nombreTalla'];
					$size->idUserUpdate = $_SESSION['userId'];
					$size->save();
					
					$responseMessage = 'Actualizado';
				}catch(\Exception $e){
					$prevMessage = substr($e->getMessage(), 0, 15);
					if ($prevMessage =="All of the requ") {
						$responseMessage = 'Error, la talla debe ser superior a 0 he inferior a 50.';
					}elseif ($prevMessage =="SQLSTATE[23000]") {
						$responseMessage = 'Error, la talla que coloco ya esta registrada (No puede haber dos tallas iguales).';
					}else{
						//$responseMessage = $e->getMessage();
						$responseMessage = substr($e->getMessage(), 0, 45);
					}
				}
			}
		
		}
		$iniciar=0;
		$size = Talla::orderBy('nombreTalla')
		->limit($this->articulosPorPagina)->offset($iniciar)
		->get();
		return $this->renderHTML('listTalla.twig',[
				'size' => $size,
				'responseMessage' => $responseMessage
			]);
	}
}

?>
