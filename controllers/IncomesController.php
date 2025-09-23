<?php
/**
 * Created by PhpStorm.
 * User: leila
 * Date: 25/11/2019
 * Time: 12:20
 */

require_once 'BaseController.php';
require_once  __DIR__.'/../models/IncomeModel.php';

class IncomesController extends BaseController
{

    function __construct(){
        parent::__construct();
        $this->model = new IncomeModel();
    }


    function getAllIncomes(){

        $filters = $this->getFilters();

         $filters[] = 'icc.class_course_id = cc.id';
         $filters[] = 'cc.student_id = s.id';
         $filters[] = 'i.id=icc.income_id';


        if(isset($_GET['payment_place'])){
            $filters[] = 'i.payment_place = "'.$_GET['payment_place'].'"';
        }
        if(isset($_GET['payment_method'])){
            $filters[] = 'i.payment_method = "'.$_GET['payment_method'].'"';
        }

        if(isset($_GET['category'])){
            $filters[] = 'cc.category = "'.$_GET['category'].'"';
        }



        $report = $this->model->getAllIncomes($this->getPaginator(),$filters);

        $this->returnSuccess(200,$report);
    }



}

