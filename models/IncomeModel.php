<?php
/**
 * Created by PhpStorm.
 * User: leila
 * Date: 25/11/2019
 * Time: 12:19
 */

require_once 'BaseModel.php';
class IncomeModel extends BaseModel
{
    public function __construct()
    {
        parent::__construct();
        $this->tableName = 'incomes';
    }

    function sumAmountIncomesByDate($filters=array()){
        $conditions = join(' AND ',$filters);
        $query = 'SELECT SUM(i.amount) as total FROM incomes i JOIN incomes_class_courses ic ON i.id = ic.income_id JOIN class_courses c ON c.id = ic.class_course_id '.( empty($filters) ?  '' : ' WHERE '.$conditions );
        $response= $this->getDb()->fetch_row($query);

        if($response['total'] != null){
            return $response['total'];
        }else{
            $response['total']=0;
            return   $response['total'];
        }

    }



    function getAllIncomesByDate($dateSince,$dateTo,$paginator){
        /*
         * ( SELECT i.amount, i.created, concat(s.name," ",s.surname) as description FROM `incomes_class_courses` icc,
         *  class_courses cc, students s, incomes i where icc.class_course_id= cc.id and cc.student_id=s.id and i.id=icc.income_id and i.created > "2019-12-06" )
         * UNION ( SELECT i.amount, i.created, concat(s.name," ",s.surname) as description FROM `incomes_rents` ir, rents r, students s, incomes i
         * where ir.rent_id=r.id and r.student_id=s.id and i.id=ir.income_id and i.created > "2019-12-06" ) order by created asc
         */

        $query= 'SELECT i.id as income_id, i.amount, i.payment_method ,i.created, concat(s.nombre," ",s.apellido) as description FROM `incomes_class_courses` icc,class_courses cc, students s, incomes i
          where icc.class_course_id= cc.id and cc.student_id=s.id and i.id=icc.income_id and i.created < \''.$dateTo.'\' and i.created > '."'$dateSince'".' order by created desc LIMIT '.$paginator['limit'].' OFFSET '.$paginator['offset'];

        return $this->getDb()->fetch_all($query);

    }

    function getAllIncomes($paginator){
        /*
         * ( SELECT i.amount, i.created, concat(s.name," ",s.surname) as description FROM `incomes_class_courses` icc,
         *  class_courses cc, students s, incomes i where icc.class_course_id= cc.id and cc.student_id=s.id and i.id=icc.income_id and i.created > "2019-12-06" )
         * UNION ( SELECT i.amount, i.created, concat(s.name," ",s.surname) as description FROM `incomes_rents` ir, rents r, students s, incomes i
         * where ir.rent_id=r.id and r.student_id=s.id and i.id=ir.income_id and i.created > "2019-12-06" ) order by created asc
         */

        $query= 'SELECT cc.category as category, i.id as income_id, i.amount as amount, i.payment_method as payment_method ,i.created as income_created, concat(s.nombre," ",s.apellido) as description FROM `incomes_class_courses` icc,class_courses cc, students s, incomes i
          where icc.class_course_id= cc.id and cc.student_id=s.id and i.id=icc.income_id order by i.created desc LIMIT '.$paginator['limit'].' OFFSET '.$paginator['offset'];

        return $this->getDb()->fetch_all($query);

    }

}