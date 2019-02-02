<?php 

namespace App\Controllers;

use App\Models\Job;
use Respect\Validation\Validator as v;
use Zend\Diactoros\Response\RedirectResponse;

class JobsController extends BaseController{
	//Registra la Actividad-Tarea
	public function getAddJobAction($request){
		$responseMessage = null;
			
		if($request->getMethod()=='POST'){
			$postData = $request->getParsedBody();

			$jobValidator = v::key('nombre', v::stringType()->length(1, 30)->notEmpty())
					->key('valorPorPar', v::numeric()->positive()->between(0, 100000))
					->key('posicion', v::numeric()->positive()->between(1, 20));
			if($_SESSION['userId']){
				try{
					$jobValidator->assert($postData);
					$postData = $request->getParsedBody();
					
					$job = new Job();
					$job->nombre = $postData['nombre'];
					$job->valorPorPar = $postData['valorPorPar'];
					$job->posicion = $postData['posicion'];
					$job->observacion = $postData['observacion'];
					$job->idUserRegister = $_SESSION['userId'];
					$job->idUserUpdate = $_SESSION['userId'];
					$job->save();
					
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
		return $this->renderHTML('addJob.twig',[
				'responseMessage' => $responseMessage
			]);
	}

	//Lista todas la Actividad-Tarea Ordenando por posicion
	public function getListActOperario(){
		$responseMessage = null;
		$jobs = Job::orderBy('posicion')->get();

		return $this->renderHTML('listActOperario.twig', [
			'jobs' => $jobs
		]);
	}

	/*Al seleccionar uno de los dos botones (Eliminar o Actualizar) llega a esta accion y verifica cual de los dos botones oprimio si eligio el boton eliminar(del) elimina el registro de where $id Pero
	Si elige actualizar(upd) cambia la ruta del renderHTML y guarda una consulta de los datos del registro a modificar para mostrarlos en formulario de actualizacion llamado updateActOperario.twig y cuando modifica los datos y le da guardar a ese formulaio regresa a esta class y elige la accion getUpdateActivity()*/
	public function postUpdDelActOperario($request){
		$responseMessage = null;
		$quiereActualizar = false;
		$ruta='listActOperario.twig';

		if($request->getMethod()=='POST'){
			$postData = $request->getParsedBody();
			
			$id = $postData['id'] ?? false;
			if ($id) {
				if($postData['boton']=='del'){
					$actOpe = new Job();
					$actOpe->destroy($id);
					$responseMessage = "Se elimino la actividad";
				}elseif ($postData['boton']=='upd') {
					$quiereActualizar=true;
				}
			}else{
				$responseMessage = 'Debe Seleccionar una actividad';
			}
		}
		
		if ($quiereActualizar){
			//si quiere actualizar hace una consulta where id=$id y la envia por el array del renderHtml
			$jobs = Job::find($id);
			$ruta='updateActOperario.twig';
		}else{
			$jobs = Job::orderBy('posicion')->get();
		}
		return $this->renderHTML($ruta, [
			'jobs' => $jobs,
			'idUpdate' => $id,
			'responseMessage' => $responseMessage
		]);
	}

	//en esta accion se registra las modificaciones del registro
	public function getUpdateActivity($request){

		$responseMessage = null;
				
		if($request->getMethod()=='POST'){
			$postData = $request->getParsedBody();

			$jobValidator = v::key('nombre', v::stringType()->notEmpty())
					->key('valorPorPar', v::numeric()->positive()->between(0, 100000))
					->key('posicion', v::numeric()->positive()->between(1, 20));

			
			if($_SESSION['userId']){
				try{
					$jobValidator->assert($postData);
					$postData = $request->getParsedBody();
					
					//la siguiente linea hace una consulta en la DB y trae el registro where id=$id y lo guarda en actOpe y posteriormente remplaza los valores y con el ->save() guarda la modificacion en la DB
					$actOpe = Job::find($postData['id']);
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

		$jobs = Job::orderBy('posicion')->get();
		return $this->renderHTML('listActOperario.twig',[
				'jobs' => $jobs,
				'responseMessage' => $responseMessage
			]);
	}
}

?>
