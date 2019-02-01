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
}