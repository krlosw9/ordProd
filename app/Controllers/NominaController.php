<?php 

namespace App\Controllers; 

use App\Models\{Nomina, Personas, TareaOperario};
use Respect\Validation\Validator as v;
use Zend\Diactoros\Response\RedirectResponse;

class NominaController extends BaseController{
	//Lista los operarios que estan activos Ordenando por nombre
	public function getListNomina(){
		$responseMessage = null;
		
		$people = Personas::where("activoCheck","=",1)->orderBy('nombre')->get();

		return $this->renderHTML('listNomina.twig', [
			'peoples' => $people
		]);
	}


	public function postListAddNominaAction($request){
		$postData = $request->getParsedBody();
		$cantTickets = $postData['cantTickets'];
		$dividePersona = explode("/", $postData['persona']);
		$idPersona = $dividePersona[0];
		$nombre = $dividePersona[1];
		$apellido = $dividePersona[2];

		return $this->renderHTML('listAddNomina.twig',[
				'cantTickets' => $cantTickets,
				'nombre' => $nombre,
				'idPersona' => $idPersona
		]);
	}


	public function postQueryNominaAction($request){
		$responseMessage = null;
		$postData = $request->getParsedBody();


		try{
			$cantTickets = $postData['cantTickets'];
			$idPersona = $postData['idPersona'];
			$nombrePersona = $postData['nombre'];
			$iterador=0; $totalTarea=0; $totalNomina = 0; $cantNoAprovados=0;
			$referenciasAprovadas='';
			$aprobados=array();
			

			$tareasNoPagas = TareaOperario::Join("infoOrdenProduccion","tareaOperario.idInfoOrdenProduccion","=","infoOrdenProduccion.id")
			->Join("actividadTarea","tareaOperario.idActTarea","=","actividadTarea.id")
			->select('tareaOperario.*', 'infoOrdenProduccion.referenciaOrd' , 'actividadTarea.nombre')
			->where("pagaCheck","=",0)
			//->where("idOperario","=",$idPersona)
			->orderBy('id')->get();
			
			//$codeFiltrado elimina los codigos repetidos
			$codeBruto = $postData['code'];
			$codeFiltrado = array_unique($codeBruto);
			
			foreach ($codeFiltrado as $code) {
				foreach ($tareasNoPagas as $tarea) {
					if ($code == $tarea->id) {
						$aprobados[$iterador]['idTarea'] = $tarea->id;
						$aprobados[$iterador]['actividad'] = $tarea->nombre;
						$aprobados[$iterador]['refOrden'] = $tarea->referenciaOrd;
						$aprobados[$iterador]['valorPar'] = $tarea->valorTarea;
						$aprobados[$iterador]['cantidadPares'] = $tarea->cantidadPares;
						$totalTarea = $tarea->valorTarea * $tarea->cantidadPares;
						$totalNomina += $totalTarea;
						$referenciasAprovadas .= $tarea->referenciaOrd.', ';
						$iterador++;
					}
				}
			}

			if ($cantTickets != $iterador ) {
				$cantNoAprovados = $cantTickets - $iterador;
			}

		}catch(\Exception $e){
			$prevMessage = substr($e->getMessage(), 0, 15);
			if ($prevMessage =="These rules mus") {
				$responseMessage = 'Error, por favor contacta al administrador del software, este error es inusual, 3172891700';
			}else{
				$responseMessage = substr($e->getMessage(), 0, 50);
			}
		}

		return $this->renderHTML('checkAddNomina.twig', [
			'totalNomina' => $totalNomina,
			'aprobados' => $aprobados,
			'nombrePersona' => $nombrePersona,
			'idPersona' => $idPersona,
			'referenciasAprovadas' => $referenciasAprovadas,
			'cantNoAprovados' => $cantNoAprovados,
			'cantAprovados' => $iterador,
			'responseMessage' => $responseMessage
		]);
	}


	public function postCheckAddNominaAction($request){
		$people = Personas::orderBy('nombre')->get();

		return $this->renderHTML('checkAddNomina.twig', [
			'peoples' => $people
		]);
	}


	public function getAddNominaAction($request){
		$people = Personas::orderBy('nombre')->get();

		return $this->renderHTML('listNomina.twig', [
			'peoples' => $people
		]);
	}

	//Registra el Cliente
	public function postAddNominaAction($request){
		$responseMessage = null;
		
		if($request->getMethod()=='POST'){
			$postData = $request->getParsedBody();
			
			if($_SESSION['userId']){

				try{
					$postData = $request->getParsedBody();
					$cantAprovados = $postData['cantAprovados'];
					
					if ($cantAprovados > 0) {

						$idTareaOperario = $postData['idTareaOperario'];
						foreach ($idTareaOperario as $idTarea ) {
							$tareaOperario = TareaOperario::find($idTarea);
							$tareaOperario->pagaCheck = 1;
							$tareaOperario->idUserUpdate = $_SESSION['userId'];
							$tareaOperario->save();
						}

						$nomina = new Nomina();
						$nomina->idPersona = $postData['idPersona'];
						$nomina->referencias = $postData['referencias'];
						$nomina->valor = $postData['totalNomina'];
						$nomina->observacion = $postData['observacion'];
						$nomina->idUserRegister = $_SESSION['userId'];
						$nomina->idUserUpdate = $_SESSION['userId'];
						$nomina->save();
						
						$responseMessage = 'Registrado';
					}else{
						$responseMessage = 'No tiene tickets aprovados por registrar';
					}
					
				}catch(\Exception $e){
					$prevMessage = substr($e->getMessage(), 0, 15);
					if ($prevMessage =="These rules mus") {
						$responseMessage = 'Error, por favor contacta al administrador del software, este error es inusual, 3172891700.';
					}else{
						$responseMessage = substr($e->getMessage(), 0, 50);
					}
				}
			}
		}

		$people = Personas::where("activoCheck","=",1)->orderBy('nombre')->get();
		return $this->renderHTML('listNomina.twig',[
				'responseMessage' => $responseMessage
		]);
	}

}

?>
