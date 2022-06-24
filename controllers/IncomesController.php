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

        $report = $this->model->getAllIncomes($this->getPaginator());


        $this->returnSuccess(200,$report);
    }



}

