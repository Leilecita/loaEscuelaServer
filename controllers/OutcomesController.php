<?php
/**
 * Created by PhpStorm.
 * User: leila
 * Date: 25/11/2019
 * Time: 12:20
 */

require_once 'BaseController.php';
require_once  __DIR__.'/../models/OutcomeModel.php';

class OutcomesController extends SecureBaseController
{

    function __construct(){
        parent::__construct();
        $this->model = new OutcomeModel();
    }


    function getAllOutcomes(){

       $report = $this->model->getOutcomes(array(),$this->getPaginator());

        $this->returnSuccess(200,$report);
    }



}

