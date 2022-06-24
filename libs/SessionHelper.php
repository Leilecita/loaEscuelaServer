<?php
/**
 * Created by PhpStorm.
 * User: leila
 * Date: 29/04/2019
 * Time: 18:55
 */

require_once  __DIR__.'/../models/UserModel.php';

class SessionHelper
{
    static function getCurrentUser(){
        if(isset($_SERVER['HTTP_SESSION'])) {
            $session = $_SERVER['HTTP_SESSION'];
            $model = new UserModel();
            return $model->find(array('token = "'.$session.'"'));

        }else{
            return null;
        }
    }

    static function passwordToHash($password){
        return md5($password.'UNCHOCLOCONMANTECA');
    }

    static function genrateSessionToken(){
        return md5('UNCHOCLOCONMANTECA'.time());
    }
}