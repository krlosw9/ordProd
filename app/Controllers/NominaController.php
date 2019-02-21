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
		$totalNomina = 0; $cantAprovados =1;
		$referenciasAprovadas = '';
		$postData = $request->getParsedBody();
		$cantTickets = $postData['cantTickets'];
		$idPersona = $postData['idPersona'];
		$nombrePersona = $postData['nombre'];
		$yaPagos=array(); $itera=0;
		$idAprovados=array();
		$sumado=[
			0 => [
				'id' => 0
			]
		];

		$tareas = TareaOperario::Join("infoOrdenProduccion","tareaOperario.idInfoOrdenProduccion","=","infoOrdenProduccion.id")
			->select('tareaOperario.*', 'infoOrdenProduccion.referenciaOrd')
			->latest('id')->get();
		for ($i=1; $i <= $cantTickets; $i++) { 
			foreach ($tareas as $key => $value) {
				if ($value->idOperario == $idPersona) {
					if ($value->pagaCheck == 1) {
						//Ingresan solo los que ya estan pagos
						if ($postData['code'.$i] == $value->id) {
							$yaPagos[$itera]['referenciaOrd'] = $value->referenciaOrd;
							$itera++;
						}
					}else{
						if ($postData['code'.$i] == $value->id) {
						//ingresa el codigo igual a el de idTareaOperarioDB
							foreach ($sumado as $valoresSumados) {
								if ($postData['code'.$i] != $valoresSumados['id'] and $valoresSumados['id'] != 1) {
									$totalNomina += $value->valorTarea * $value->cantidadPares;
									$referenciasAprovadas .= $value->referenciaOrd.', ';
									$idAprovados[$cantAprovados]['idTareaOperario'] = $value->id;
									$sumado=[
										0 => [
											'id' => 1
										]
									];
									$sumado[$i]['id'] = $value->id;
									$cantAprovados++;
								}
							}
						}
					}
				}//else { Este ticket no le pertenece }
			}
		}

		//Cuando termina el ciclo $cantAprovados termina con 1 mas entonces hay que restarle ese 1 mas
		$cantAprovados = $cantAprovados - 1;

		return $this->renderHTML('checkAddNomina.twig', [
			'totalNomina' => $totalNomina,
			'referenciasAprovadas' => $referenciasAprovadas,
			'nombrePersona' => $nombrePersona,
			'idPersona' => $idPersona,
			'cantAprovados' => $cantAprovados,
			'idAprovados' => $idAprovados
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
					$iterador = $postData['iterador'] - 1;
					$cantAprovados = $postData['cantAprovados'];
					
					if($iterador == $cantAprovados){
						
						if ($cantAprovados > 0) {
							for ($i=1; $i <=$iterador ; $i++) { 
								$tareaOperario = TareaOperario::find($postData['idTareaOperario'.$i]);
								$tareaOperario->pagaCheck = 1;
								$tareaOperario->idUserUpdate = $_SESSION['userId'];
								$tareaOperario->save();
								//echo " | idTarea:: ".$postData['idTareaOperario'.$i];
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
					}else{
						$responseMessage = 'Error, hay un problema al contar los tickets, contacta al administrador de software.!!';	
					}
				}catch(\Exception $e){
					$prevMessage = substr($e->getMessage(), 0, 15);
					if ($prevMessage =="These rules mus") {
						$responseMessage = 'Error, por favor contacta al administrador del software, este error es inusual.';
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
