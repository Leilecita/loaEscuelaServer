<?php
/**
 * Created by PhpStorm.
 * User: leila
 * Date: 21/06/2021
 * Time: 12:48
 */

require_once "BaseModel.php";
class HighschoolModel extends BaseModel
{
    public function __construct()
    {
        parent::__construct();
        $this->tableName = 'highschooles';
    }

}
