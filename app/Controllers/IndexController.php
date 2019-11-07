<?php 

namespace App\Controllers;

use App\Models\{ActividadTarea,InfoOrdenProduccion, Clientes, Pedido, ModelosInfo};

class IndexController extends BaseController{
	public function indexAction(){
		
		$queryOrden = InfoOrdenProduccion::all();
		$cantOrdenes = $queryOrden->count();

		$queryClientes = Clientes::where("tipo","=",0)->get();
		$cantClientes = $queryClientes->count();

		$queryPedidos = Pedido::all();
		$cantPedidos = $queryPedidos->count();

		$queryModelos = ModelosInfo::all();
		$cantModelos = $queryModelos->count();

		$valorX='100';
		$year='2006';
		return $this->renderHTML('index.twig', [
			'cantOrdenes' => $cantOrdenes,
			'cantClientes' => $cantClientes,
			'cantPedidos' => $cantPedidos,
			'cantModelos' => $cantModelos,
			'valorX' => $valorX,
			'year' => $year
		]);

	}
}

?>