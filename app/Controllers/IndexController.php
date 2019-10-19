<?php 

namespace App\Controllers;

use App\Models\ActividadTarea;

class IndexController extends BaseController{
	public function indexAction(){

		$valorX='100';
		$year='2006';
		return $this->renderHTML('index.twig', [
			'valorX' => $valorX,
			'year' => $year
		]);

	}
}

?>