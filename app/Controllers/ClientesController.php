<?php 

namespace App\Controllers; 

use App\Models\Clientes;
use Respect\Validation\Validator as v;
use Zend\Diactoros\Response\RedirectResponse;

class ClientesController extends BaseController{

	//estos dos valores son los que se cambian, para modificar la cantidad de registros listados por pagina y el maximo numero en paginacion
	private $articulosPorPagina=15;
	private $limitePaginacion=20;


	public function getAddClientesAction($request){
		return $this->renderHTML('addClientes.twig');
	}

	//Registra el Cliente
	public function postAddClientesAction($request){
		$responseMessage = null;
		
		if($request->getMethod()=='POST'){
			$postData = $request->getParsedBody();
			
			$clientesValidator = v::key('nombre', v::stringType()->length(1, 30)->notEmpty())
					->key('telefono', v::numeric()->positive()->length(1, 12)->notEmpty());
			
			if($_SESSION['userId']){

				try{

					$clientesValidator->assert($postData);
					$postData = $request->getParsedBody();
					
					$customer = new Clientes();
					
					$customer->nombre = $postData['nombre'];
					$customer->apellido = $postData['apellido'];
					$customer->tipo = '0';
					$customer->telefono = $postData['telefono'];
					$customer->direccion = $postData['direccion'];
					$customer->observacion = $postData['observacion'];
					$customer->idUserRegister = $_SESSION['userId'];
					$customer->idUserUpdate = $_SESSION['userId'];
					$customer->save();
					
					$responseMessage = 'Registrado';
				}catch(\Exception $e){
					$prevMessage = substr($e->getMessage(), 0, 15);
					if ($prevMessage =="These rules mus") {
						$responseMessage = 'Error, El telefono debe tener de 1 a 12 digitos y el Nombre 30 digitos maximo.';
					}
				}
			}
		}

		//Retorna a la pagina de registro con un mensaje $responseMessage
		return $this->renderHTML('addClientes.twig',[
				'responseMessage' => $responseMessage
		]);
	}

	//Lista todas la Clientes Ordenando por posicion
	public function getListClientes(){
		$responseMessage = null; $iniciar=0;
		
		$numeroDeFilas = Clientes::where("tipo","=",0)
		->selectRaw('count(*) as query_count')
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
		
		$customerQuery = Clientes::where("tipo","=",0)
		->orderBy('nombre')
		->limit($this->articulosPorPagina)->offset($iniciar)
		->get();

		return $this->renderHTML('listClientes.twig', [
			'customers' => $customerQuery,
			'numeroDePaginas' => $numeroDePaginas,
			'paginaActual' => $paginaActual
		]);
	}




	/*Al seleccionar uno de los dos botones (Eliminar o Actualizar) llega a esta accion y verifica cual de los dos botones oprimio si eligio el boton eliminar(del) elimina el registro de where $id Pero
	Si elige actualizar(upd) cambia la ruta del renderHTML y guarda una consulta de los datos del registro a modificar para mostrarlos en formulario de actualizacion llamado updateActOperario.twig y cuando modifica los datos y le da guardar a ese formulaio regresa a esta class y elige la accion getUpdateActivity()*/
	public function postUpdDelClientes($request){
		$responseMessage = null;
		$quiereActualizar = false;
		$ruta='listClientes.twig';

		if($request->getMethod()=='POST'){
			$postData = $request->getParsedBody();
			
			$id = $postData['id'] ?? false;
			if ($id) {
				if($postData['boton']=='del'){
				  try{
					$customer = new Clientes();
					$customer->destroy($id);
					$responseMessage = "Se elimino el registro del operario";
				  }catch(\Exception $e){
				  	//$responseMessage = $e->getMessage();
				  	$prevMessage = substr($e->getMessage(), 0, 53);
					if ($prevMessage =="SQLSTATE[23000]: Integrity constraint violation: 1451") {
						$responseMessage = 'Error, No se puede eliminar, este cliente esta siendo usado.';
					}
				  }
				}elseif ($postData['boton']=='upd') {
					$quiereActualizar=true;
				}
			}else{
				$responseMessage = 'Debe Seleccionar un cliente';
			}
		}
		
		if ($quiereActualizar){
			//si quiere actualizar hace una consulta where id=$id y la envia por el array del renderHtml
			$customers = Clientes::find($id);
			$ruta='updateClientes.twig';
		}else{
			$iniciar=0;
			$customers = Clientes::where("tipo","=",0)
			->orderBy('nombre')
			->limit($this->articulosPorPagina)->offset($iniciar)
			->get();

		}
		return $this->renderHTML($ruta, [
			'customers' => $customers,
			'idUpdate' => $id,
			'responseMessage' => $responseMessage
		]);
	}

	//en esta accion se registra las modificaciones del registro
	public function getUpdateClientes($request){

		$responseMessage = null;
				
		if($request->getMethod()=='POST'){
			$postData = $request->getParsedBody();

			$clientesValidator = v::key('nombre', v::stringType()->length(1, 30)->notEmpty())
					->key('telefono', v::numeric()->positive()->length(1, 12)->notEmpty());

			
			if($_SESSION['userId']){
				try{
					$clientesValidator->assert($postData);
					$postData = $request->getParsedBody();

					//la siguiente linea hace una consulta en la DB y trae el registro where id=$id y lo guarda en actOpe y posteriormente remplaza los valores y con el ->save() guarda la modificacion en la DB
					$customer = Clientes::find($postData['id']);
					$customer->nombre = $postData['nombre'];
					$customer->apellido = $postData['apellido'];
					$customer->telefono = $postData['telefono'];
					$customer->direccion = $postData['direccion'];
					$customer->observacion = $postData['observacion'];
					$customer->idUserUpdate = $_SESSION['userId'];
					$customer->save();
					$responseMessage = 'Actualizado';
				}catch(\Exception $e){
					$prevMessage = substr($e->getMessage(), 0, 15);
					if ($prevMessage =="These rules mus") {
						$responseMessage = 'Error, El telefono debe tener de 1 a 12 digitos y el Nombre 30 digitos maximo.';
					}
				}
			}
		}
		
		$iniciar=0;
		$customers = Clientes::where("tipo","=",0)
		->orderBy('nombre')
		->limit($this->articulosPorPagina)->offset($iniciar)
		->get();
		
		return $this->renderHTML('listClientes.twig',[
				'customers' => $customers,
				'responseMessage' => $responseMessage
		]);
	}
}

?>
