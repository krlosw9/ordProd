<?php

namespace App\Controllers;

use App\Models\User;
use Respect\Validation\Validator as v;

class UsersController extends BaseController{
	public function getAddUserAction($request){
		return $this->renderHTML('addUser.twig');
	}

	public function postSaveUser($request){
		$responseMessage = null;

		if($request->getMethod()=='POST'){
			$postData = $request->getParsedBody();

			$userValidator = v::key('email', v::stringType()->notEmpty())
					->key('pass', v::stringType()->notEmpty());

			
			try{
				$userValidator->assert($postData);
				$postData = $request->getParsedBody();
				
				
				$user = new User();
				$user->nombre = $postData['email'];
				$user->contrasena = password_hash($postData['pass'],PASSWORD_DEFAULT);
				$user->save();
				
				$responseMessage = 'Usuario registrado.';
			}catch(\Exception $e){
				$responseMessage = $e->getMessage();
			}
		}

		return $this->renderHTML('addUser.twig',[
				'responseMessage' => $responseMessage
		]);
	}

	public function getUpdateUserAction(){
		return $this->renderHTML('updateUser.twig');
	}

	public function postUpdateUser($request){
		$responseMessage = null;

		if($request->getMethod()=='POST'){
			$postData = $request->getParsedBody();

			$userValidator = v::key('passOld', v::stringType()->notEmpty())
					->key('passNew', v::stringType()->notEmpty());

			
			try{
				$userValidator->assert($postData);
				$postData = $request->getParsedBody();
				
				$userQuery = User::where('id',$_SESSION['userId'])->first();
				
				if(\password_verify($postData['passOld'], $userQuery->contrasena)){

					$user = User::find($_SESSION['userId']);
					$user->contrasena = password_hash($postData['passNew'],PASSWORD_DEFAULT);
					$user->save();
					
					$responseMessage = 'Usuario actualizado.';
				}else{
					$responseMessage = 'Error, contraseña incorrecta';
				}

			}catch(\Exception $e){
				
				$prevMessage = substr($e->getMessage(), 0, 15);
					
				if ($prevMessage =="All of the requ") {
					$responseMessage = 'Error, debe completar los dos campos.';
				}else{
					//$responseMessage = substr($e->getMessage(), 0, 25);
					$responseMessage = $e->getMessage();
				}
			}
		}

		return $this->renderHTML('updateUser.twig',[
				'responseMessage' => $responseMessage
		]);
	}
}