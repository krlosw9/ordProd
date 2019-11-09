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

						/* Consulta el ultimo id de Nomina */
						$queryNomina = Nomina::all();
						$nominaUltimo = $queryNomina->last();
						$nominaUltimoId = $nominaUltimo->id+1;

						$idTareaOperario = $postData['idTareaOperario'];
						foreach ($idTareaOperario as $idTarea ) {
							$tareaOperario = TareaOperario::find($idTarea);
							$tareaOperario->pagaCheck = 1;
							$tareaOperario->idNomina = $nominaUltimoId;
							$tareaOperario->idUserUpdate = $_SESSION['userId'];
							$tareaOperario->save();
						}

						$nomina = new Nomina();
						$nomina->id = $nominaUltimoId;
						$nomina->idPersona = $postData['idPersona'];
						$nomina->referencias = $postData['referencias'];
						$nomina->valor = $postData['totalNomina'];
						$nomina->observacion = $postData['observacion'];
						$nomina->liquidada = 0;
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



//Lista todas las tareas que aun no se han pagado y que estan asignadas a ese operario en especifico
	public function postListNominaPorOperarioYaAsignado($request){
		$responseMessage = null; 
		if($request->getMethod()=='POST'){
			
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
			
		return $this->renderHTML('NominaPorOperarioAsignado.twig',[
				'responseMessage' => $responseMessage,
				'tareasNoPagas' => $tareasNoPagas,
				'people' => $people
		]);
	}

	public function postPagarNomina($request){
		$responseMessage = null; $totalNomina=0; $cantidadTicketsAprobados=0; $algunTicketAprobado=false;
		$arrayTareasPorPagar=null; $people=null; $tareasNomina=null;

		if($request->getMethod()=='POST'){
			$postData = $request->getParsedBody();
			
			if($_SESSION['userId']){

				try{
					$postData = $request->getParsedBody();
					
					/* Consulta el ultimo id de Nomina */
					$queryNomina = Nomina::all();
					$nominaUltimo = $queryNomina->last();
					$nominaUltimoId = $nominaUltimo->id+1;


					$arrayTareasPorPagar = $postData['idTareas'] ?? null;

					if($arrayTareasPorPagar){
						foreach ($arrayTareasPorPagar as $tarea) {
							$postTotalTarea = $postData['totalTarea'.$tarea] ?? null;
							$postCheck = $postData['check'.$tarea] ?? null;
							if ($postCheck == 'on') {
								/*Edita cada tarea seleccionada para el pago y la coloca como tarea Paga (pagaCheck=1)
								y Le coloca el idNomina*/
								$tareaOperario = TareaOperario::find($tarea);
								$tareaOperario->pagaCheck = 1;
								$tareaOperario->idNomina = $nominaUltimoId;
								$tareaOperario->idUserUpdate = $_SESSION['userId'];
								$tareaOperario->save();

								$algunTicketAprobado=true;

								$totalNomina += $postTotalTarea;
								$cantidadTicketsAprobados++;
							}
							$idPersona = $postData['idPersona'] ?? null;
							$people = Personas::find($idPersona);
						}
					}else{
						$idPersona = $postData['idPersona'] ?? null;
						if($idPersona){
							$people = Personas::find($idPersona);
							$responseMessage = "$people->nombre No tiene tareas asignadas";
						}else{
							$responseMessage = "Este operario No tiene tareas asignadas";
						}
					}

					/*Crea la nomina y le coloca el valor total ademas 
					Marca el valor liquidada en 0 para saber que aun no se a liquidado*/ 
					if($algunTicketAprobado){
						$nomina = new Nomina();
						$nomina->id=$nominaUltimoId;
						$nomina->idPersona=$idPersona;
						$nomina->valor=$totalNomina;
						$nomina->liquidada=0;
						$nomina->iduserregister = $_SESSION['userId'];
						$nomina->iduserupdate = $_SESSION['userId'];
						$nomina->save();

						$tareasNomina = TareaOperario::Join("infoOrdenProduccion","tareaOperario.idInfoOrdenProduccion","=","infoOrdenProduccion.id")
						->Join("actividadTarea","tareaOperario.idActTarea","=","actividadTarea.id")
						->select('tareaOperario.*', 'infoOrdenProduccion.referenciaOrd' , 'actividadTarea.nombre')
						->where("idNomina","=",$nominaUltimoId)
						->orderBy('id')->get();
					}

				}catch(\Exception $e){
					$responseMessage = substr($e->getMessage(), 0, 35);
				}
			}
		}

		
		
		return $this->renderHTML('NominaResumenPago.twig',[
			'responseMessage' => $responseMessage,
			'tareasNomina' => $tareasNomina,
			'people' => $people,
			'totalNomina' => $totalNomina,
			'cantidadTicketsAprobados' => $cantidadTicketsAprobados
	]);
	}


}

?>
