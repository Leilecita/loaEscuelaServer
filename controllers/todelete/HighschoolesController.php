<?php
/**
 * Created by PhpStorm.
 * User: leila
 * Date: 21/06/2021
 * Time: 12:47
 */

class HighschoolesController  extends BaseController
{
    private $events;

    function __construct(){
        parent::__construct();
        $this->model = new HighschoolModel();
        $this->events = new EventModel();
    }


/*
    function createEventIncomeClassCourse($data,$insertedIncome){

        $course = $this->courses->findById($data['class_course_id']);

        $student = $this->students->findById($course['student_id']);

        $total_amount= $this->model->getPaidAmountByClassCourseIncomes(array( 'class_course_id = "'.$course['id'].'"'));

        $descr="pago ".$total_amount." ".$course['amount'];

        $event=array('student_id'=> $student['id'] ,'description' => $descr, 'type' => "clase", 'datetime' => $data['datetime'],
            'class_course_id' => $course['id'], 'state'=> "" ,'amount' => $data['amount'], 'income_id' => $insertedIncome );

        $res=$this->events->save($event);

    }
*/
}
