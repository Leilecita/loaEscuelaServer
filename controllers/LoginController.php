<?php
/**
 * Created by PhpStorm.
 * User: leila
 * Date: 30/08/2021
 * Time: 21:52
 */

require_once 'BaseController.php';
require_once  __DIR__.'/../models/UserModel.php';
require_once  __DIR__.'/../libs/SessionHelper.php';

define('KEY_ACCESS',"lorena");

class LoginController extends BaseController
{
    function __construct()
    {
        parent::__construct();
        $this->model = new UserModel();
    }


    function login(){

        $username =  $_GET['name'];
        $password =  $_GET['hash_password'];
        $passwordHashed = SessionHelper::passwordToHash($password);

        error_log( $_GET['name']);

        error_log($_GET['hash_password']);
        error_log($passwordHashed);

        $user = $this->model->find(array('name = "'.$username.'"','hash_password = "'.$passwordHashed.'"'));

        if($user){
            $token = SessionHelper::genrateSessionToken();
            $this->model->update($user['id'],array('token' => $token));
            $result = array('token' => $token,'name' => $user['name']);
            $this->returnSuccess(200,$result);
        }else{
            $this->returnError(401,'Usuario o contraseña incorrecto');
        }

    }

    function register(){

        if($_GET['key_access'] == KEY_ACCESS){
            $data = (array)json_decode(file_get_contents("php://input"));

            unset($data['id']);
            $res = $this->model->save($data);
            if($res<0){
                $this->returnError(404,null);
            }else{
                $inserted = $this->model->findById($res);

                $this->mail($inserted['mail'],$inserted['hash_password']);

                $hash_password=SessionHelper::passwordToHash($inserted['hash_password']);;
                $this->model->update($inserted['id'],array('hash_password' => $hash_password));
                $this->returnSuccess(201,$inserted);
            }
        }else{
            $this->returnError(400,"Codigo de acceso no valido");
        }
    }


    function post(){
        if(isset($_GET['method'])){
            $this->method();
        }else{
            $this->beforeMethod();
            $data = (array)json_decode(file_get_contents("php://input"));
            unset($data['id']);
            $res = $this->getModel()->save($data);
            if($res<0){
                $this->returnError(404,null);
            }else{
                $inserted = $this->getModel()->findById($res);
                $this->returnSuccess(201,$inserted);
            }
        }
    }

    function mail($mail,$password){

        $to = $mail;
        $subject = "Contraseña app";
        $txt = "Contraseña : ".$password;
        mail($to,$subject,$txt);
    }
}