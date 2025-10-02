<?php
/**
 * Created by PhpStorm.
 * User: leila
 * Date: 25/11/2019
 * Time: 12:20
 */

require_once 'BaseController.php';
require_once  __DIR__.'/../models/IncomeModel.php';
require_once __DIR__.'/../template/template.php';

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




    private function renderTemplateIncome($created, $nombre, $amount, $detail) {
        $date = $created;

        $html = "
    <html>
    <head>
        <meta charset='utf-8'>
        <title>Recibo</title>
        <style>
            body { font-family: Arial, sans-serif; margin: 40px; }
            h1 { text-align: center; }
            .content { margin-top: 50px; font-size: 18px; }
            .line { margin: 15px 0; }
            .bold { font-weight: bold; }
        </style>
    </head>
    <body>
        <h1>ESCUELA DE SURF LOA COLONIA 2026</h1>

        <div class='content'>
            <div class='line'><span class='bold'>Recibimos de:</span> {$nombre}</div>
            <div class='line'><span class='bold'>La cantidad de:</span> {$amount}</div>
            <div class='line'><span class='bold'>En concepto de:</span> {$detail}</div>
            <div class='line'><span class='bold'>Al:</span> {$date}</div>
        </div>
    </body>
    </html>
    ";

        return $html;
    }


    function generatePdfTest() {
        global $WKCONFIG;
        $WKCONFIG['PATH'] = '/usr/local/bin/wkhtmltopdf';

        // Evita el error qt_mac_loadMenuNib
        putenv('TMPDIR=/tmp');

       // $html = "<h1>Hola PDF</h1><p>Esto es un test desde PHP</p>";
        $html = $this->renderTemplateIncome($_GET['created'], $_GET['nombre'], $_GET['amount'], $_GET['detail']);
        $descriptorspec = array(
            0 => array('pipe', 'r'),
            1 => array('pipe', 'w'),
            2 => array('pipe', 'w'),
        );

        $process = proc_open($WKCONFIG['PATH'].' -q - -', $descriptorspec, $pipes);
        if (!is_resource($process)) {
            die(json_encode(['result'=>'error','message'=>'No se pudo iniciar wkhtmltopdf. Revisa permisos y path.']));
        }

        fwrite($pipes[0], $html);
        fclose($pipes[0]);

        $pdf = stream_get_contents($pipes[1]);
        $errors = stream_get_contents($pipes[2]);
        fclose($pipes[1]);
        fclose($pipes[2]);
        proc_close($process);

        if ($pdf) {
            echo json_encode(['result'=>'success','pdf'=>base64_encode($pdf)]);
        } else {
            echo json_encode(['result'=>'error','message'=>"PDF generation failed: $errors"]);
        }
    }

}

