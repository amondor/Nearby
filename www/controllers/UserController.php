<?php

namespace cms\controllers;

use cms\models\User;
use cms\core\View;
use cms\core\Mailer;
use cms\core\Helpers;
use cms\managers\UserManager;
use cms\managers\MovieManager;
use cms\managers\PageManager;
use cms\core\NotFoundException;
use cms\core\Controller;
use cms\core\Page;
use cms\core\Validator;
use cms\forms\LoginType;
use cms\forms\RegisterType;
use cms\forms\ResetPwdFormType;
use cms\forms\ResetPwdType;
use cms\models\Movie;
use cms\PHPMailer\src\PHPMailer;

class UserController extends Controller{

    private $email;
    private $password;

    // public function __construct()
    // {
    //     isset($_POST['email']) ? $this->email = $_POST['email'] : null;
    //     isset($_POST['password']) ? $this->password = $_POST['password'] : null;
    // }

	public function landingAction(){
        new View("landing-page","front");
    }

    public function homeAction(){
        new View("home","landing");
    }

    public function statAction(){
        new View("stat","back");
    }

    public function noPermissionAction(){
        new View("no-permission", "front");
    }

    public function usersAction(){
        $userManager = new UserManager(User::class,'user');
        $users = $userManager->read();

        $this->render("users", "back", ['users' => $users]);
    }

    public function editUserAction($id){
        $userManager = new UserManager(User::class,'user');
        $userId = $userManager->read($id);

        $this->render("edit-user", "back", ['myUser' => $userId]);

        if( $_SERVER["REQUEST_METHOD"] == "POST"){
            $user = new User();
            
            $user->setId($id);
            $user->setLastname($_POST['lastname']);
            $user->setFirstname($_POST['firstname']);

            if(empty($_POST['statut'])){
                $user->setStatut(reset($userId)->getStatut());
            }else{
                $user->setStatut($_POST['statut']);
            }

            if(empty($_POST['allow'])){
                $user->setAllow(reset($userId)->getAllow());
            }else{
                $user->setAllow($_POST['allow']);
            }
            $user->setVerified(1);            
            $user->setPassword(reset($userId)->getPassword());
            $user->setEmail(reset($userId)->getEmail());

            if(!empty($_FILES['image_profile']['name'])){
                $data_image = $this->uploadImage();
                if(isset($data_image) && !empty($data_image['image'])){
                    $user->setImage_profile($data_image['image']);
                }
            }else{
                $user->setImage_profile(reset($userId)->getImage_profile());
            }
            $userManager->save($user);

            echo "<script>alert('User modifi?? avec succ??s');</script>";
        }
    }

    public function deleteUserAction($id){
        new View('confirm-page','back');

        $userManager = new UserManager(User::class,'user');
        $userManager->delete($id);

        echo "<script>alert('User supprim?? avec succ??s');</script>";
    }

    public function uploadImage()
    {
        if(isset($_FILES['image_profile'])){
            $output_dir = "public/images";//Path for file upload
            $RandomNum = time();
            $ImageName = str_replace(' ','-',strtolower($_FILES['image_profile']['name']));
            $ImageType = $_FILES['image_profile']['type']; //"image/png", image/jpeg etc.
            $ImageExt = substr($ImageName, strrpos($ImageName, '.'));
            $ImageExt = str_replace('.','',$ImageExt);
            $ImageName = preg_replace("/\.[^.\s]{3,4}$/", "", $ImageName);
            $NewImageName = $ImageName.'-'.$RandomNum.'.'.$ImageExt;
            $ret[$NewImageName]= $output_dir.$NewImageName;
            move_uploaded_file($_FILES["image_profile"]["tmp_name"],$output_dir."/".$NewImageName );
            $data = array(
            'image' =>$NewImageName
            );
            return $data;
        }
    }

    public function accountActivationAction($token)
    {
        $user = (new UserManager(User::class,'user'))->getUserByToken($token);
        
        if ($user) {
            reset($user)->setVerified(1);
            (new UserManager(User::class,'user'))->save(reset($user));
            new View('mail-check', 'front');
        }else{
            echo "<script>alert('user inconnu');</script>";
            Helpers::alert_popup('user inconnu');
            new View("user-token-unknown", "front");
        }
    }

    public function mailNotCheckedAction(){
        new View('mail-not-checked', 'front');
    }

    public function sessionNotStartAction(){
        new View('session-not-start','front');
    }

	public function getUserAction($params)
    {
        $userManager = new UserManager('user', 'user');

        $user = $userManager->find($params['id']);

        if(!$user) {
            throw new NotFoundException("User not found");
		}
        return $user;
    }

    public function showUserAction($id){
        $user = new UserManager(User::class, 'user');
        $user_id = $user->read($id);

        $this->render('show-user','back', ['myUser' => $user_id]);
    }

    public function reportCommentAction($id){
        $userManager = new UserManager(User::class, 'user');

        $users =  $userManager->read($id);
        $user = array_shift($users);

        $mail = new Mailer();

        if($user->getReport() < 5){
            $user->setReport($user->getReport() + 1);
            $userManager->save($user);
            $mail->sendUserReported($user->getEmail(), $user->getFirstname());
        }elseif($user->getReport() >= 5){
            $user->setStatut(0);
            $userManager->save($user);
            $mail->sendUserBanned($user->getEmail(), $user->getFirstname());
        }
        new View('confirm-page');
        Helpers::alert_popup('Utilisateur signal?? avec succ??s');
    }

	public function loginAction()
    {
        if(APP_INSTALLED == 'false'){
            $view = Helpers::getUrl("installer", "installer");
            $newUrl = trim($view, "/");
            header("Location: ".$newUrl);
        }

        $form = $this->createForm(LoginType::class);
        $form->handle();

        if($form->isSubmit() && $form->isValid())
        { 
            
            $userManager = new UserManager(User::class,'user');
            $users = $userManager->read();
            
            $userCheck = $userManager->checkUserInDb($_POST[$form->getName().'_email'] , $users, $_POST[$form->getName().'_password']);
            if($userCheck){
                if($userCheck->getVerified() == 1){
                    session_start();
                    $_SESSION['userId'] = $userCheck->getId();
                    $view = Helpers::getUrl("Page", "templateCreate");
                    $newUrl = trim($view, "/");
                    header("Location: ".$newUrl);
                }else{
                    $view = Helpers::getUrl("User", "mailNotChecked");
                    $newUrl = trim($view, "/");
                    header("Location: ".$newUrl);
                }
            }
            else
            {
                $this->render("login", "account", [
                    "configFormUser" => $form
                ]);
            }
        }
        else
        {
            $this->render("login", "account", [
                "configFormUser" => $form
            ]);
        }
    }

    public function forgetPasswordAction(){
        $form = $this->createForm(ResetPwdType::class);
        $form->handle();
        $userManager = new UserManager(User::class, 'user');
        
        if($form->isSubmit() && $form->isValid())
        { 
            $myUser = $userManager->findBy(['email' => $_POST[$form->getName().'_email']]);
            
            if(empty($myUser)){
                Helpers::alert_popup('Utilisateur inconnu');
                $url = Helpers::getUrl("User", "forgetPassword");
                echo "<meta http-equiv='refresh' content='0;url='.$url />";
            }else{
                $user = array_shift($myUser);

                if($user->getStatut() == 1){
                    $token = bin2hex(random_bytes(50));
                    $user->setToken($token);
                    $userManager->save($user);
                    
                    $mail = new Mailer();
                    $userNewEmail = $_POST[$form->getName().'_email'];
                    $mail->sendForgetPwd($userNewEmail, $user->getFirstname(), $token);

                    Helpers::alert_popup("Un lien de r??initialisation vous a ??t?? envoy?? par mail !");

                    $view = Helpers::getUrl("User", "login");
                    $newUrl = trim($view, "/");
                    header("Location: " . $newUrl);
                }else{
                    Helpers::alert_popup("Votre compte n'est pas autoris?? ?? acc??der au site");
                    $url = Helpers::getUrl("User", "login");
                    echo "<meta http-equiv='refresh' content='0;url='.$url />";
                }
            }
        }else{
            $this->render("forget-pwd", "account", [
            "configFormUser" => $form
            ]);
        }
    }   

    public function resetFormPwdAction($token){
        $form = $this->createForm(ResetPwdFormType::class);
        $form->handle();
        $userManager = new UserManager(User::class, 'user');
        
        $user = $userManager->getUserByToken($token);

        if(empty($user)){
            Helpers::alert_popup('Utilisateur inconnu');
            $url = Helpers::getUrl("User", "login");
            echo "<meta http-equiv='refresh' content='0;url='.$url />";
        }else{
            if($form->isSubmit() && $form->isValid())
            { 
                $pwdHash = password_hash($_POST[$form->getName().'_password'], PASSWORD_DEFAULT);
                $myUser = array_shift($user);
                $myUser->setPassword($pwdHash);

                $userManager->save($myUser);

                Helpers::alert_popup("Mot de passe r??initialis??, connectez-vous !");
                $url = Helpers::getUrl("User", "home");
                echo "<meta http-equiv='refresh' content='0;url='.$url />";
            }else{
                $this->render("reset-form-pwd", "account", [
                    "configFormUser" => $form
                ]);
            }
        }
    }

    public function registerAction()
    {
        if(APP_INSTALLED == 'false'){
            $view = Helpers::getUrl("installer", "installer");
            $newUrl = trim($view, "/");
            header("Location: ".$newUrl);
        }

        $form = $this->createForm(RegisterType::class);
        $form->handle();

        if($form->isSubmit() && $form->isValid())
        { 
            $userManager = new UserManager(User::class,'user');
            
            if ($userManager->checkIfMailExist($_POST[$form->getName().'_email']) != true)
            {
                $token = bin2hex(random_bytes(50));          
                $user = new User;
                $user->setLastname($_POST[$form->getName().'_lastname']);
                $user->setFirstname($_POST[$form->getName().'_firstname']);
                $user->setEmail($_POST[$form->getName().'_email']);
                $pwdHash = password_hash($_POST[$form->getName().'_password'], PASSWORD_DEFAULT);
                $user->setPassword($pwdHash);
                $user->setAllow(0);
                $user->setToken($token);
                $user->setStatut(1);
                $user->setImage_profile('null');

                $userManager->save($user);

                // on v??rifie si le save a bien ??t?? fait et on envoie un mail
                $users = $userManager->read();
                $userCheck = $userManager->checkUserInDb($_POST[$form->getName().'_email'], $users, $_POST[$form->getName().'_password']);

                if($userCheck){
                    $mail = new Mailer();
                    $result = $mail->sendVerifAuth($_POST[$form->getName().'_email'], $token, $_POST[$form->getName().'_firstname']);
                    if(!$result){
                        Helpers::alert_popup('Confirmer votre adresse en cliquant sur le lien envoy?? par mail !');
                        $this->render("register", "account", [
                            "configFormUser" => $form
                        ]);
                    }
                }
            } else {
                $this->render("register", "account", [
                    "configFormUser" => $form
                ]);
            }
        }
        else
        {
            $this->render("register", "account", [
                "configFormUser" => $form
            ]);
        }   
    }

}
