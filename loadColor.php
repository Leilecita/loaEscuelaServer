<?php

include __DIR__ . '/config/config.php';
require_once  __DIR__.'/models/StudentModel.php';


global $DBCONFIG;

function getActualTime(){
    $date = new DateTime("now", new DateTimeZone('America/Argentina/Buenos_Aires') );
    return $date->format('Y-m-d H:i:s');
}

// $this->db->connect('pdo', 'mysql', $DBCONFIG['HOST'], $DBCONFIG['USERNAME'], $DBCONFIG['PASSWORD'],$DBCONFIG['DATABASE'],$DBCONFIG['PORT']);

$db_host=$DBCONFIG['HOST'];
$db_user="root";
$db_password= $DBCONFIG['PASSWORD'];
$db_name=$DBCONFIG['DATABASE'];
$db_table_name="students";

$model = new StudentModel();

$db_connection = mysqli_connect($db_host, $db_user, $db_password);
mysqli_select_db($db_connection,$db_name );


if (!$db_connection) {
    die('No se ha podido conectar a la base de datos');
}

$color=array();
$color[]=array('#E57373');
$color[]=array('#4DD0E1');

$color[]=array('#64B5F6');
$color[]=array('#80CBC4');
$color[]=array('#80DEEA');

$color[]=array('#D4E157');
$color[]=array('#FF8A65');
$color[]=array('#E57373');
$color[]=array('#FFB74D');
$color[]=array('#F06292');
$color[]=array('#4FC3F7');
$color[]=array('#9575CD');

$color[]=array('#90A4AE');
$color[]=array('#FFD54F');
$color[]=array('#F9A825');
$color[]=array('#CE93D8');
$color[]=array('#FF8A65');
$color[]=array('#90CAF9');
$color[]=array('#4DB6AC');

$color[]=array('#64B5F6');
$color[]=array('#81C784');
$color[]=array('#FF8A65');
$color[]=array('#9FA8DA');
$color[]=array('#B39DDB');
$color[]=array('#4FC3F7');
$color[]=array('#4DB6AC');
$color[]=array('#BA68C8');
$color[]=array('#EF9A9A');



$students = $model->findAllAll(array());


global  $form;

if (count($students)>0)
{

    for ($j = 0; $j < count($students); ++$j) {

        $model->update($students[$j]['id'],array('color' => $color[ rand(0,23)]));
    }



}
mysqli_close($db_connection);

