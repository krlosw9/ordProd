<?php 

namespace App\Controllers;

use App\Models\ActividadTarea;

class IndexController extends BaseController{
	public function indexAction(){

		$jobs = ActividadTarea::all();

		//last() trae el ultimo row de la consulta y de esa forma podemos ver el ultimo id
		$ultimo = $jobs->last();

		$nombre = 'Carlos Waldo';
		$limitsMonths = 2000;

		return $this->renderHTML('index.twig', [
			'nombre' => $nombre,
			'jobs' => $jobs
		]);

	}
}

?>