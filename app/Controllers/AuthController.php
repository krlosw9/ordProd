<?php

namespace App\Controllers;

use App\Models\User;
use Respect\Validation\Validator as v;
use Zend\Diactoros\Response\RedirectResponse;

class AuthController extends BaseController{
	public function getLogin(){
		return $this->renderHTML('login.twig');
	}

	public function postLogin($request){
		$postData = $request->getParsedBody();

		$responseMessage = null;

		//Consulta si el nombre de usuario (email) esta en base de datos y si lo esta trae el primer registro que encuentra con todos sus datos idCedula, nombre, contrasena y otros para compararlos contra los que se trae en el $request que pasa a ser $postData['email'] 

		$user = User::where('nombre',$postData['email'])->first();
		if ($user) {
			if(\password_verify($postData['pass'], $user->contrasena)){
				$_SESSION['userId'] = $user->id;
				$_SESSION['userName'] = $user->nombreUsuario;
				$_SESSION['userRol'] = $user->rol;
				$_SESSION['companyName'] = 'Cheduar';
				return new RedirectResponse('./');
			}else{
				$responseMessage = 'Usuario y Contraseña incorrecto';
			}
		}else{
			$responseMessage = 'Usuario y Contraseña incorrecto';
		}

		return $this->renderHTML('login.twig',[
			'responseMessage' => $responseMessage
		]);
	}


	public function getLogout(){
		unset($_SESSION['userId']);
		unset($_SESSION['userName']);
		unset($_SESSION['userRol']);
		unset($_SESSION['companyName']);
		return new RedirectResponse('login');
	}
}