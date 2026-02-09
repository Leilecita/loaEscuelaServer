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


    function getPresentsGroupByDate2($filters=array() ,$paginator=array()){
        $conditions = join(' AND ',$filters);
        $query = 'SELECT * FROM planillas_presentes '.( empty($filters) ?  '' : ' WHERE '.$conditions ).' group by DAY(fecha_presente), MONTH(fecha_presente), YEAR (fecha_presente) ORDER BY fecha_presente DESC LIMIT '.$paginator['limit'].' OFFSET '.$paginator['offset'];
        return $this->getDb()->fetch_all($query);
    }

    function getPresentsGroupByDate($filters = array(), $paginator = array()) {
        $conditions = join(' AND ', $filters);

        $query = "
        SELECT MIN(fecha) as fecha_presente
        FROM (
            -- Días con presentes
            SELECT fecha_presente as fecha
            FROM planillas_presentes
            " . (empty($filters) ? '' : 'WHERE ' . $conditions) . "
            UNION
            -- Días con pagos
            SELECT DATE(created) as fecha
            FROM incomes
        ) AS dias
        GROUP BY fecha
        ORDER BY fecha DESC
        LIMIT " . (int)$paginator['limit'] . " OFFSET " . (int)$paginator['offset'] . "
    ";

        return $this->getDb()->fetch_all($query);
    }

   /* function getPresentsGroupByMonth($filters = array(), $paginator = array()) {
        $conditions = join(' AND ', $filters);

        $query = "
        SELECT MIN(fecha) as fecha_presente
        FROM (
            -- Días con presentes
            SELECT fecha_presente as fecha
            FROM planillas_presentes
            " . (empty($filters) ? '' : 'WHERE ' . $conditions) . "
            UNION
            -- Días con pagos
            SELECT DATE(created) as fecha
            FROM incomes
        ) AS dias
        GROUP BY DATE_FORMAT(fecha, '%Y-%m')
        ORDER BY fecha_presente DESC
        LIMIT " . (int)$paginator['limit'] . " OFFSET " . (int)$paginator['offset'] . "
    ";

        return $this->getDb()->fetch_all($query);
    } */

    function getPresentsGroupByMonth($filters = array(), $paginator = array()) {
        $conditions = join(' AND ', $filters);

        $query = "
        SELECT 
            DATE_FORMAT(fecha_presente, '%Y-%m-01') AS fecha_presente
        FROM planillas_presentes
        " . (empty($filters) ? '' : 'WHERE ' . $conditions) . "
        GROUP BY DATE_FORMAT(fecha_presente, '%Y-%m')
        ORDER BY fecha_presente DESC
        LIMIT " . (int)$paginator['limit'] . " OFFSET " . (int)$paginator['offset'] . "
    ";

        return $this->getDb()->fetch_all($query);
    }



    function getPresentsGroupByDateSinPag($filters=array()){
        $conditions = join(' AND ',$filters);
        $query = 'SELECT * FROM planillas_presentes '.( empty($filters) ?  '' : ' WHERE '.$conditions ).' group by DAY(fecha_presente), MONTH(fecha_presente), YEAR (fecha_presente) ORDER BY fecha_presente DESC';
        return $this->getDb()->fetch_all($query);
    }

    function countPresentes_ant($filters=array()){
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

    function countPresentes($filters = array()) {
        $conditions = join(' AND ', $filters);

        $query = "
        SELECT COUNT(DISTINCT pp.alumno_id) as total
        FROM planillas_presentes pp
        " . (empty($filters) ? '' : ' WHERE ' . $conditions);

        $response = $this->getDb()->fetch_row($query);
        return (int) ($response['total'] ?? 0);
    }

    function existePresenteHoy($planillaId, $alumnoId, $fecha) {
        $query = "
        SELECT id
        FROM planillas_presentes
        WHERE planilla_id = ?
          AND alumno_id = ?
          AND DATE(fecha_presente) = ?
        LIMIT 1
    ";

        return $this->getDb()->fetch_row($query, [
            $planillaId,
            $alumnoId,
            $fecha
        ]);
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