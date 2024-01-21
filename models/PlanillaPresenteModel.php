<?php
/**
 * Created by PhpStorm.
 * User: leila
 * Date: 30/08/2021
 * Time: 20:25
 */

require_once 'BaseModel.php';
class PlanillaPresenteModel extends BaseModel
{
    public function __construct()
    {
        parent::__construct();
        $this->tableName = 'planillas_presentes';
    }

    function getPresent($filters=array()){
        $conditions = join(' AND ',$filters);
        $query = 'SELECT * FROM planillas_presentes '.( empty($filters) ?  '' : ' WHERE '.$conditions ).' ORDER BY created DESC ';
        return $this->getDb()->fetch_row($query);

    }

    function findAllPresentsByStudent($filters=array()){
        $conditions = join(' AND ',$filters);
        $query = 'SELECT * FROM '.$this->tableName .( empty($filters) ?  '' : ' WHERE '.$conditions ).' ORDER BY fecha_presente DESC ';
        return $this->getDb()->fetch_all($query);
    }


    function getPresentsGroupByDate($filters=array() ,$paginator=array()){
        $conditions = join(' AND ',$filters);
        $query = 'SELECT * FROM planillas_presentes '.( empty($filters) ?  '' : ' WHERE '.$conditions ).' group by DAY(fecha_presente), MONTH(fecha_presente), YEAR (fecha_presente) ORDER BY fecha_presente DESC LIMIT '.$paginator['limit'].' OFFSET '.$paginator['offset'];
        return $this->getDb()->fetch_all($query);
    }

    function getPresentsGroupByDateSinPag($filters=array()){
        $conditions = join(' AND ',$filters);
        $query = 'SELECT * FROM planillas_presentes '.( empty($filters) ?  '' : ' WHERE '.$conditions ).' group by DAY(fecha_presente), MONTH(fecha_presente), YEAR (fecha_presente) ORDER BY fecha_presente DESC';
        return $this->getDb()->fetch_all($query);
    }

    function countPresentes($filters=array()){
        $conditions = join(' AND ',$filters);
        $query = 'SELECT COUNT(pp.id) as total FROM planillas_presentes pp JOIN planillas p ON pp.planilla_id = p.id '.( empty($filters) ?  '' : ' WHERE '.$conditions );
        $response=$this->getDb()->fetch_row($query);
        if($response['total'] != null){
            return $response['total'];
        }else{
            $response['total']=0;
            return   $response['total'];
        }

    }

    function countPresentesByStudent($filters=array()){
        $conditions = join(' AND ',$filters);
        $query = 'SELECT COUNT(id) as total FROM planillas_presentes '.( empty($filters) ?  '' : ' WHERE '.$conditions );
        $response=$this->getDb()->fetch_row($query);
        if($response['total'] != null){
            return $response['total'];
        }else{
            $response['total']=0;
            return   $response['total'];
        }

    }






}