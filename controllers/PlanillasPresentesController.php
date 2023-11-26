<?php
/**
 * Created by PhpStorm.
 * User: leila
 * Date: 30/08/2021
 * Time: 20:25
 */

require_once 'BaseController.php';
require_once  __DIR__.'/../models/PlanillaPresenteModel.php';
require_once  __DIR__.'/../models/PlanillaModel.php';
require_once  __DIR__.'/../models/IncomeModel.php';

class PlanillasPresentesController extends BaseController
{

    private $planillas;
    private $incomes;

    function __construct(){
        parent::__construct();
        $this->model = new PlanillaPresenteModel();
        $this->planillas = new PlanillaModel();
        $this->incomes = new IncomeModel();
    }



    function getDates($data){

        $parts = explode(" ", $data);
        $date=$parts[0]." 00:00:00";
        $next_date = date('Y-m-d', strtotime( $parts[0].' +1 day'));
        $dateTo=$next_date." 00:00:00";
        $result=array('date' => $date, 'dateTo' => $dateTo);
        return $result;
    }

    function getYearFromPresentDate($date){
        $parts = explode("-", $date);
        return $parts[0];
    }

    function getSeason($month,$year){
        if($month > 8){
            $year1 = $year;
            $year2 = $year + 1;
        }else{
            $year1 = $year - 1;
            $year2 = $year;
        }
        return $year1."-".$year2;
    }

    function getPlanillasByYear($datePresent){

        $date = explode("-", $datePresent);

        $pl = $this->planillas->findAll(array('anio = "'.$this->getSeason($date[1],$date[0]).'"'), $this->getPaginator());

        return $pl;
    }



    function getPresentsByStudent(){

       // $presentes = $this->model->findAll(array('alumno_id = "' . $_GET['id'] . '"'), $this->getPaginator());
        $presentes = $this->model->findAllPresentsByStudent(array('alumno_id = "' . $_GET['id'] . '"'), $this->getPaginator());
        $report = array();

        for ($l = 0; $l < count($presentes); ++$l) {

            $planilla = $this->planillas->findById($presentes[$l]['planilla_id']);

            $report[] = array('planilla' => $planilla['subcategoria'], 'fecha_presente' => $presentes[$l]['fecha_presente']);
        }

        $this->returnSuccess(200,$report);
    }


    function getDayResumPresents(){

        $presents = $this->model->getPresentsGroupByDateSinPag($this->getFilters());
        $reportItems = array();

        for ($l = 0; $l < count($presents); ++$l) {

           // $list_planillas_by_year = $this->getPlanillasByYear($this->getYearFromPresentDate($presents[$l]['fecha_presente']));
            $list_planillas_by_year = $this->getPlanillasByYear($presents[$l]['fecha_presente']);

            $dates = $this->getDates($presents[$l]['fecha_presente']);

            $sum_tot_amount = $this->incomes->sumAmountIncomesByDate(array('c.created >= "' . $dates['date'] . '"', 'c.created < "' . $dates['dateTo'] . '"'));
            $sum_tot_escuela = $this->incomes->sumAmountIncomesByDate(array('c.created >= "' . $dates['date'] . '"', 'c.created < "' . $dates['dateTo'] . '"',
                'c.category = "Escuela"'));

            $sum_tot_highschool = $this->incomes->sumAmountIncomesByDate(array('c.created >= "' . $dates['date'] . '"', 'c.created < "' . $dates['dateTo'] . '"',
                'c.category = "Highschool"'));

            $sum_tot_colonia = $this->incomes->sumAmountIncomesByDate(array('c.created >= "' . $dates['date'] . '"', 'c.created < "' . $dates['dateTo'] . '"',
                'c.category = "Colonia"'));

            $reportPlanilla = array();

            $tot_presents = 0;

            for ($j = 0; $j < count($list_planillas_by_year); ++$j) {

                $planilla_presentes = $this->model->countPresentes(array('fecha_presente >= "' . $dates['date'] . '"', 'fecha_presente < "' . $dates['dateTo'] . '"',
                    'planilla_id = "' . $list_planillas_by_year[$j]['id'] . '"'));

                $tot_presents = $tot_presents + $planilla_presentes;

                $reportPlanilla[] = array('nombre_planilla' => $list_planillas_by_year[$j]['subcategoria'], 'cant_presentes' => $planilla_presentes);
            }

            $reportItems[] = array('day' => $presents[$l]['fecha_presente'], 'tot_presents' => $tot_presents,'planillas' => $reportPlanilla , 'tot_incomes' => $sum_tot_amount,
                'tot_incomes_escuela' => $sum_tot_escuela, 'tot_incomes_colonia' => $sum_tot_colonia, 'tot_incomes_highschool' => $sum_tot_highschool);

        }
        $this->returnSuccess(200, $reportItems);
    }

}

