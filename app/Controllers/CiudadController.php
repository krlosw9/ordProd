<?php 

namespace App\Controllers;

use App\Models\Ciudad;
use Respect\Validation\Validator as v;
use Zend\Diactoros\Response\RedirectResponse;

class CiudadController extends BaseController{
	
	//estos dos valores son los que se cambian, para modificar la cantidad de registros listados por pagina y el maximo numero en paginacion
	private $articulosPorPagina=15;
	private $limitePaginacion=20;

	public function getAddCiudadAction($request){
		return $this->renderHTML('addCiudad.twig');
	}

	//Registra la Persona
	public function postAddCiudadAction($request){
		$responseMessage = null;
		
		if($request->getMethod()=='POST'){
			$postData = $request->getParsedBody();
			
			$ciudadValidator = v::key('nombre', v::stringType()->length(1, 30)->notEmpty());
			
			if($_SESSION['userId']){
				try{
					$ciudadValidator->assert($postData);
					$postData = $request->getParsedBody();
					
					$city = new Ciudad();
					$city->nombre = $postData['nombre'];
					$city->idUserRegister = $_SESSION['userId'];
					$city->idUserUpdate = $_SESSION['userId'];
					$city->save();
					
					$responseMessage = 'Registrado';
				}catch(\Exception $e){
					$prevMessage = substr($e->getMessage(), 0, 15);
					if ($prevMessage =="These rules mus") {
						$responseMessage = 'Error, el nombre de la ciudad no puede tener mas de 30 digitos.';
					}
				}
			}
		}

		//Retorna a la pagina de registro con un mensaje $responseMessage
		return $this->renderHTML('addCiudad.twig',[
				'responseMessage' => $responseMessage
		]);
	}

	//Lista todas la Ciudad Ordenando por posicion
	public function getListCiudad(){
		$responseMessage = null; $iniciar=0;

		$numeroDeFilas = Ciudad::selectRaw('count(*) as query_count')
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

		
		$city = Ciudad::orderBy('nombre')
		->limit($this->articulosPorPagina)->offset($iniciar)
		->get();

		return $this->renderHTML('listCiudad.twig', [
			'cities' => $city,
			'numeroDePaginas' => $numeroDePaginas,
			'paginaActual' => $paginaActual
		]);
	}

	/*Al seleccionar uno de los dos botones (Eliminar o Actualizar) llega a esta accion y verifica cual de los dos botones oprimio si eligio el boton eliminar(del) elimina el registro de where $id Pero
	Si elige actualizar(upd) cambia la ruta del renderHTML y guarda una consulta de los datos del registro a modificar para mostrarlos en formulario de actualizacion llamado updateActOperario.twig y cuando modifica los datos y le da guardar a ese formulaio regresa a esta class y elige la accion getUpdateActivity()*/
	public function postUpdDelCiudad($request){
		$responseMessage = null;
		$quiereActualizar = false;
		$ruta='listCiudad.twig';

		if($request->getMethod()=='POST'){
			$postData = $request->getParsedBody();

			$id = $postData['id'] ?? false;
			if ($id) {
				if($postData['boton']=='del'){
				  try{
					  $city = new Ciudad();
					  $city->destroy($id);
					  $responseMessage = "Se elimino la ciudad";	
				  }catch(\Exception $e){
				  	//$responseMessage = $e->getMessage();
				  	$prevMessage = substr($e->getMessage(), 0, 53);
					if ($prevMessage =="SQLSTATE[23000]: Integrity constraint violation: 1451") {
						$responseMessage = 'Error, No se puede eliminar, esta ciudad esta siendo usada.';
					}
				  }
				}elseif ($postData['boton']=='upd') {
					$quiereActualizar=true;
				}
			}else{
				$responseMessage = 'Debe Seleccionar una ciudad';
			}
		}
		
		if ($quiereActualizar){
			//si quiere actualizar hace una consulta where id=$id y la envia por el array del renderHtml
			$cities = Ciudad::find($id);
			$ruta='updateCiudad.twig';
		}else{
			$iniciar=0;
			$cities = Ciudad::orderBy('nombre')
			->limit($this->articulosPorPagina)->offset($iniciar)
			->get();
		}
		return $this->renderHTML($ruta, [
			'cities' => $cities,
			'idUpdate' => $id,
			'responseMessage' => $responseMessage
		]);
	}

	//en esta accion se registra las modificaciones del registro
	public function getUpdateCiudad($request){

		$responseMessage = null;
				
		if($request->getMethod()=='POST'){
			$postData = $request->getParsedBody();

			$ciudadValidator = v::key('nombre', v::stringType()->length(1, 30)					->notEmpty());

			
			if($_SESSION['userId']){
				try{
					$ciudadValidator->assert($postData);
					$postData = $request->getParsedBody();

					//la siguiente linea hace una consulta en la DB y trae el registro where id=$id y lo guarda en actOpe y posteriormente remplaza los valores y con el ->save() guarda la modificacion en la DB
					$city = Ciudad::find($postData['id']);
					$city->nombre = $postData['nombre'];
					$city->idUserUpdate = $_SESSION['userId'];
					$city->save();
					$responseMessage = 'Actualizado';
				}catch(\Exception $e){
					$prevMessage = substr($e->getMessage(), 0, 15);
					if ($prevMessage =="These rules mus") {
						$responseMessage = 'Error, el nombre de la ciudad no puede tener mas de 30 digitos.';
					}
				}
			}
		}
		$iniciar=0;
		$cities = Ciudad::orderBy('nombre')
		->limit($this->articulosPorPagina)->offset($iniciar)
		->get();
		return $this->renderHTML('listCiudad.twig',[
				'cities' => $cities,
				'responseMessage' => $responseMessage
		]);
	}
}

?>
