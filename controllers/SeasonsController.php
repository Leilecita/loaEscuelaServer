<?php
/**
 * Created by PhpStorm.
 * User: leila
 * Date: 09/06/2022
 * Time: 15:17
 */

require_once 'BaseController.php';
require_once  __DIR__.'/../models/SeasonModel.php';
require_once  __DIR__.'/../models/PlanillaPresenteModel.php';
require_once  __DIR__.'/../models/ClassCourseModel.php';
require_once  __DIR__.'/../models/IncomeClassCourseModel.php';

class SeasonsController extends BaseController
{

    private $incomes_class_course;
    private $planillas_presentes;
    private $class_courses;

    function __construct(){
        parent::__construct();
        $this->model = new SeasonModel();
        $this->planillas_presentes = new PlanillaPresenteModel();
        $this->class_courses = new ClassCourseModel();
        $this->incomes_class_course = new IncomeClassCourseModel();
    }


    function getResumInfoByStudent(){
        $student_id = $_GET['student_id'];

        $this->returnSuccess(200, $this->getPresentsBySeason($student_id));
    }

    //me dice la cantidad de clases tomadas y compradas por temporada

    function getPresentsBySeason($student_id){

        $seasons = $this->model->findAll($this->getFilters(), $this->getPaginator());

        $reportPresentsBySeason = array();

        for ($k = 0; $k < count($seasons); ++$k) {


            $filters = parent::getFilters();

            $filters[] = 'alumno_id = "' . $student_id . '"';
            $filters[] = 'fecha_presente >= "' .$seasons[$k]['since_date']. '"';
            $filters[] = 'fecha_presente < "' . $seasons[$k]['to_date'] . '"';

            $cant_presentes = $this->planillas_presentes->countPresentesByStudent($filters);

            $filters2 = parent::getFilters();

            $filters2[] = 'student_id = "' . $student_id . '"';
            $filters2[] = 'created >= "' .$seasons[$k]['since_date']. '"';
            $filters2[] = 'created < "' . $seasons[$k]['to_date'] . '"';

            $cant_buyed_classes = $this->class_courses->countClassesByStudentBySeason($filters2);

            $filters3 = parent::getFilters();

            $filters3[] = 'c.student_id = "' . $student_id . '"';
            $filters3[] = 'ic.created >= "' .$seasons[$k]['since_date']. '"';
            $filters3[] = 'ic.created < "' . $seasons[$k]['to_date'] . '"';

            $tot_paid_amount = $this->incomes_class_course->getPaidAmountByClassCourseIncomesBySeason($filters3);

            $tot_amount = $this->incomes_class_course->getAmountByClassCourseIncomesBySeason($filters2);


            $reportPresentsBySeason[] = array('name' => $seasons[$k]['name'], 'cant_presents' => $cant_presentes, 'cant_buyed_classes' => $cant_buyed_classes, 'tot_paid_amount' => $tot_paid_amount,
                'tot_amount' => $tot_amount);

        }

        return $reportPresentsBySeason;
    }


}

