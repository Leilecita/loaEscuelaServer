<?php
/**
 * Created by PhpStorm.
 * User: leila
 * Date: 22/12/2020
 * Time: 17:04
 */


require_once 'SecureBaseController.php';
require_once  __DIR__.'/../models/StudentModel.php';
require_once  __DIR__.'/../controllers/SeasonsController.php';
require_once  __DIR__.'/../models/PlanillaModel.php';
require_once  __DIR__.'/../models/PlanillaPresenteModel.php';

class StudentsController extends SecureBaseController
{

    private $seasonsController;
    private $planillas;
    private $planillas_presentes;

    function __construct(){
        parent::__construct();
        $this->seasonsController = new SeasonsController();
        $this->model = new StudentModel();
        $this->planillas = new PlanillaModel();
        $this->planillas_presentes = new PlanillaPresenteModel();
    }

        function checkExistStudent(){

        $dni = $_GET['dni'];
        $student = $this->model->findByDni($dni);

        $res = "false";

        if($student){
            $res = "true";
        }

        $r = array('val' => $res);

        $this->returnSuccess(200, $r);
    }


    //SI CREO UN ALUMNO ME FIJO SI EXISTIA, EN CASO DE QUE SI , LO UPDATEO
    function post()
    {
        $this->beforeMethod();
        $data = (array)json_decode(file_get_contents("php://input"));

        unset($data['id']);

        $dni = $data['dni'];
        $student = $this->model->findByDni($dni);

        if($student){

            $date = new DateTime("now", new DateTimeZone('America/Argentina/Buenos_aires') );
            $updated_date =   $date->format('Y-m-d H:i:s');
            $data['updated_date'] = $updated_date;

            $this->model->update($student['id'],$data);
            $updated = $this->getModel()->findById($student['id']);

            $this->returnSuccess(200,$updated);
        }else{
            $res = $this->model->save($data);
            $this->returnSuccess(201,$data);
        }
    }




    public function getFilters(){
        $filters = parent::getFilters(); // TODO: Change the autogenerated stub
        if(isset($_GET['query']) && !empty($_GET['query'])){
          //  $filters[] = 'nombre like "%'.$_GET['query'].'%"';

            $filters[] = '(nombre like "%'.$_GET['query'].'%" || dni like "%'.$_GET['query'].'%" || apellido like "%'.$_GET['query'].'%")';
        }

        if(isset($_GET['category'])) {
            if ($_GET['category'] != "todos") {
                $filters[] = 'category = "' . $_GET['category'] . '"';
            }
        }

        return $filters;
    }


    function getStudents(){

        if(isset($_GET['order']) && !empty($_GET['order'])){

            if($_GET['order'] == "nombre"){
                $order = $_GET['order']." ASC";
            }else{
                $order = $_GET['order']." DESC";
            }

        }else{
            $order = 'created DESC';
        }

        $this->returnSuccess(200, $this->model->findAllStudents($this->getFilters(),$this->getPaginator(), $order));
    }



    function getStudentsByAssistsPlanilla($localFilter,$filtersAssists,$onlyPresents){

        //viene categoria y subcat, se busca planilla

        // trae la ultima creada, si hay mas, ver eso, habria que enviar, anio y mes

        //hacer lo de las season

        $planilla = $this->planillas->getPlanillaByCategoriaAndSubCat($localFilter);

        $reportStudent = array();

        $planilla_id = -1;

        if($planilla){

            $planilla_id = $planilla['id'] ;
            $filtersAssists[] = 'pa.planilla_id = "' . $planilla['id'] . '"';

            $students = $this->model->getStudentsAssists($filtersAssists, $this->getPaginator());

            for ($k = 0; $k < count($students); ++$k) {

                //por alumo me traigo las clases pagas, tomadas y plata que debe
                $report_takenandpaid_classes = $this->seasonsController->getPresentsBySeason($students[$k]['alumno_id']);

                //buscar si tiene presente en la planilla en el dia seleccionado
                $present = $this->planillas_presentes->find(array('alumno_id = "' . $students[$k]['alumno_id'] . '"', 'fecha_presente = "'.$_GET['date']. '"',
                    'planilla_id = "'.$planilla['id']. '"' ) );

                $presente = "no";
                $planilla_presente_id = -1;
                $obs = "";

                if($present){
                    $presente = "si";
                    $planilla_presente_id = $present['id'];
                    $obs = $present['observacion'];
                }


                if($onlyPresents == "false"){

                    $reportStudent[] = array('student_id' => $students[$k]['student_id'] , 'dni' => $students[$k]['dni'], 'nombre' => $students[$k]['nombre'], 'apellido' => $students[$k]['apellido'], 'presente' => $presente,
                        'planilla_id' => $planilla['id'], 'planilla_presente_id' => $planilla_presente_id, 'taken_classes' => $report_takenandpaid_classes, 'color' => "",
                        'nombre_mama' => $students[$k]['nombre_mama'],
                        'nombre_papa' => $students[$k]['nombre_papa'],
                        'tel_papa' => $students[$k]['tel_papa'],
                        'tel_mama' => $students[$k]['tel_mama'],
                        'observacion' => $obs,
                        );
                }else{
                    if(strcmp($presente, "si") == 0 ){
                        $reportStudent[] = array('student_id' => $students[$k]['student_id'] , 'dni' => $students[$k]['dni'],'nombre' => $students[$k]['nombre'], 'apellido' => $students[$k]['apellido'], 'presente' => $presente,
                            'planilla_id' => $planilla['id'], 'planilla_presente_id' => $planilla_presente_id, 'taken_classes' => $report_takenandpaid_classes, 'color' => "",
                            'nombre_mama' => $students[$k]['nombre_mama'],
                            'nombre_papa' => $students[$k]['nombre_papa'],
                            'tel_papa' => $students[$k]['tel_papa'],
                            'tel_mama' => $students[$k]['tel_mama'],
                            'observacion' => $obs,);
                    }
                }
            }
        }

        $generalReport=array('planilla_id' => $planilla_id, 'list_rep' => $reportStudent);

        $this->returnSuccess(200,$generalReport);
    }

    function getStudentsByAssists(){

            $localFilter = $this->getLocalFilter();
            $filtersAssists = $this->getFilters();

            if($_GET['categoria'] != "Todo" && $_GET['subcategoria'] != "Todo"){


                $this->getStudentsByAssistsPlanilla($localFilter,$filtersAssists,$_GET['onlyPresents']);

            }else{
               // $this->getStudentsByAssistsGeneral($localFilter,$filtersAssists);

                $generalReport=array('planilla_id' => -1, 'list_rep' => array());
                $this->returnSuccess(200,$generalReport);
            }
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



    function getLocalFilter(){
        $localFilter = array();

        if(isset($_GET['categoria']) && $_GET['categoria'] != "Todo") {
            $localFilter[] = 'categoria = "' . $_GET['categoria'] . '"';
        }

        if(isset($_GET['subcategoria']) && $_GET['subcategoria'] != "Todo") {
            $localFilter[] = 'subcategoria = "' . $_GET['subcategoria'] . '"';
        }

        if(isset($_GET['date']) && $_GET['date'] != "") {
            $date = explode("-", $_GET['date']);
           // $year = $date[0];

            $localFilter[] = 'anio = "' . $this->getSeason($date[1],$date[0]) . '"';

        }

        return $localFilter;
    }


    //CANTIDAD DE PRESENTES y de alumnos en la planilla
    function getValues(){

        $localFilter = $this->getLocalFilter();

        $total_alumnos = 0;
        $total_presents = 0;

        if($_GET['categoria'] != "Todo" && $_GET['subcategoria'] != "Todo"){ //SI HAY UNA PLANILLA SELECCIONADA

            $planilla = $this->planillas->getPlanillaByCategoriaAndSubCat($localFilter);
            $total_alumnos = 0;

            if($planilla){
                $total_alumnos = $this->model->countStudents(array('pa.planilla_id = '.$planilla['id']));
            }

            $total_presents = $this->planillas_presentes->countPresentes(array('fecha_presente = "'.$_GET['date']. '"', 'planilla_id = "'.$planilla['id']. '"' ));

        }  //VARIAS PLANILLAS SELECCIONADA, POR EJEMPLO ESCUELA, TODOS, tiene que si o is haber selccionado categ y sub cat


        $res = array('tot_students' => $total_alumnos, 'tot_presents' => $total_presents);
        $this->returnSuccess(200,$res);
    }


    //CANTIDAD DE Almnos tambien puede serpor categoria
    function getAlumnosQuantity(){

        $total_alumnos = $this->model->countStudents($this->getLocalFilter());

        $res = array('tot_students' => $total_alumnos);
        $this->returnSuccess(200,$res);
    }


}

/* function getStudentsByAssistsGeneral($localFilter, $filtersAssists){

       $planillas = $this->planillas->getPlanillasByCategoriaAndSubCatALL($localFilter);

       for ($s = 0; $s < count($planillas); ++$s) {
           $filtersAssists[] = 'pa.planilla_id = "' . $planillas[$s]['id'] . '"';
          // error_log("planilla ". $planillas[$s]['id']);
       }

       $students = $this->model->getStudentsAssistsOR($filtersAssists, $this->getPaginator());

       $reportStudent = array();

       for ($k = 0; $k < count($students); ++$k) {
           //buscar si tiene presente en la planilla

           $present = $this->planillas_presentes->find(array('alumno_id = "' . $students[$k]['alumno_id'] . '"', 'fecha_presente = "'.$_GET['date']. '"' ));

           $presente = "no";
           $planilla_presente_id = -1;
           if($present){
               $presente = "si";
               $planilla_presente_id = $present['id'];
           }

           $reportStudent[] = array('student_id' => $students[$k]['student_id'] ,'nombre' => $students[$k]['nombre'], 'apellido' => $students[$k]['apellido'], 'presente' => $presente,
               'planilla_id' => $students[$k]['planilla_id'], 'planilla_presente_id' => $planilla_presente_id);

       }

       $generalReport=array('planilla_id' => -1, 'list_rep' => $reportStudent);


       $this->returnSuccess(200,$generalReport);
   }*/