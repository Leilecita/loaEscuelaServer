<?php

/**
 * Created by PhpStorm.
 * User: leila
 * Date: 09/06/2022
 * Time: 15:17
 */

require_once 'BaseController.php';
require_once  __DIR__.'/../models/ClassPriceModel.php';

class ClassesPriceController extends BaseController
{
    function __construct(){
        parent::__construct();
        $this->model = new ClassPriceModel();
    }

    function getAll(){
        $this->returnSuccess(200,$this->getModel()->findAllAll());
    }
}

