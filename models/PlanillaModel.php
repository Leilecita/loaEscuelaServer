<?php
/**
 * Created by PhpStorm.
 * User: leila
 * Date: 30/08/2021
 * Time: 20:20
 */

require_once 'BaseModel.php';
class PlanillaModel extends BaseModel
{
    public function __construct()
    {
        parent::__construct();
        $this->tableName = 'planillas';
    }


    function findAllPlanillas($filters=array(),$paginator=array()){
        $conditions = join(' AND ',$filters);
        $query = 'SELECT * FROM '.$this->tableName .( empty($filters) ?  '' : ' WHERE '.$conditions ).' ORDER BY anio DESC LIMIT '.$paginator['limit'].' OFFSET '.$paginator['offset'];
        return $this->getDb()->fetch_all($query);
    }

    function getPlanillaByCategoriaAndSubCat($filters=array()){
        $conditions = join(' AND ',$filters);
        $query = 'SELECT * FROM planillas '.( empty($filters) ?  '' : ' WHERE '.$conditions ).' ORDER BY created DESC ';
        return $this->getDb()->fetch_row($query);

    }

    function getPlanillasByCategoriaAndSubCatALL($filters=array()){
        $conditions = join(' AND ',$filters);
        $query = 'SELECT * FROM planillas '.( empty($filters) ?  '' : ' WHERE '.$conditions ).' ORDER BY created DESC ';
        return $this->getDb()->fetch_all($query);

    }





}