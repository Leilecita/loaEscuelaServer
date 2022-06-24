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
