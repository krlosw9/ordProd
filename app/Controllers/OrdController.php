<?php 

namespace App\Controllers;

use App\Models\{Job,Project};

class OrdController extends BaseController{
	public function ordAction(){

		return $this->renderHTML('layout.twig');

	}
}

?>