<?php
/**
 * Created by PhpStorm.
 * User: leila
 * Date: 22/12/2020
 * Time: 17:03
 */

require_once 'BaseModel.php';

class StudentModel extends BaseModel
{
    public function __construct()
    {
        parent::__construct();
        $this->tableName = 'students';
    }

    function findByDni($dni){
        return $this->getDb()->fetch_row('SELECT * FROM '.$this->tableName.' WHERE dni = ?',$dni);
    }

    function findAllStudents($filters=array(),$paginator=array(), $orderby){
        $conditions = join(' AND ',$filters);
        $query = 'SELECT * FROM '.$this->tableName .( empty($filters) ?  '' : ' WHERE '.$conditions ).' ORDER BY '.$orderby.' LIMIT '.$paginator['limit'].' OFFSET '.$paginator['offset'];
        return $this->getDb()->fetch_all($query);
    }


    function countStudents($filters=array()){
        $conditions = join(' AND ',$filters);
        $query = 'SELECT COUNT(s.id) as total FROM students s JOIN planillas_alumnos pa ON s.id = pa.alumno_id '.( empty($filters) ?  '' : ' WHERE '.$conditions );
        $response= $this->getDb()->fetch_row($query);

        if($response['total'] != null){
            return $response['total'];
        }else{
            $response['total']=0;
            return   $response['total'];
        }
    }

    function countStudentsOR($filters=array()){
        $conditions = join(' OR ',$filters);
        $query = 'SELECT COUNT(*) as total FROM students s JOIN planillas_alumnos pa ON s.id = pa.alumno_id '.( empty($filters) ?  '' : ' WHERE '.$conditions );
        $response= $this->getDb()->fetch_row($query);


        if($response['total'] != null){
            return $response['total'];
        }else{
            $response['total']=0;
            return   $response['total'];
        }


    }

    function getStudentsAssists($filters=array(),$paginator=array(),$orderby){
        $conditions = join(' AND ',$filters);
        $query = 'SELECT *, pa.created as pa_created, s.nombre as nombre_al s.id as student_id FROM students s JOIN planillas_alumnos pa ON s.id = pa.alumno_id '.( empty($filters) ?  '' : ' WHERE '.$conditions ).'
 ORDER BY '.$orderby.' LIMIT '.$paginator['limit'].' OFFSET '.$paginator['offset'];
        return $this->getDb()->fetch_all($query);

    }

    //probar este
    function getStudentsAssists2($filters=array(),$paginator=array()){
        $conditions = join(' AND ',$filters);
        $query = 'SELECT *, pp.fecha_presente fecha_pre, pa.created as pa_created, s.id as student_id FROM students s JOIN planillas_alumnos pa ON s.id = pa.alumno_id JOIN 
planillas_presentes pp ON pa.planilla_id = pp.planilla_id and pa.alumno_id = pp.alumno_id '.( empty($filters) ?  '' : ' WHERE '.$conditions ).'
 ORDER BY pa_created, fecha_pre DESC
        LIMIT '.$paginator['limit'].' OFFSET '.$paginator['offset'];
        return $this->getDb()->fetch_all($query);

    }


    function getStudentsAssistsOR($filters=array(),$paginator=array()){
        $conditions = join(' OR ',$filters);
        $query = 'SELECT *, pa.created as pa_created, s.id as student_id FROM students s JOIN planillas_alumnos pa ON s.id = pa.alumno_id '.( empty($filters) ?  '' : ' WHERE '.$conditions ).'
 ORDER BY pa_created DESC
        LIMIT '.$paginator['limit'].' OFFSET '.$paginator['offset'];
        return $this->getDb()->fetch_all($query);

    }



}