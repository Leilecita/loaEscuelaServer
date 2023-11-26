<?php
/**
 * Created by PhpStorm.
 * User: leila
 * Date: 09/11/2022
 * Time: 14:38
 */
require_once "BaseModel.php";
class BeachBoxModel extends BaseModel
{
    public function __construct()
    {
        parent::__construct();
        $this->tableName = 'beach_boxes';
    }


    function getPaidAmountByClassCourseByDay($filters){

        $conditions = join(' AND ',$filters);
        $query = 'SELECT SUM(i.amount) as total FROM incomes i JOIN incomes_class_courses ic  ON ic.income_id = i.id JOIN class_courses c ON c.id = ic.class_course_id '.( empty($filters) ?  '' : ' WHERE '.$conditions );
        $response= $this->getDb()->fetch_row($query);
        if($response['total']!=null){
            return $response['total'];
        }else{
            $response['total']=0;
            return $response['total'];
        }
    }

    function findAllBoxes($filters=array()){
        $conditions = join(' AND ',$filters);
        $query = 'SELECT * FROM '.$this->tableName .( empty($filters) ?  '' : ' WHERE '.$conditions ).' ORDER BY created DESC LIMIT 10 ';
        return $this->getDb()->fetch_all($query);
    }



}
