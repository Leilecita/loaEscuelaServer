<?php
/**
 * Created by PhpStorm.
 * User: leila
 * Date: 31/05/2022
 * Time: 13:26
 */

include 'controllers/IncomesClassCoursesController.php';


$controller = new IncomesClassCoursesController();

$method = $_SERVER['REQUEST_METHOD'];


switch ($method) {
    case 'GET':
        $controller->get();
        break;
    case 'POST':
        $controller->post();
        break;
    case 'DELETE':
        $controller->delete();
        break;
    case 'PUT':
        $controller->put();
        break;
    default:
        $controller->returnError(400,'INVALID METHOD');
        break;
}