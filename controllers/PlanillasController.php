<?php
/**
 * Created by PhpStorm.
 * User: leila
 * Date: 30/08/2021
 * Time: 20:19
 */

require_once 'BaseController.php';
require_once  __DIR__.'/../models/PlanillaModel.php';

class PlanillasController extends BaseController
{

    function __construct(){
        parent::__construct();
        $this->model = new PlanillaModel();
    }


    function getAll(){

        $this->returnSuccess(200, $this->model->findAllPlanillas($this->getFilters(), $this->getPaginator()));

    }


}

