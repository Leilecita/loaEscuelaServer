<?php
/**
 * Created by PhpStorm.
 * User: leila
 * Date: 30/10/2019
 * Time: 10:25
 */

require_once 'BaseController.php';
require_once  __DIR__.'/../models/ClassCourseModel.php';
require_once  __DIR__.'/../models/IncomeClassCourseModel.php';
require_once  __DIR__.'/../models/IncomeModel.php';

class ClassCoursesController  extends BaseController
{
    private $incomesClassCourse;
    private $incomes;

    function __construct(){
        parent::__construct();
        $this->model = new ClassCourseModel();
        $this->incomesClassCourse = new IncomeClassCourseModel();
        $this->incomes = new IncomeModel();
    }


    function getActualDateTimeMoreSeconds(){
        $date = new DateTime("now", new DateTimeZone('America/Argentina/Buenos_Aires') );
        $date->modify('+ 2 second');
        return $date->format('Y-m-d H:i:s');
    }

    function getActualDateTime(){
        $date = new DateTime("now", new DateTimeZone('America/Argentina/Buenos_Aires') );
        return $date->format('Y-m-d H:i:s');
    }

    public function getFilters()
    {
        $filters = parent::getFilters();
        if(isset($_GET['student_id'])){
            $filters[] = 's.id = "'.$_GET['student_id'].'"';
        }
        if(isset($_GET['course_id'])){
            $filters[] = 'c.id = "'.$_GET['course_id'].'"';
        }
        return $filters;
    }

    function post(){
        $this->beforeMethod();
        $data = (array)json_decode(file_get_contents("php://input"));
        unset($data['id']);

        $amount_income = 0;

        $payment_method = $data['payment_method'];
        unset($data['payment_method']);

        $payment_place = $data['payment_place'];
        unset($data['payment_place']);

        if(empty($data['paid_amount'])) {
            unset($data['paid_amount']);
        }else{
            $amount_income = $data['paid_amount'];
            unset($data['paid_amount']);
        }

        $res = $this->model->save($data);
        if($res<0){
            $this->returnError(404,null);
        }else{

            $inserted = $this->model->findById($res);


            $this->checkAndCreateIncome($amount_income, $inserted['id'], $payment_method, $payment_place, $inserted['observation']);

            $this->returnSuccess(201,$inserted);

        }
    }

    function checkAndCreateIncome($amount_income,$class_course_id,$payment_method, $payment_place, $observation){


        if($amount_income != 0){

            // es nuevo para poder cmbiar de fecha
            $course = $this->model->findById($class_course_id);

           // $res = $this->createIncome($amount_income,$class_course_id,$payment_method,$this->getTimeMoreSeconds($course['created']));
            $res = $this->createIncome($amount_income, $class_course_id, $payment_method, $course['created'], $payment_place, $observation);

            //$this->createIncomeEvent($class_course_id,$amount_income,$res);
        }

        //esto se agrega para poder ir creando pagos desde la app
        // $this->createIncome(0,$class_course_id,"nuevo",$this->getActualDateTime());

       // $this->createIncome(0,$class_course_id,"nuevo",$course['created']);
    }

    function createIncome($amount_income,$class_course_id,$payment_method, $created, $payment_place, $observation){

        $newIncome = array('amount' => $amount_income,'payment_method' => $payment_method,'payment_place' => $payment_place,'created' => $created);
        $res = $this->incomes->save($newIncome);

        $insertedIncome = $this->incomes->findById($res);

        $incomeClassCourse = array('income_id' => $insertedIncome['id'], 'class_course_id' => $class_course_id, 'detail' => $observation, 'created' => $created);

        $this->incomesClassCourse->save($incomeClassCourse);

        //devuelvo esto para guardarme el id del income
        return $res;

    }


    function getCoursesByStudent(){

        $list_all= $this->model->joinStudentOnCourse($this->getFilters(),$this->getPaginator());
        $reportCourse=array();
        for ($j = 0; $j < count($list_all); ++$j) {

            $course = $this->model->findById($list_all[$j]['id']);

            $paidAmountByCourse= $this->incomesClassCourse->getPaidAmountByClassCourseIncomes(array( 'class_course_id = "'.$list_all[$j]['id'].'"'));

            $incomesByCourse = $this->incomesClassCourse->joinIncomesAndClassCourseIncomes(array( 'class_course_id = "'.$list_all[$j]['id'].'"'));

            $reportCourse[]=array('student_name' => $list_all[$j]['nombre'], 'classes_number' => $course['classes_number'],
                'class_course_id' => $course['id'], 'category' => $course['category'],'sub_category' => $course['sub_category'],'amount' => $course['amount'],
                'paid_amount' => $paidAmountByCourse,
                'list_incomes' => $incomesByCourse,'created' => $list_all[$j]['class_course_created']);
        }

        $this->returnSuccess(200,$reportCourse);
    }

}