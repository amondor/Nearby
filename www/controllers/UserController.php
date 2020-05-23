<?php

namespace mvc\controllers;

use mvc\models\Users;
use mvc\core\View;

class UserController{
	public function loginAction(){
		$myView = new View("login", "account");
	}

	public function registerAction(){

		$configForm = users::getRegisterForm();

		if($_SERVER["REQUEST_METHOD"] == "POST"){
			$error = Validator::formValidate($configForm, $_POST);
		}

		$user = new Users();
		
		$user->setFirstName("bob");
		$user->setLastName("berry");
		$user->setEmail("boberry@gmail.com");
		$user->setPwd("anonymous");
		$user->setStatus(0);

		$user->save();
		// $user->count();

		$myView = new View("register", "account");
		$myView->assign("configForm",$configForm);
	}

	public function forgetPwdAction(){
		$myView = new View("forgetPwd", "account");
	}
}