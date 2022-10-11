<?php
/**
 * Created by PhpStorm.
 * User: leila
 * Date: 25/11/2019
 * Time: 12:19
 */

include 'controllers/OutcomesController.php';


$controller = new OutcomesController();

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