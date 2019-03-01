<?php 

namespace App\Controllers;

use App\Models\Linea;
use Respect\Validation\Validator as v;
use Zend\Diactoros\Response\RedirectResponse;

class LineaController extends BaseController{
	
	//estos dos valores son los que se cambian, para modificar la cantidad de registros listados por pagina y el maximo numero en paginacion
	private $articulosPorPagina=15;
	private $limitePaginacion=20;

	public function getAddLineaAction($request){
		return $this->renderHTML('addLinea.twig');
	}

	//Registra la Persona
	public function postAddLineaAction($request){
		$responseMessage = null;
		
		if($request->getMethod()=='POST'){
			$postData = $request->getParsedBody();
			
			$lineaValidator = v::key('nombreLinea', v::stringType()->length(1, 40)->notEmpty());
			
			if($_SESSION['userId']){
				try{
					$lineaValidator->assert($postData);
					$postData = $request->getParsedBody();
					
					$line = new Linea();
					$line->nombreLinea = $postData['nombreLinea'];
					$line->idUserRegister = $_SESSION['userId'];
					$line->idUserUpdate = $_SESSION['userId'];
					$line->save();
					
					$responseMessage = 'Registrado';
				}catch(\Exception $e){
					$prevMessage = substr($e->getMessage(), 0, 15);
					if ($prevMessage =="These rules mus") {
						$responseMessage = 'Error, el nombre de la linea no puede tener mas de 40 digitos.';
					}
				}
			}
		}

		//Retorna a la pagina de registro con un mensaje $responseMessage
		return $this->renderHTML('addLinea.twig',[
				'responseMessage' => $responseMessage
		]);
	}

	//Lista todas la linea Ordenando por posicion
	public function getListLinea(){
		$responseMessage = null; $iniciar=0;
		

		$numeroDeFilas = Linea::selectRaw('count(*) as query_count')
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


		$line = Linea::orderBy('nombreLinea')
		->limit($this->articulosPorPagina)->offset($iniciar)
		->get();

		return $this->renderHTML('listLinea.twig', [
			'lines' => $line,
			'numeroDePaginas' => $numeroDePaginas,
			'paginaActual' => $paginaActual
		]);
	}

	/*Al seleccionar uno de los dos botones (Eliminar o Actualizar) llega a esta accion y verifica cual de los dos botones oprimio si eligio el boton eliminar(del) elimina el registro de where $id Pero
	Si elige actualizar(upd) cambia la ruta del renderHTML y guarda una consulta de los datos del registro a modificar para mostrarlos en formulario de actualizacion llamado updateActOperario.twig y cuando modifica los datos y le da guardar a ese formulaio regresa a esta class y elige la accion getUpdateActivity()*/
	public function postUpdDelLinea($request){
		$responseMessage = null;
		$quiereActualizar = false;
		$ruta='listLinea.twig';

		if($request->getMethod()=='POST'){
			$postData = $request->getParsedBody();

			$id = $postData['id'] ?? false;
			if ($id) {
				if($postData['boton']=='del'){
				  try{
					  $line = new Linea();
					  $line->destroy($id);
					  $responseMessage = "Se elimino la linea";	
				  }catch(\Exception $e){
				  	//$responseMessage = $e->getMessage();
				  	$prevMessage = substr($e->getMessage(), 0, 53);
					if ($prevMessage =="SQLSTATE[23000]: Integrity constraint violation: 1451") {
						$responseMessage = 'Error, No se puede eliminar, esta linea esta siendo usada.';
					}
				  }
				}elseif ($postData['boton']=='upd') {
					$quiereActualizar=true;
				}
			}else{
				$responseMessage = 'Debe Seleccionar una linea de calzado';
			}
		}
		
		if ($quiereActualizar){
			//si quiere actualizar hace una consulta where id=$id y la envia por el array del renderHtml
			$lines = Linea::find($id);
			$ruta='updateLinea.twig';
		}else{
			$iniciar=0;
			$lines = Linea::orderBy('nombreLinea')
			->limit($this->articulosPorPagina)->offset($iniciar)
			->get();
		}
		return $this->renderHTML($ruta, [
			'lines' => $lines,
			'idUpdate' => $id,
			'responseMessage' => $responseMessage
		]);
	}

	//en esta accion se registra las modificaciones del registro
	public function getUpdateLinea($request){

		$responseMessage = null;
				
		if($request->getMethod()=='POST'){
			$postData = $request->getParsedBody();

			$lineaValidator = v::key('nombreLinea', v::stringType()->length(1, 40)					->notEmpty());

			
			if($_SESSION['userId']){
				try{
					$lineaValidator->assert($postData);
					$postData = $request->getParsedBody();

					//la siguiente linea hace una consulta en la DB y trae el registro where id=$id y lo guarda en actOpe y posteriormente remplaza los valores y con el ->save() guarda la modificacion en la DB
					$line = Linea::find($postData['id']);
					$line->nombreLinea = $postData['nombreLinea'];
					$line->idUserUpdate = $_SESSION['userId'];
					$line->save();
					$responseMessage = 'Actualizado';
				}catch(\Exception $e){
					$prevMessage = substr($e->getMessage(), 0, 15);
					if ($prevMessage =="These rules mus") {
						$responseMessage = 'Error, el nombre de la linea no puede tener mas de 40 digitos.';
					}
				}
			}
		}

		$iniciar=0;
		$lines = Linea::orderBy('nombreLinea')
		->limit($this->articulosPorPagina)->offset($iniciar)
		->get();
		return $this->renderHTML('listLinea.twig',[
				'lines' => $lines,
				'responseMessage' => $responseMessage
		]);
	}
}

?>
