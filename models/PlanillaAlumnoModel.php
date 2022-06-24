<?php
/**
 * Created by PhpStorm.
 * User: leila
 * Date: 30/08/2021
 * Time: 20:27
 */
require_once 'BaseModel.php';
class PlanillaAlumnoModel extends BaseModel
{
    public function __construct()
    {
        parent::__construct();
        $this->tableName = 'planillas_alumnos';
    }


}