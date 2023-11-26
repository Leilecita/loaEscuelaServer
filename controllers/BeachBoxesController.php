<?php
/**
 * Created by PhpStorm.
 * User: leila
 * Date: 09/11/2022
 * Time: 14:33
 */
require_once 'BaseController.php';
require_once  __DIR__.'/../models/BeachBoxModel.php';
class BeachBoxesController  extends BaseController
{

    function __construct(){
        parent::__construct();
        $this->model = new BeachBoxModel();
    }


    function getBoxes(){

        $filters = $this->getFilters();
        if(isset($_GET['payment_place'])){
            $filters[] = 'payment_place = "'.$_GET['payment_place'].'"';
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

        error_log($payment_place);

        $filters3 = parent::getFilters();

        $filters3[] = 'ic.created >= "'.$dates['date'].'"';
        $filters3[] = 'ic.created < "'.$dates['dateTo'].'"';

        $filters3[] = 'c.category = "' . "escuela" . '"';
        $filters3[] = 'i.payment_place = "'.$payment_place.'"';



        $tot_paid_amount_esc = $this->model->getPaidAmountByClassCourseByDay($filters3);

        $filters4 = parent::getFilters();

        $filters4[] = 'ic.created >= "'.$dates['date'].'"';
        $filters4[] = 'ic.created < "'.$dates['dateTo'].'"';

        $filters4[] = 'c.category = "' . "highschool" . '"';
        $filters4[] = 'i.payment_place = "'.$payment_place.'"';

        $tot_paid_amount_high = $this->model->getPaidAmountByClassCourseByDay($filters4);

        $filters5 = parent::getFilters();

        $filters5[] = 'ic.created >= "'.$dates['date'].'"';
        $filters5[] = 'ic.created < "'.$dates['dateTo'].'"';

        $filters5[] = 'c.category = "' . "colonia" . '"';
        $filters5[] = 'i.payment_place = "'.$payment_place.'"';

        $tot_paid_amount_col = $this->model->getPaidAmountByClassCourseByDay($filters5);

        $reportBox = array('tot_esc_ef' => $tot_paid_amount_esc, 'tot_col_ef' => $tot_paid_amount_col, 'tot_high_ef' => $tot_paid_amount_high);

        $this->returnSuccess(200, $reportBox);
    }
}
