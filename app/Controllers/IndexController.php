<?php 

namespace App\Controllers;

use App\Models\Job;

class IndexController extends BaseController{
	public function indexAction(){

		$jobs = Job::all();

		$nombre = 'Carlos Waldo';
		$limitsMonths = 2000;

		return $this->renderHTML('index.twig', [
			'nombre' => $nombre,
			'jobs' => $jobs
		]);

	}
}

?>