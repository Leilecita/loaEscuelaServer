<?php
/**
 * Created by PhpStorm.
 * User: leila
 * Date: 09/06/2022
 * Time: 15:18
 */

require_once 'BaseModel.php';

class SeasonModel extends BaseModel
{
    public function __construct()
    {
        parent::__construct();
        $this->tableName = 'seasons';
    }





}