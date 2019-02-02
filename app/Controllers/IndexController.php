<?php 

namespace App\Controllers;

use App\Models\ActividadTarea;

class IndexController extends BaseController{
	public function indexAction(){

		$jobs = ActividadTarea::all();

		$nombre = 'Carlos Waldo';
		$limitsMonths = 2000;

		return $this->renderHTML('index.twig', [
			'nombre' => $nombre,
			'jobs' => $jobs
		]);

	}
}

?>