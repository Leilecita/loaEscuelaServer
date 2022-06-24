<?php
/**
 * Created by PhpStorm.
 * User: leila
 * Date: 30/08/2021
 * Time: 20:23
 */

require_once 'BaseController.php';
require_once  __DIR__.'/../models/PlanillaAlumnoModel.php';

class PlanillasAlumnosController extends BaseController
{

    function __construct(){
        parent::__construct();
        $this->model = new PlanillaAlumnoModel();
    }


    function checkRepeateStudent($student_id, $planilla_id){

        $filters= array();

        $filters[] = 'alumno_id = "'.$student_id.'"';
        $filters[] = 'planilla_id = "'.$planilla_id.'"';

        $student = $this->model->find($filters);
        return $student;
    }

    function post(){

        $this->beforeMethod();
        $data = (array)json_decode(file_get_contents("php://input"));

        unset($data['id']);

        $student = $this->checkRepeateStudent($data['alumno_id'], $data['planilla_id']);

        if($student){

            $this->returnSuccess(201,$student);

        }else{

            $res = $this->model->save($data);
            if($res<0){
                $this->returnError(404,null);
            }else{
                $inserted = $this->model->findById($res);
                $this->returnSuccess(201,$inserted);
            }
        }


    }

}