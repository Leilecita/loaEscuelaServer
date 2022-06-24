<?php
/**
 * Created by PhpStorm.
 * User: leila
 * Date: 09/06/2022
 * Time: 15:17
 */

include 'controllers/SeasonsController.php';


$controller = new SeasonsController();

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