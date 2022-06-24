<?php
/**
 * Created by PhpStorm.
 * User: leila
 * Date: 25/11/2019
 * Time: 12:23
 */

require_once 'BaseController.php';
require_once  __DIR__.'/../models/IncomeClassCourseModel.php';
require_once  __DIR__.'/../models/IncomeModel.php';
require_once  __DIR__.'/../models/StudentModel.php';
require_once  __DIR__.'/../models/ClassCourseModel.php';


class IncomesClassCoursesController extends BaseController
{
    private $incomes;
    private $courses;
    private $students;

    function __construct(){
        parent::__construct();
        $this->model = new IncomeClassCourseModel();
        $this->incomes = new IncomeModel();
        $this->courses = new ClassCourseModel();
        $this->students = new StudentModel();
    }

    public function getFilters()
    {
        $filters = parent::getFilters();
        if(isset($_GET['student_id'])){
            $filters[] = 'c.student_id = "'.$_GET['student_id'].'"';
        }
        return $filters;
    }





    function post()
    {
        $data = (array) json_decode(file_get_contents("php://input"));

        $newIncome= array('amount' => $data['amount'],'payment_method' => "efectivo");
        $res=$this->incomes->save($newIncome);

        if($res<0){
            $this->returnError(404,null);
        }else{
            $insertedIncome = $this->incomes->findById($res);

            $incomeClassCourse=array('income_id' => $insertedIncome['id'], 'class_course_id' => $data['class_course_id']);
            $resIncomeClassCourse= $this->model->save($incomeClassCourse);

            if($resIncomeClassCourse<0){

                $this->returnError(404,null);
            }else{

                //$this->createEventIncomeClassCourse($data,$insertedIncome['id']);

                $this->returnSuccess(201,$insertedIncome);
            }
        }
    }

  /*  function createEventIncomeClassCourse($data,$insertedIncome){

        $course = $this->courses->findById($data['class_course_id']);

        $student = $this->students->findById($course['student_id']);

        $total_amount= $this->model->getPaidAmountByClassCourseIncomes(array( 'class_course_id = "'.$course['id'].'"'));

        $descr="pago ".$total_amount." ".$course['amount'];

        $event=array('student_id'=> $student['id'] ,'description' => $descr, 'type' => "clase", 'datetime' => $data['datetime'],
            'class_course_id' => $course['id'], 'state'=> "" ,'amount' => $data['amount'], 'income_id' => $insertedIncome );

        $res=$this->events->save($event);

    }*/
}
