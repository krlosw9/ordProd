<?php

namespace App\Controllers;

use App\Models\ActividadTarea;
use Respect\Validation\Validator as v;
use Zend\Diactoros\Response\RedirectResponse;

class ActividadTareaController extends BaseController{
	
	//estos dos valores son los que se cambian, para modificar la cantidad de registros listados por pagina y el maximo numero en paginacion
	private $articulosPorPagina=15;
	private $limitePaginacion=20;

	public function getAddActividadTareaAction($request){
		return $this->renderHTML('addActividadTarea.twig');
	}

	//Registra la Actividad-Tarea
	public function postAddActividadTareaAction($request){
		$responseMessage = null;
			
		if($request->getMethod()=='POST'){
			$postData = $request->getParsedBody();

			$actividadTareaValidator = v::key('nombre', v::stringType()->length(1, 30)->notEmpty())
					->key('valorPorPar', v::numeric()->positive()->between(0, 100000))
					->key('posicion', v::numeric()->positive()->between(1, 20));
			if($_SESSION['userId']){
				try{
					$actividadTareaValidator->assert($postData);
					$postData = $request->getParsedBody();
					
					$actOpe = new ActividadTarea();
					$actOpe->nombre = $postData['nombre'];
					$actOpe->valorPorPar = $postData['valorPorPar'];
					$actOpe->posicion = $postData['posicion'];
					$actOpe->observacion = $postData['observacion'];
					$actOpe->idUserRegister = $_SESSION['userId'];
					$actOpe->idUserUpdate = $_SESSION['userId'];
					$actOpe->save();
					
					$responseMessage = 'Registrado';
				}catch(\Exception $e){
					$prevMessage = substr($e->getMessage(), 0, 15);
					if ($prevMessage =="These rules mus") {

						$responseMessage = 'Error, el valor por par debe ser superior a $1 y la posicion debe ser entre 1 y 20.';
					}elseif ($prevMessage =="SQLSTATE[23000]") {
						$responseMessage = 'Error, la posicion que coloco ya esta registrada (No puede haber dos posiciones iguales).';
					}
				}
			}
		}
		

		//Retorna a la pagina de registro con un mensaje $responseMessage
		return $this->renderHTML('addActividadTarea.twig',[
				'responseMessage' => $responseMessage
			]);
	}

	//Lista todas la Actividad-Tarea Ordenando por posicion
	public function getListActividadTarea(){
		$iniciar=0;

		$numeroDeFilas = ActividadTarea::selectRaw('count(*) as query_count')
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

		$actOpes = ActividadTarea::orderBy('posicion')
		->limit($this->articulosPorPagina)->offset($iniciar)
		->get();

		return $this->renderHTML('listActividadTarea.twig', [
			'actOpes' => $actOpes,
			'numeroDePaginas' => $numeroDePaginas,
			'paginaActual' => $paginaActual
		]);
	}

	/*Al seleccionar uno de los dos botones (Eliminar o Actualizar) llega a esta accion y verifica cual de los dos botones oprimio si eligio el boton eliminar(del) elimina el registro de where $id Pero
	Si elige actualizar(upd) cambia la ruta del renderHTML y guarda una consulta de los datos del registro a modificar para mostrarlos en formulario de actualizacion llamado updateActOperario.twig y cuando modifica los datos y le da guardar a ese formulaio regresa a esta class y elige la accion getUpdateActivity()*/
	public function postUpdDelActividadTarea($request){
		$responseMessage = null;
		$quiereActualizar = false;
		$ruta='listActividadTarea.twig';

		if($request->getMethod()=='POST'){
			$postData = $request->getParsedBody();
			
			$id = $postData['id'] ?? false;
			if ($id) {
				if($postData['boton']=='del'){
				  try{
					$actOpe = new ActividadTarea();
					$actOpe->destroy($id);
					$responseMessage = "Se elimino la actividad";
				  }catch(\Exception $e){
				  	//$responseMessage = $e->getMessage();
				  	$prevMessage = substr($e->getMessage(), 0, 53);
					if ($prevMessage =="SQLSTATE[23000]: Integrity constraint violation: 1451") {
						$responseMessage = 'Error, No se puede eliminar, esta actividad esta siendo usada.';
					}
				  }

				}elseif ($postData['boton']=='upd') {
					$quiereActualizar=true;
				}
			}else{
				$responseMessage = 'Debe Seleccionar una actividad';
			}
		}
		
		if ($quiereActualizar){
			//si quiere actualizar hace una consulta where id=$id y la envia por el array del renderHtml
			$actOpes = ActividadTarea::find($id);
			$ruta='updateActividadTarea.twig';
		}else{
			$iniciar=0;
			$actOpes = ActividadTarea::orderBy('posicion')
			->limit($this->articulosPorPagina)->offset($iniciar)
			->get();
		}
		return $this->renderHTML($ruta, [
			'actOpes' => $actOpes,
			'idUpdate' => $id,
			'responseMessage' => $responseMessage
		]);
	}

	//en esta accion se registra las modificaciones del registro
	public function getUpdateActividadTarea($request){

		$responseMessage = null;
				
		if($request->getMethod()=='POST'){
			$postData = $request->getParsedBody();

			$actividadTareaValidator = v::key('nombre', v::stringType()->notEmpty())
					->key('valorPorPar', v::numeric()->positive()->between(0, 100000))
					->key('posicion', v::numeric()->positive()->between(1, 20));

			
			if($_SESSION['userId']){
				try{
					$actividadTareaValidator->assert($postData);
					$postData = $request->getParsedBody();
					
					//la siguiente linea hace una consulta en la DB y trae el registro where id=$id y lo guarda en actOpe y posteriormente remplaza los valores y con el ->save() guarda la modificacion en la DB
					$actOpe = ActividadTarea::find($postData['id']);
					$actOpe->nombre = $postData['nombre'];
					$actOpe->valorPorPar = $postData['valorPorPar'];
					$actOpe->posicion = $postData['posicion'];
					$actOpe->observacion = $postData['observacion'];
					$actOpe->idUserUpdate = $_SESSION['userId'];
					$actOpe->save();
					
					$responseMessage = 'Actualizado';
				}catch(\Exception $e){
					$prevMessage = substr($e->getMessage(), 0, 15);
					if ($prevMessage =="These rules mus") {

						$responseMessage = 'Error, el valor por par debe ser superior a $1 y la posicion debe ser entre 1 y 20.';
					}elseif ($prevMessage =="SQLSTATE[23000]") {
						$responseMessage = 'Error, la posicion que coloco ya esta registrada (No puede haber dos posiciones iguales).';
					}
				}
			}
		
		}
		$iniciar=0;
		$actOpes = ActividadTarea::orderBy('posicion')
		->limit($this->articulosPorPagina)->offset($iniciar)
		->get();
		return $this->renderHTML('listActividadTarea.twig',[
				'actOpes' => $actOpes,
				'responseMessage' => $responseMessage
			]);
	}
}

?>
