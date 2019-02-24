<?php 

namespace App\Controllers;

use App\Models\{Personas, TareaOperario};
use Respect\Validation\Validator as v;
use Zend\Diactoros\Response\RedirectResponse;

class ReportesController extends BaseController{
	//Lista todas la Hormas Ordenando por posicion
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

	
	//Consulta de nomina individual
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

	
	
}

?>
