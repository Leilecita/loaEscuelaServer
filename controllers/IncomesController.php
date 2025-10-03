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
        $dateObj = new DateTime($created);
        $date = $dateObj->format('d/m/Y');

        $html = "
    <html>
    <head>
        <meta charset='utf-8'>
        <title>Recibo</title>
        <style>
            body { 
                font-family: Arial, sans-serif; 
                margin: 20px; 
                font-size: 16px;
            }
            .container {
                border: 1px solid #000;
                padding: 20px;
            }
            .header {
                border: 1px solid #000;
                padding: 10px;
                margin-bottom: 30px;
            }
            .header-top {
                display: flex;
                justify-content: space-between;
                align-items: flex-start; /* título y logo arriba */
                margin-bottom: 5px;
            }
            .header .title {
                font-size: 22px;
                font-weight: bold;
                margin-left: 8px;
            }
            .header .subtitle {
                font-size: 16px;
                 margin-left: 8px;
                  margin-top: 8px;
            }
            .logo {
                width: 80px;
                height: auto;
                padding: 8px;
            }
            .content {
                margin-top: 20px;
                line-height: 2;
            }
            .label {
                font-style: italic;
                font-weight: bold;
                display: inline-block;
                width: 180px;
                font-size: 16px;
            }
            .value {
                flex: 1;        
                word-wrap: break-word;
                font-size: 18px;
            }
        </style>
    </head>
    <body>
        <div class='container'>
            <table class='header' style='width:100%; border-collapse:collapse;'>
                <tr>
                    <td style='text-align:left;''>
                        <div class='title' >ESCUELA DE SURF LOA</div>
                        <div class='subtitle' >COLONIA 2026</div>
                    </td>
                    <td style='text-align:right; vertical-align:middle;'>
                        <img src='https://www.loasurf.com.ar/img/logoloa.png' class='logo' />
                    </td>
                </tr>
            </table>
            
            <div class='content'>
                <div>
                    <span class='label'>Recibimos de</span> 
                    <span class='value value-nombre'>{$nombre}</span>
                </div>
                <div>
                    <span class='label'>la cantidad de</span> 
                    <span class='value value-nombre'>{$amount}</span>
                </div>
                <div>
                    <span class='label'>en concepto de</span> 
                    <span class='value '>{$detail}</span>
                </div>
                <div>
                    <span class='label'>al</span> 
                    <span class='value value-nombre'>{$date}</span>
                </div>
            </div>
        </div>
    </body>
    </html>
    ";

        return $html;
    }


    function generatePdfTest() {
        global $WKCONFIG;
        //$WKCONFIG['PATH'] = '/usr/local/bin/wkhtmltopdf';
      //  $WKCONFIG['PATH'] = '/usr/bin/wkhtmltopdf';

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

