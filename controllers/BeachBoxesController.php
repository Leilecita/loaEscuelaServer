<?php
/**
 * Created by PhpStorm.
 * User: leila
 * Date: 09/11/2022
 * Time: 14:33
 */
require_once 'BaseController.php';
require_once  __DIR__.'/../models/BeachBoxModel.php';
require_once  __DIR__.'/../models/OutcomeModel.php';
class BeachBoxesController  extends BaseController
{
    private $outcomes;
    function __construct(){
        parent::__construct();
        $this->model = new BeachBoxModel();
        $this->outcomes = new OutcomeModel();
    }


    function getBoxes(){

        $filters = $this->getFilters();
        if(isset($_GET['payment_place'])){
            $filters[] = 'payment_place = "'.$_GET['payment_place'].'"';
        }

        if(isset($_GET['category']) && $_GET['category'] != "Todas"){
            $filters[] = 'category = "'.$_GET['category'].'"';
        }

        $this->returnSuccess(200,$this->model->findAll($filters,$this->getPaginator()));

    }

    function getLastBox(){

        $totalAmount=array('total' => 0.0);
        if(isset($_GET['date']) && isset($_GET['dateTo'])){
           // $totalAmount = $this->extractions->amountByExtractionsDay($_GET['date'],$_GET['dateTo']);
        }


        $filters = $this->getFilters();
        if(isset($_GET['payment_place'])){
            $filters[] = 'payment_place = "'.$_GET['payment_place'].'"';
        }

        if(isset($_GET['category'])){
            $filters[] = 'category = "'.$_GET['category'].'"';
        }

        $boxes = $this->model->findAllBoxes($filters);

        $lastBox = array('rest_box' => 0);
        if($boxes){
            $lastBox = $boxes[0];
        }


        $resp=array('lastBox' => $lastBox, 'amountExtractions' => $totalAmount['total']);
        $this->returnSuccess(200,$resp);
    }


    function getDates($data){

        $parts = explode(" ", $data);
        $date=$parts[0]." 00:00:00";
        $next_date = date('Y-m-d', strtotime( $parts[0].' +1 day'));
        $dateTo=$next_date." 00:00:00";
        $result=array('date' => $date, 'dateTo' => $dateTo);
        return $result;
    }

    function getPaidAmountByDay(){

        $dates = $this->getDates($_GET['date']);

        $payment_place = $_GET['payment_place'];

        $filters_out = parent::getFilters();

        $filters_out[] = 'created >= "'.$dates['date'].'"';
        $filters_out[] = 'created < "'.$dates['dateTo'].'"';

        if($_GET['category'] == "escuela"){
            $filters_out[] = 'category = "' . $_GET['category'] . '"';
        }else{
            $filters_out[] = '(category like "%'."colonia".'%" || category like "%'."highschool".'%")';
        }

        $amount_outcomes_esc = $this->outcomes->sumAmountOutcomes($filters_out);

        $filters3 = parent::getFilters();

        $filters3[] = 'ic.created >= "'.$dates['date'].'"';
        $filters3[] = 'ic.created < "'.$dates['dateTo'].'"';

        if($_GET['category'] == "escuela"){
            $filters3[] = 'c.category = "' . $_GET['category'] . '"';
        }else{
            $filters3[] = '(c.category like "%'."colonia".'%" || c.category like "%'."highschool".'%")';
        }
        //$filters3[] = 'c.category = "' . $_GET['category'] . '"';
        $filters3[] = 'i.payment_place = "'.$payment_place.'"';

        $filters3e = $filters3;
        $filters3e[] = 'i.payment_method = "efectivo"';


        $tot_paid_amount_esc = $this->model->getPaidAmountByClassCourseByDay($filters3e);

        $filters3t = $filters3;
        $filters3t[] = 'i.payment_method = "tarjeta"';
        $tot_paid_amount_esc_tarj = $this->model->getPaidAmountByClassCourseByDay($filters3t);


        $reportBox = array('tot_ef' => $tot_paid_amount_esc, 'tot_tarjeta' => $tot_paid_amount_esc_tarj, 'amount_outcomes' => $amount_outcomes_esc);

        $this->returnSuccess(200, $reportBox);
    }

    function getPaidAmountByDay2(){

        $dates = $this->getDates($_GET['date']);

        $payment_place = $_GET['payment_place'];

        $filters_out = parent::getFilters();

        $filters_out[] = 'created >= "'.$dates['date'].'"';
        $filters_out[] = 'created < "'.$dates['dateTo'].'"';
        $filters_out[] = 'category = "' . $_GET['category'] . '"';

        $amount_outcomes_esc = $this->outcomes->sumAmountOutcomes($filters_out);

        $filters3 = parent::getFilters();

        $filters3[] = 'ic.created >= "'.$dates['date'].'"';
        $filters3[] = 'ic.created < "'.$dates['dateTo'].'"';


        $filters3[] = 'c.category = "' . "escuela" . '"';
        $filters3[] = 'i.payment_place = "'.$payment_place.'"';

        $filters3e = $filters3;
        $filters3e[] = 'i.payment_method = "efectivo"';



        $tot_paid_amount_esc = $this->model->getPaidAmountByClassCourseByDay($filters3e);

        $filters3t = $filters3;
        $filters3t[] = 'i.payment_method = "tarjeta"';
        $tot_paid_amount_esc_tarj = $this->model->getPaidAmountByClassCourseByDay($filters3t);

        $filters4 = parent::getFilters();

        $filters4[] = 'ic.created >= "'.$dates['date'].'"';
        $filters4[] = 'ic.created < "'.$dates['dateTo'].'"';

        $filters4[] = 'c.category = "' . "highschool" . '"';
        $filters4[] = 'i.payment_place = "'.$payment_place.'"';

        $filters4e = $filters4;
        $filters4e[] = 'i.payment_method = "efectivo"';


        $tot_paid_amount_high = $this->model->getPaidAmountByClassCourseByDay($filters4e);

        $filters4t = $filters4;
        $filters4t[] = 'i.payment_method = "tarjeta"';
        $tot_paid_amount_high_tarj = $this->model->getPaidAmountByClassCourseByDay($filters4t);

        $filters5 = parent::getFilters();

        $filters5[] = 'ic.created >= "'.$dates['date'].'"';
        $filters5[] = 'ic.created < "'.$dates['dateTo'].'"';

        $filters5[] = 'c.category = "' . "colonia" . '"';
        $filters5[] = 'i.payment_place = "'.$payment_place.'"';

        $filters5e = $filters5;
        $filters5e[] = 'i.payment_method = "efectivo"';

        $tot_paid_amount_col = $this->model->getPaidAmountByClassCourseByDay($filters5e);

        $filters5t = $filters5;
        $filters5t[] = 'i.payment_method = "tarjeta"';
        $tot_paid_amount_col_tarj = $this->model->getPaidAmountByClassCourseByDay($filters5t);

        $reportBox = array('tot_esc_ef' => $tot_paid_amount_esc, 'tot_esc_tarj' => $tot_paid_amount_esc_tarj, 'amount_outcomes' => $amount_outcomes_esc,
            'tot_col_ef' => $tot_paid_amount_col, 'tot_col_tarj' => $tot_paid_amount_col_tarj,
            'tot_high_ef' => $tot_paid_amount_high, 'tot_high_tarj' => $tot_paid_amount_high_tarj,
            );

        $this->returnSuccess(200, $reportBox);
    }
}
