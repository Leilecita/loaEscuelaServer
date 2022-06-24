<?php
/**
 * Created by PhpStorm.
 * User: leila
 * Date: 21/06/2021
 * Time: 12:27
 */

require_once 'BaseModel.php';
class IncomeHighschoolModel extends BaseModel
{
    public function __construct()
    {
        parent::__construct();
        $this->tableName = 'incomes_highschool';
    }

    function getIncomesByHighschoolId($id){

        return $this->getDb()->fetch_all('SELECT * FROM incomes_highschool WHERE highschool_id = ?',$id);
    }

    function deleteIncomesByCourseId($id){

        $this->getDb()->delete('DELETE FROM incomes_highschool WHERE highschool_id = ?',$id);
    }

    function joinIncomesAndClassCourseIncomes($filters){
        $conditions = join(' AND ',$filters);
        $query = 'SELECT *, ic.id as income_course_id  FROM incomes i JOIN incomes_highschool ic ON ic.income_id = i.id '.( empty($filters) ?  '' :
                ' WHERE '.$conditions ).' ORDER BY i.created DESC';
        return $this->getDb()->fetch_all($query);
    }

    function getPaidAmountByClassCourseIncomes($filters){

        $conditions = join(' AND ',$filters);
        $query = 'SELECT SUM(amount) as total FROM incomes i JOIN incomes_highschool ic ON ic.income_id = i.id '.( empty($filters) ?  '' : ' WHERE '.$conditions );
        $response= $this->getDb()->fetch_row($query);
        if($response['total']!=null){
            return $response['total'];
        }else{
            $response['total']=0;
            return $response['total'];
        }
    }

}