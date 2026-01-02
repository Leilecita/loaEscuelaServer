<?php

require_once 'BaseController.php';
require_once  __DIR__.'/../models/WorkerJobModel.php';

class WorkerJobsController extends BaseController
{
    function __construct(){
        parent::__construct();
        $this->model = new WorkerJobModel();
    }

    function getAll(){
        $this->returnSuccess(200,$this->getModel()->findAllAll());
    }
   /* function post() {
        $this->beforeMethod();

        $rawInput = file_get_contents("php://input");
        $data = json_decode($rawInput, true);

        if (empty($data)) {
            $this->returnError(400, "No se recibieron datos válidos");
            return;
        }

        $model = $this->getModel();

        // Si el input es un array de arrays (varios registros)
        if (isset($data[0]) && is_array($data[0])) {
            $insertedItems = [];

            foreach ($data as $item) {
                unset($item['id']); // por si lo trae
                $res = $model->save($item);

                if ($res < 0) {
                    $this->returnError(500, "Error al insertar un registro");
                    return;
                }

                $inserted = $model->findById($res);
                $insertedItems[] = $inserted;
            }

            $this->returnSuccess(201, $insertedItems);
            return;
        }

        // Si el input es un solo objeto (caso anterior)
        unset($data['id']);
        $res = $model->save($data);

        if ($res < 0) {
            $this->returnError(404, null);
        } else {
            $inserted = $model->findById($res);
            $this->returnSuccess(201, $inserted);
        }
    }*/

    function post() {
        $this->beforeMethod();

        $rawInput = file_get_contents("php://input");
        $data = json_decode($rawInput, true);

        if (empty($data)) {
            $this->returnError(400, "No se recibieron datos válidos");
            return;
        }

        $model = $this->getModel();

        // Aseguramos que $data siempre sea un array de arrays
        if (!isset($data[0]) || !is_array($data[0])) {
            $data = [$data]; // si es un solo objeto, lo convertimos en array de 1 elemento
        }

        // Tomamos worker_id y date del primer elemento
        $worker_id = isset($data[0]['worker_id']) ? intval($data[0]['worker_id']) : null;
        if (!$worker_id) {
            $this->returnError(400, "Falta worker_id en los datos enviados");
            return;
        }

        $job_date = isset($data[0]['date']) ? addslashes($data[0]['date']) : date('Y-m-d');

        // -----------------------
        // BORRAR registros previos de ese usuario y fecha
        // -----------------------
        $deleteQuery = "DELETE FROM worker_jobs WHERE worker_id = $worker_id AND date = '$job_date'";
        $model->getDb()->query($deleteQuery);

        // -----------------------
        // INSERTAR todos los registros enviados
        // -----------------------
        $insertedItems = [];
        foreach ($data as $item) {
            unset($item['id']); // eliminamos id por seguridad
            if (!isset($item['date'])) {
                $item['date'] = $job_date;
            }
            $res = $model->save($item);
            if ($res < 0) {
                $this->returnError(500, "Error al insertar un registro");
                return;
            }
            $insertedItems[] = $model->findById($res);
        }

        $this->returnSuccess(201, $insertedItems);
    }

    function getByWorkerAndDate() {
        $worker_id = isset($_GET['worker_id']) ? $_GET['worker_id'] : null;
        $date = isset($_GET['date']) ? $_GET['date'] : date('Y-m-d');

        if (!$worker_id) {
            $this->returnError(400, "Falta worker_id");
            return;
        }

        $filters = array();
        $filters[] = "wj.worker_id = " . intval($worker_id);
        $filters[] = "DATE(wj.date) = '" . addslashes($date) . "'";

        $rows = $this->model->getWorkersToday($filters);

        if (!$rows) {
            $rows = array();
        }

        $job_ids = array();
        foreach ($rows as $r) {
            if (isset($r['job_id'])) {
                $job_ids[] = (int)$r['job_id'];
            }
        }

        $this->returnSuccess(200, $job_ids);
    }

    function deleteTodayJobs() {
        $this->beforeMethod();

        $rawInput = file_get_contents("php://input");
        $data = json_decode($rawInput, true);

        // Puede venir por POST (JSON) o por parámetro GET
        $worker_id = null;
        if (isset($data['worker_id'])) {
            $worker_id = intval($data['worker_id']);
        } elseif (isset($_GET['worker_id'])) {
            $worker_id = intval($_GET['worker_id']);
        }

        if (!$worker_id) {
            $this->returnError(400, "Falta worker_id");
            return;
        }

        $model = $this->getModel();
        $today = date('Y-m-d');

        $deleteQuery = "DELETE FROM worker_jobs 
                    WHERE worker_id = " . intval($worker_id) . " 
                    AND DATE(created) = '" . addslashes($today) . "'";

        $model->getDb()->query($deleteQuery);

        $this->returnSuccess(200, array("message" => "Trabajos del día eliminados correctamente"));
    }

    function getUsersWithJobsByDay() {
        $model = $this->getModel();

        try {
            $paginator = $this->getPaginator();
            $periodFilter = isset($_GET['periodFilter']) ? $_GET['periodFilter'] : "Dia";

            // Nuevo parámetro opcional user_id
            $userId = isset($_GET['user_id']) ? intval($_GET['user_id']) : null;

            $daysWithUsers = $model->getUsersWithJobsByDay($paginator, $periodFilter, $userId);

            $this->returnSuccess(200, $daysWithUsers);
        } catch (Exception $e) {
            $this->returnError(500, "Error al obtener usuarios con trabajos por día: " . $e->getMessage());
        }
    }


    function getUsersWithJobsByDaya() {
        $model = $this->getModel();

        try {
            $paginator = $this->getPaginator();
            $periodFilter = isset($_GET['periodFilter']) ? $_GET['periodFilter'] : "Dia";


            $daysWithUsers = $model->getUsersWithJobsByDay($paginator, $periodFilter);

            $this->returnSuccess(200, $daysWithUsers);
        } catch (Exception $e) {
            $this->returnError(500, "Error al obtener usuarios con trabajos por día: " . $e->getMessage());
        }
    }

    function getUsersWithJobs() {
        $model = $this->getModel();

        $date = isset($_GET['date']) ? $_GET['date'] : date('Y-m-d');

        try {
            $users = $model->getUsersWithJobs($date, $this->getPaginator());
            $this->returnSuccess(200, $users);
        } catch (Exception $e) {
            $this->returnError(500, "Error al obtener usuarios con trabajos: " . $e->getMessage());
        }
    }

}
