<?php 

namespace App\Controllers;

use App\Models\{Personas, TareaOperario, Pedido, ActividadTarea, InfoOrdenProduccion};
use Respect\Validation\Validator as v;
use Zend\Diactoros\Response\RedirectResponse;

class ReportesController extends BaseController{
	
	//estos dos valores son los que se cambian, para modificar la cantidad de registros listados y el maximo numero en paginacion
	private $articulosPorPagina=20;
	private $limitePaginacion=20;

	//Lista los operarios activos para mostrar los tickets que este operario tiene
	public function getListNominaIndividual(){
		$responseMessage = null;
		
		$peoples = Personas::where("activoCheck","=",1)->orderBy('nombre')->get();

		return $this->renderHTML('listReportNominaIndividual.twig', [
			'peoples' => $peoples
		]);
		

		//return $this->renderHTML('listHormas.twig');
	}


	//Consulta de nomina individual
	public function postQueryNominaIndividualAction($request){
		$responseMessage = null;
		if($request->getMethod()=='POST'){
			$postData = $request->getParsedBody();
			if($_SESSION['userId']){
				try{
					$postData = $request->getParsedBody();

					$idPersona = $postData['idPersona'];
					 
					$tareasNoPagas = TareaOperario::Join("infoOrdenProduccion","tareaOperario.idInfoOrdenProduccion","=","infoOrdenProduccion.id")
					->Join("actividadTarea","tareaOperario.idActTarea","=","actividadTarea.id")
					->select('tareaOperario.*', 'infoOrdenProduccion.referenciaOrd' , 'actividadTarea.nombre')
					->where("pagaCheck","=",0)
					->where("idOperario","=",$idPersona)
					->latest('id')->get();

					$people = Personas::find($idPersona);

				}catch(\Exception $e){
					$responseMessage = substr($e->getMessage(), 0, 35);
					//$responseMessage = $e->getMessage();
				}
			}
		}
			
		return $this->renderHTML('reportNominaIndividual.twig',[
				'responseMessage' => $responseMessage,
				'tareasNoPagas' => $tareasNoPagas,
				'people' => $people
		]);
	}

	
	//Consulta de nomina Total
	public function getListNominaTotal(){
		$responseMessage = null;
		if($_SESSION['userId']){
			try{
				
				 
				$tareas = TareaOperario::Join("infoOrdenProduccion","tareaOperario.idInfoOrdenProduccion","=","infoOrdenProduccion.id")
					->Join("actividadTarea","tareaOperario.idActTarea","=","actividadTarea.id")
					->select('tareaOperario.*', 'infoOrdenProduccion.referenciaOrd' , 'actividadTarea.nombre')
					->where("pagaCheck","=",0)
					->latest('id')->get();

			}catch(\Exception $e){
				//$responseMessage = substr($e->getMessage(), 0, 35);
				$responseMessage = $e->getMessage();
			}
		}
			
		return $this->renderHTML('reportNominaTotal.twig',[
				'responseMessage' => $responseMessage,
				'tareas' => $tareas
		]);
	}

	public function getListPedidoEstado(){
		$responseMessage = null; $iniciar=0;
		
		$numeroDeFilas = Pedido::selectRaw('count(*) as query_count')
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

		$pedido = Pedido::Join("clientesProvedores","pedido.idCliente","=","clientesProvedores.id")
		->select('pedido.*', 'clientesProvedores.nombre')
		->latest('id')
		->limit($this->articulosPorPagina)->offset($iniciar)
		->get();

		return $this->renderHTML('listReportPedidoEstado.twig', [
			'pedidos'=> $pedido,
			'numeroDePaginas' => $numeroDePaginas,
			'paginaActual' => $paginaActual
		]);
	}


	
	//Consulta de nomina individual
	public function postQueryPedidoEstado($request){
		$responseMessage = null; $ruta='reportPedidoEstado.twig'; $pedido=null;
		$reporte = array(); $i=0; $actividades =null;
		if($request->getMethod()=='POST'){
			$postData = $request->getParsedBody();
			if($_SESSION['userId']){
				try{
					$postData = $request->getParsedBody();

					$idPedido = $postData['id'] ?? null;

					if ($idPedido) {

						$actividades = ActividadTarea::latest('posicion')->get();

						$orders = InfoOrdenProduccion::Join("pedido","infoOrdenProduccion.idPedido","=","pedido.id")
						->select('infoOrdenProduccion.*', 'pedido.referencia')
						->where("idPedido","=",$idPedido)
						->orderBy('id')
						->get();
						
						
						foreach ($orders as $orden) {
							$tareas = null;

							$tareas = TareaOperario::Join("actividadTarea","tareaOperario.idActTarea","=","actividadTarea.id")
							->select('tareaOperario.*', 'actividadTarea.nombre', 'actividadTarea.posicion')
							->where("pagaCheck","=",0)
							->whereNotNull('idOperario')
							->where("idInfoOrdenProduccion","=",$orden->id)
							->latest('posicion')
							->limit(1)->offset(0)
							->get();

								

							foreach ($tareas as $tarea) {
								$reporte[$tarea->idActTarea]['cantidadPares'] = $reporte[$tarea->idActTarea]['cantidadPares'] ?? 0;
								$reporte[$tarea->idActTarea]['cantidadPares']+=$tarea->cantidadPares;
								$reporte[$tarea->idActTarea]['actividad']=$tarea->nombre;
							}

							

							$pedido=$orden->referencia;
						}

					}else{
						$iniciar=0;
						$pedido = Pedido::Join("clientesProvedores","pedido.idCliente","=","clientesProvedores.id")
						->select('pedido.*', 'clientesProvedores.nombre')
						->latest('id')
						->limit($this->articulosPorPagina)->offset($iniciar)
						->get();
						$responseMessage = 'Debe seleccionar algun pedido';
						$ruta='listReportPedidoEstado.twig';
					}
					
				}catch(\Exception $e){
					$responseMessage = substr($e->getMessage(), 0, 35);
					//$responseMessage = $e->getMessage();
				}
			}
		}
			
		return $this->renderHTML($ruta,[
				'responseMessage' => $responseMessage,
				'pedidos' => $pedido,
				'actividades' => $actividades,
				'reporte' => $reporte
		]);
	}


	
	
}

?>
