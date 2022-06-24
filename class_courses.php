<?php
/**
 * Created by PhpStorm.
 * User: leila
 * Date: 21/06/2021
 * Time: 12:33
 */
include 'controllers/ClassCoursesController.php';


$controller = new ClassCoursesController();

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