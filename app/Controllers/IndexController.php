<?php 

namespace App\Controllers;

use App\Models\{Job,Project};

class IndexController extends BaseController{
	public function indexAction(){

		$jobs = Job::all();

		$project1 = new Project('Project 1','Description 1');
		$projects = [
		  $project1
		];

		$nombre = 'Carlos Waldo';
		$limitsMonths = 2000;

		return $this->renderHTML('index.twig', [
			'nombre' => $nombre,
			'jobs' => $jobs
		]);

	}
}

?>