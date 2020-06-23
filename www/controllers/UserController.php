<?php

namespace cms\controllers;

use cms\models\Users;
use cms\core\View;

class UserController extends Controller{

	public function landingAction(){
        new View("landing-page","front");
    }

    public function homeAction(){
        new View("home","empty");
    }

    public function templateAction(){
        new View("template-create","empty");
    }

    public function usersAction(){
        new View("users","back");
    }

    public function signupAction(){
        new View("signup","front");
    }

    public function signinAction(){
        new View("signin","front");
    }

    public function addFilmAction()
    {
        new view("addfilm","back");
    }

	public function getUserAction($params)
    {
        $userManager = new UserManager();

        $user = $userManager->find($params['id']);

        if(!$user) {
            throw new NotFoundException("User not found");
		}
        return $user;
    }



	public function loginAction()
    {

        $registerType = new LoginType();

        if ( $_SERVER["REQUEST_METHOD"] == "POST") {
            //Vérification des champs
            $this->render("register", "account", [
                "form" => $registerType,
                "errors" => Validator::formLoginValidate( $registerType, $_POST )
            ]);
        } else {
            $this->render("register", "account", [
                "form" => $registerType
            ]);
        }
      
	}
	
    public function registerAction()
    {
        $registerType = new RegisterType();

        if ( $_SERVER["REQUEST_METHOD"] == "POST") {
            //Vérification des champs
            $this->render("register", "account", [
                "form" => $registerType,
                "errors" => Validator::formRegisterValidate( $registerType, $_POST )
            ]);
        } else {
            $this->render("register", "account", [
                "form" => $registerType
            ]);
        }
    }
    
    public function buildPage()
    {

    }


	public function forgetPwdAction(){
		$myView = new View("forgetPwd", "account");
	}
}