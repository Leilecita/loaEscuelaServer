<?php
/**
 * Created by PhpStorm.
 * User: leila
 * Date: 30/08/2021
 * Time: 21:38
 */


require_once 'BaseModel.php';


class UserModel extends BaseModel
{
    public function __construct()
    {
        parent::__construct();
        $this->tableName = 'users';
    }

}