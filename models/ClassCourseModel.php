<?php
/**
 * Created by PhpStorm.
 * User: leila
 * Date: 30/10/2019
 * Time: 10:18
 */

require_once "BaseModel.php";
class ClassCourseModel  extends BaseModel
{
    public function __construct()
    {
        parent::__construct();
        $this->tableName = 'class_courses';
    }

    function joinStudentOnCourse($filters,$paginator){
        $conditions = join(' AND ',$filters);
        $query = 'SELECT *, c.id as class_course_id, s.id as student_id, c.created as class_course_created FROM students s inner JOIN class_courses c ON c.student_id = s.id '.( empty($filters) ?  '' :
                ' WHERE '.$conditions ).' ORDER BY c.created DESC LIMIT '.$paginator['limit'].' OFFSET '.$paginator['offset'];

        return $this->getDb()->fetch_all($query);
    }

    function getIncomesByStudentId($filters, $paginator) {
        $conditions = join(' AND ', $filters);

        $query = 'SELECT i.*, 
                     icc.class_course_id,
                     c.student_id as student_id,
                     s.nombre,
                     s.apellido,
                     s.dni,
                     icc.detail,
                     i.payment_place,
                     s.sub_category,
                     s.category
              FROM incomes i
              INNER JOIN incomes_class_courses icc ON icc.income_id = i.id
              INNER JOIN class_courses c ON c.id = icc.class_course_id
              INNER JOIN students s ON c.student_id = s.id'
            . (empty($filters) ? '' : ' WHERE ' . $conditions) .
            ' ORDER BY i.created DESC
              LIMIT ' . $paginator['limit'] . ' OFFSET ' . $paginator['offset'];

        return $this->getDb()->fetch_all($query);
    }




    function countClassesByStudentBySeason($filters){

        $conditions = join(' AND ',$filters);
        $query = 'SELECT SUM(classes_number) as total FROM class_courses '.( empty($filters) ?  '' : ' WHERE '.$conditions );
        $response= $this->getDb()->fetch_row($query);
        if($response['total']!=null){
            return $response['total'];
        }else{
            $response['total']=0;
            return $response['total'];
        }
    }
}
