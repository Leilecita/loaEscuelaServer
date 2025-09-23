<?php
/**
 * Created by PhpStorm.
 * User: leila
 * Date: 30/08/2021
 * Time: 20:25
 */

require_once 'BaseController.php';
require_once 'SecureBaseController.php';
require_once  __DIR__.'/../models/PlanillaPresenteModel.php';
require_once  __DIR__.'/../models/PlanillaModel.php';
require_once  __DIR__.'/../models/IncomeModel.php';
require_once  __DIR__.'/../models/OutcomeModel.php';

class PlanillasPresentesController extends SecureBaseController
{

    private $planillas;
    private $incomes;
    private $outcomes;

    function __construct(){
        parent::__construct();
        $this->model = new PlanillaPresenteModel();
        $this->planillas = new PlanillaModel();
        $this->incomes = new IncomeModel();
        $this->outcomes = new OutcomeModel();
    }

    function post(){

        $data = (array)json_decode(file_get_contents("php://input"));
        $exist = $this->model->find(array('planilla_id = ' . $data['planilla_id'] , 'alumno_id = ' . $data['alumno_id'] , 'fecha_presente = "' . $data['fecha_presente'] . '"'));

        if($exist){
            $this->returnSuccess(200,$exist);
        }else{
            parent::post();
        }
    }



    function getDates($data){

        $parts = explode(" ", $data);
        $date=$parts[0]." 00:00:00";
        $next_date = date('Y-m-d', strtotime( $parts[0].' +1 day'));
        $dateTo=$next_date." 00:00:00";
        $result=array('date' => $date, 'dateTo' => $dateTo);
        return $result;
    }

    function getMonths($data) {
        $parts = explode(" ", $data);
        $first_day = date('Y-m-01 00:00:00', strtotime($parts[0]));
        $next_month = date('Y-m-01 00:00:00', strtotime($parts[0] . ' +1 month'));
        return [
            'date'   => $first_day,
            'dateTo' => $next_month
        ];
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

    function getPlanillasByYearSinPaginator($datePresent){

        $date = explode("-", $datePresent);

        $pl = $this->planillas->findAllAll(array('anio = "'.$this->getSeason($date[1],$date[0]).'"'));

        return $pl;
    }




    function getPresentsByStudent(){

       // $presentes = $this->model->findAll(array('alumno_id = "' . $_GET['id'] . '"'), $this->getPaginator());
        //$presentes = $this->model->findAllPresentsByStudent(array('alumno_id = "' . $_GET['id'] . '"'), $this->getPaginator());
        $presentes = $this->model->findAllPresentsByStudent(array('alumno_id = "' . $_GET['id'] . '"'));
        $report = array();

        for ($l = 0; $l < count($presentes); ++$l) {

            $planilla = $this->planillas->findById($presentes[$l]['planilla_id']);

            $report[] = array('planilla' => $planilla['subcategoria'], 'fecha_presente' => $presentes[$l]['fecha_presente']);
        }

        $this->returnSuccess(200,$report);
    }


    function getDayResumPresents(){
        if(isset($_GET['period']) && ($_GET['period'] == 'Mes')){
            $presents = $this->model->getPresentsGroupByMonth($this->getFilters(), $this->getPaginator());
        }else{
            $presents = $this->model->getPresentsGroupByDate($this->getFilters(), $this->getPaginator());
        }
        $reportItems = array();

        for ($l = 0; $l < count($presents); ++$l) {

            $list_planillas_by_year = $this->getPlanillasByYearSinPaginator($presents[$l]['fecha_presente']);

            if(isset($_GET['period']) && ($_GET['period'] === 'Mes')){
               $dates = $this->getMonths($presents[$l]['fecha_presente']);
            }else{
                $dates = $this->getDates($presents[$l]['fecha_presente']);
            }

            $baseFilters = [
                'i.created >= "' . $dates['date'] . '"',
                'i.created < "' . $dates['dateTo'] . '"'
            ];

            // Configuración de filtros adicionales por variable
            $filtersConfig = [
                'sum_tot_amount' => [],
                'sum_tot_amount_ef' => ['i.payment_method = "efectivo"'],
                'sum_tot_amount_transf' => ['i.payment_method = "transferencia"'],
                'sum_tot_amount_mp' => ['i.payment_method = "mp"'],

                'sum_tot_amount_ef_esc' => ['i.payment_method = "efectivo"', 'c.category = "Escuela"'],
                'sum_tot_amount_transf_esc' => ['i.payment_method = "transferencia"', 'c.category = "Escuela"'],
                'sum_tot_amount_mp_esc' => ['i.payment_method = "mp"', 'c.category = "Escuela"'],

                'sum_tot_amount_ef_col' => ['i.payment_method = "efectivo"', '(c.category = "colonia" OR c.category = "highschool")'],
                'sum_tot_amount_transf_col' => ['(i.payment_method = "mp" OR i.payment_method = "transferencia")', '(c.category = "colonia" OR c.category = "highschool")'],
                'sum_tot_amount_mp_col' => ['i.payment_method = "mp"', '(c.category = "colonia" OR c.category = "highschool")'],
            ];

            foreach ($filtersConfig as $varName => $additionalFilters) {
                // versión normal
                ${$varName} = $this->incomes->sumAmountIncomesByDate(array_merge($baseFilters, $additionalFilters));

                // versión con payment_place = playa
                $varNamePlaya = $varName . '_playa';
                ${$varNamePlaya} = $this->incomes->sumAmountIncomesByDate(array_merge($baseFilters, $additionalFilters, ['i.payment_place = "escuela"']));

                // versión con payment_place = negocio
                $varNameNegocio = $varName . '_negocio';
                ${$varNameNegocio} = $this->incomes->sumAmountIncomesByDate(
                    array_merge($baseFilters, $additionalFilters, ['i.payment_place = "negocio"'])
                );
            }


            $reportPlanilla = array();

            $tot_presents = 0;

            for ($j = 0; $j < count($list_planillas_by_year); ++$j) {

                $planilla_presentes = $this->model->countPresentes(array('fecha_presente >= "' . $dates['date'] . '"', 'fecha_presente < "' . $dates['dateTo'] . '"',
                    'planilla_id = "' . $list_planillas_by_year[$j]['id'] . '"'));

                $tot_presents = $tot_presents + $planilla_presentes;

                $reportPlanilla[] = array('nombre_planilla' => $list_planillas_by_year[$j]['subcategoria'], 'cant_presentes' => $planilla_presentes);
            }

            $reportItems[] = array(
                'day' => $presents[$l]['fecha_presente'],
                'tot_presents' => $tot_presents,
                'planillas' => $reportPlanilla,

                // --- Totales generales ---
                'tot_incomes' => $sum_tot_amount,
                'tot_incomes_ef' => $sum_tot_amount_ef,
                'tot_incomes_transf' => $sum_tot_amount_transf,
                'tot_incomes_mp' => $sum_tot_amount_mp, // 👈 agregado

                // --- Escuela ---
                'tot_incomes_ef_esc' => $sum_tot_amount_ef_esc,
                'tot_incomes_transf_esc' => $sum_tot_amount_transf_esc,
                'tot_incomes_mp_esc' => $sum_tot_amount_mp_esc, // 👈 agregado

                // --- Colonia / Highschool ---
                'tot_incomes_ef_col' => $sum_tot_amount_ef_col,
                'tot_incomes_transf_col' => $sum_tot_amount_transf_col,
                'tot_incomes_mp_col' => $sum_tot_amount_mp_col, // 👈 agregado

                // --- Playa ---
                'tot_incomes_playa' => $sum_tot_amount_playa,
                'tot_incomes_ef_playa' => $sum_tot_amount_ef_playa,
                'tot_incomes_transf_playa' => $sum_tot_amount_transf_playa,
                'tot_incomes_mp_playa' => $sum_tot_amount_mp_playa, // 👈 agregado

                'tot_incomes_ef_esc_playa' => $sum_tot_amount_ef_esc_playa,
                'tot_incomes_transf_esc_playa' => $sum_tot_amount_transf_esc_playa,
                'tot_incomes_mp_esc_playa' => $sum_tot_amount_mp_esc_playa, // 👈 agregado

                'tot_incomes_ef_col_playa' => $sum_tot_amount_ef_col_playa,
                'tot_incomes_transf_col_playa' => $sum_tot_amount_transf_col_playa,
                'tot_incomes_mp_col_playa' => $sum_tot_amount_mp_col_playa, // 👈 agregado

                // --- Negocio ---
                'tot_incomes_negocio' => $sum_tot_amount_negocio,
                'tot_incomes_ef_negocio' => $sum_tot_amount_ef_negocio,
                'tot_incomes_transf_negocio' => $sum_tot_amount_transf_negocio,
                'tot_incomes_mp_negocio' => $sum_tot_amount_mp_negocio, // 👈 agregado

                'tot_incomes_ef_esc_negocio' => $sum_tot_amount_ef_esc_negocio,
                'tot_incomes_transf_esc_negocio' => $sum_tot_amount_transf_esc_negocio,
                'tot_incomes_mp_esc_negocio' => $sum_tot_amount_mp_esc_negocio, // 👈 agregado

                'tot_incomes_ef_col_negocio' => $sum_tot_amount_ef_col_negocio,
                'tot_incomes_transf_col_negocio' => $sum_tot_amount_transf_col_negocio,
                'tot_incomes_mp_col_negocio' => $sum_tot_amount_mp_col_negocio, // 👈 agregado
            );


        }
        $this->returnSuccess(200, $reportItems);
    }

    function getDayResumPresents_ant(){
        if(isset($_GET['period']) && ($_GET['period'] == 'Mes')){
            $presents = $this->model->getPresentsGroupByMonth($this->getFilters(), $this->getPaginator());
        }else{
            $presents = $this->model->getPresentsGroupByDate($this->getFilters(), $this->getPaginator());
        }
        $reportItems = array();

        for ($l = 0; $l < count($presents); ++$l) {

            $list_planillas_by_year = $this->getPlanillasByYearSinPaginator($presents[$l]['fecha_presente']);

            if(isset($_GET['period']) && ($_GET['period'] === 'Mes')){
                $dates = $this->getMonths($presents[$l]['fecha_presente']);
            }else{
                $dates = $this->getDates($presents[$l]['fecha_presente']);

            }

            // total diario
            /*      $sum_tot_amount = $this->incomes->sumAmountIncomesByDate(array('i.created >= "' . $dates['date'] . '"', 'i.created < "' . $dates['dateTo'] . '"'));

                $sum_tot_amount_ef = $this->incomes->sumAmountIncomesByDate(array('i.created >= "' . $dates['date'] . '"', 'i.created < "' . $dates['dateTo'] . '"',
                    'i.payment_method = "efectivo"'));
                $sum_tot_amount_transf = $this->incomes->sumAmountIncomesByDate(array('i.created >= "' . $dates['date'] . '"', 'i.created < "' . $dates['dateTo'] . '"',
                    '(i.payment_method = "mp" OR i.payment_method = "transferencia")'));

                $sum_tot_amount_ef_esc = $this->incomes->sumAmountIncomesByDate(array('i.created >= "' . $dates['date'] . '"', 'i.created < "' . $dates['dateTo'] . '"',
                    'i.payment_method = "efectivo"','c.category = "Escuela"'));
                $sum_tot_amount_transf_esc = $this->incomes->sumAmountIncomesByDate(array('i.created >= "' . $dates['date'] . '"', 'i.created < "' . $dates['dateTo'] . '"',
                    '(i.payment_method = "mp" OR i.payment_method = "transferencia")'));

                $sum_tot_amount_ef_col = $this->incomes->sumAmountIncomesByDate(array('i.created >= "' . $dates['date'] . '"', 'i.created < "' . $dates['dateTo'] . '"',
                    'i.payment_method = "efectivo"','c.category = "Escuela"'));
                $sum_tot_amount_transf_col = $this->incomes->sumAmountIncomesByDate(array('i.created >= "' . $dates['date'] . '"', 'i.created < "' . $dates['dateTo'] . '"',
                    '(i.payment_method = "mp" OR i.payment_method = "transferencia")','(c.category = "colonia" OR c.category = "highschool")'));
    */
            $baseFilters = [
                'i.created >= "' . $dates['date'] . '"',
                'i.created < "' . $dates['dateTo'] . '"'
            ];

            // Configuración de filtros adicionales por variable
            $filtersConfig = [
                'sum_tot_amount' => [],
                'sum_tot_amount_ef' => ['i.payment_method = "efectivo"'],
                'sum_tot_amount_transf' => ['(i.payment_method = "mp" OR i.payment_method = "transferencia")'],
                'sum_tot_amount_ef_esc' => ['i.payment_method = "efectivo"', 'c.category = "Escuela"'],
                'sum_tot_amount_transf_esc' => ['(i.payment_method = "mp" OR i.payment_method = "transferencia")', 'c.category = "Escuela"'],
                'sum_tot_amount_ef_col' => ['i.payment_method = "efectivo"', '(c.category = "colonia" OR c.category = "highschool")'],
                'sum_tot_amount_transf_col' => ['(i.payment_method = "mp" OR i.payment_method = "transferencia")', '(c.category = "colonia" OR c.category = "highschool")'],
            ];

            foreach ($filtersConfig as $varName => $additionalFilters) {
                // versión normal
                ${$varName} = $this->incomes->sumAmountIncomesByDate(array_merge($baseFilters, $additionalFilters));

                // versión con payment_place = playa
                $varNamePlaya = $varName . '_playa';
                ${$varNamePlaya} = $this->incomes->sumAmountIncomesByDate(array_merge($baseFilters, $additionalFilters, ['i.payment_place = "escuela"']));

                // versión con payment_place = negocio
                $varNameNegocio = $varName . '_negocio';
                ${$varNameNegocio} = $this->incomes->sumAmountIncomesByDate(
                    array_merge($baseFilters, $additionalFilters, ['i.payment_place = "negocio"'])
                );
            }

            /*  $sum_tot_escuela = $this->incomes->sumAmountIncomesByDate(array('i.created >= "' . $dates['date'] . '"', 'i.created < "' . $dates['dateTo'] . '"',
                  'c.category = "Escuela"'));
              $sum_tot_escuela_ef = $this->incomes->sumAmountIncomesByDate(array('i.created >= "' . $dates['date'] . '"', 'i.created < "' . $dates['dateTo'] . '"',
                  'c.category = "Escuela"',  'i.payment_method = "efectivo"'));
              $sum_tot_escuela_tarj = $this->incomes->sumAmountIncomesByDate(array('i.created >= "' . $dates['date'] . '"', 'i.created < "' . $dates['dateTo'] . '"',
                  'c.category = "Escuela"','(i.payment_method = "mp" OR i.payment_method = "transferencia")'));


              $sum_tot_highschool = $this->incomes->sumAmountIncomesByDate(array('i.created >= "' . $dates['date'] . '"', 'i.created < "' . $dates['dateTo'] . '"',
                  'c.category = "Highschool"'));
              $sum_tot_highschool_ef = $this->incomes->sumAmountIncomesByDate(array('i.created >= "' . $dates['date'] . '"', 'i.created < "' . $dates['dateTo'] . '"',
                  'c.category = "Highschool"',  'i.payment_method = "efectivo"'));
              $sum_tot_highschool_tarj = $this->incomes->sumAmountIncomesByDate(array('i.created >= "' . $dates['date'] . '"', 'i.created < "' . $dates['dateTo'] . '"',
                  'c.category = "Highschool"','(i.payment_method = "mp" OR i.payment_method = "transferencia")'));

              $sum_tot_colonia = $this->incomes->sumAmountIncomesByDate(array('i.created >= "' . $dates['date'] . '"', 'i.created < "' . $dates['dateTo'] . '"',
                  'c.category = "Colonia"'));
              $sum_tot_colonia_ef = $this->incomes->sumAmountIncomesByDate(array('i.created >= "' . $dates['date'] . '"', 'i.created < "' . $dates['dateTo'] . '"',
                  'c.category = "Colonia"',  'i.payment_method = "efectivo"'));
              $sum_tot_colonia_tarj = $this->incomes->sumAmountIncomesByDate(array('i.created >= "' . $dates['date'] . '"', 'i.created < "' . $dates['dateTo'] . '"',
                  'c.category = "Colonia"','(i.payment_method = "mp" OR i.payment_method = "transferencia")'));*/
            ///
            ///
            ///

            //total negocio
            //  $sum_tot_amount_negocio = $this->incomes->sumAmountIncomesByDate(array('i.payment_place = "' .'negocio'. '"','i.created >= "' . $dates['date'] . '"', 'i.created < "' . $dates['dateTo'] . '"'));

            // $sum_tot_amount_negocio_ef = $this->incomes->sumAmountIncomesByDate(array('i.payment_place = "' .'negocio'. '"','i.created >= "' . $dates['date'] . '"', 'i.created < "' . $dates['dateTo'] . '"','i.payment_method = "efectivo"'));
            // $sum_tot_amount_negocio_tarj = $this->incomes->sumAmountIncomesByDate(array('i.payment_place = "' .'negocio'. '"','i.created >= "' . $dates['date'] . '"', 'i.created < "' . $dates['dateTo'] . '"','(i.payment_method = "mp" OR i.payment_method = "transferencia")'));
            //$sum_tot_amount_playa = $this->incomes->sumAmountIncomesByDate(array('i.payment_place = "' .'escuela'. '"','i.created >= "' . $dates['date'] . '"', 'i.created < "' . $dates['dateTo'] . '"'));



            $reportPlanilla = array();

            $tot_presents = 0;

            for ($j = 0; $j < count($list_planillas_by_year); ++$j) {

                $planilla_presentes = $this->model->countPresentes(array('fecha_presente >= "' . $dates['date'] . '"', 'fecha_presente < "' . $dates['dateTo'] . '"',
                    'planilla_id = "' . $list_planillas_by_year[$j]['id'] . '"'));

                $tot_presents = $tot_presents + $planilla_presentes;

                $reportPlanilla[] = array('nombre_planilla' => $list_planillas_by_year[$j]['subcategoria'], 'cant_presentes' => $planilla_presentes);
            }

            $reportItems[] = array('day' => $presents[$l]['fecha_presente'], 'tot_presents' => $tot_presents, 'planillas' => $reportPlanilla ,
                'tot_incomes' => $sum_tot_amount,
                'tot_incomes_ef' => $sum_tot_amount_ef,
                'tot_incomes_transf' => $sum_tot_amount_transf,
                'tot_incomes_ef_esc' => $sum_tot_amount_ef_esc,
                'tot_incomes_transf_esc' => $sum_tot_amount_transf_esc,
                'tot_incomes_ef_col' => $sum_tot_amount_ef_col,
                'tot_incomes_transf_col' => $sum_tot_amount_transf_col,


                'tot_incomes_playa' => $sum_tot_amount_playa,
                'tot_incomes_ef_playa' => $sum_tot_amount_ef_playa,
                'tot_incomes_transf_playa' => $sum_tot_amount_transf_playa,
                'tot_incomes_ef_esc_playa' => $sum_tot_amount_ef_esc_playa,
                'tot_incomes_transf_esc_playa' => $sum_tot_amount_transf_esc_playa,
                'tot_incomes_ef_col_playa' => $sum_tot_amount_ef_col_playa,
                'tot_incomes_transf_col_playa' => $sum_tot_amount_transf_col_playa,

                'tot_incomes_negocio' => $sum_tot_amount_negocio,
                'tot_incomes_ef_negocio' => $sum_tot_amount_ef_negocio,
                'tot_incomes_transf_negocio' => $sum_tot_amount_transf_negocio,
                'tot_incomes_ef_esc_negocio' => $sum_tot_amount_ef_esc_negocio,
                'tot_incomes_transf_esc_negocio' => $sum_tot_amount_transf_esc_negocio,
                'tot_incomes_ef_col_negocio' => $sum_tot_amount_ef_col_negocio,
                'tot_incomes_transf_col_negocio' => $sum_tot_amount_transf_col_negocio,

            );

        }
        $this->returnSuccess(200, $reportItems);
    }


}

