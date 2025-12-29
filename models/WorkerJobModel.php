<?php

require_once 'BaseModel.php';

class WorkerJobModel extends BaseModel
{
    public function __construct()
    {
        parent::__construct();
        $this->tableName = 'worker_jobs';
    }

    function getWorkersToday($filters = array()) {
        $conditions = join(' AND ', $filters);
        // Consulta (ajustá los campos y joins si tu estructura difiere)
        $query = 'SELECT wj.job_id FROM worker_jobs wj WHERE 1 ' . (empty($filters) ? '' : ' AND ' . $conditions);

        return $this->getDb()->fetch_all($query);
    }

    function getUsersWithJobsByDay($paginator = [], $periodFilter = "Dia", $userId = null) {
        $db = $this->getDb();

        // Obtener fechas únicas según filtro
        if ($periodFilter === "Dia") {
            $datesQuery = "SELECT DISTINCT DATE(created) as day
                       FROM worker_jobs
                       " . ($userId ? "WHERE worker_id = " . intval($userId) : "") . "
                       ORDER BY day DESC
                       LIMIT " . intval($paginator['limit']) . " OFFSET " . intval($paginator['offset']);
        } else { // Mes
            $datesQuery = "SELECT DISTINCT DATE_FORMAT(created, '%Y-%m') as day
                       FROM worker_jobs
                       " . ($userId ? "WHERE worker_id = " . intval($userId) : "") . "
                       ORDER BY day DESC
                       LIMIT " . intval($paginator['limit']) . " OFFSET " . intval($paginator['offset']);
        }

        $dates = $db->fetch_all($datesQuery);
        $result = [];

        foreach ($dates as $d) {
            $day = $d['day'];

            // Traer todos los usuarios (o solo el userId) y sus trabajos
            $query = "
        SELECT 
            u.id AS user_id,
            u.name AS user_name,
            dj.id AS job_id,
            dj.name AS job_name,
            dj.category AS job_category,
            COUNT(wj.id) AS countPeriod
        FROM users u
        LEFT JOIN worker_jobs wj 
            ON wj.worker_id = u.id
            " . ($periodFilter === "Dia" ? "AND DATE(wj.created) = '" . addslashes($day) . "'" : "") . "
            " . ($periodFilter === "Mes" ? "AND DATE_FORMAT(wj.created, '%Y-%m') = '" . addslashes($day) . "'" : "") . "
        LEFT JOIN day_jobs dj ON dj.id = wj.job_id
        " . ($userId ? "WHERE u.id = " . intval($userId) : "") . "
        GROUP BY u.id, dj.id
        ORDER BY u.name ASC, dj.name ASC
        ";

            $rows = $db->fetch_all($query);

            $users = [];
            foreach ($rows as $row) {
                $userIdRow = $row['user_id'];
                if (!isset($users[$userIdRow])) {
                    $users[$userIdRow] = [
                        'id' => $userIdRow,
                        'name' => $row['user_name'],
                        'jobs' => []
                    ];
                }

                // Solo agregamos trabajos si hay job_id
                if ($row['job_id']) {
                    $users[$userIdRow]['jobs'][] = [
                        'id' => $row['job_id'],
                        'name' => $row['job_name'],
                        'category' => $row['job_category'],
                        'countPeriod' => intval($row['countPeriod'])
                    ];
                }
            }

            $result[] = [
                'day' => $day,
                'users' => array_values($users)
            ];
        }

        return $result;
    }

    function getUsersWithJobsByDayantultimofnciona($paginator = [], $periodFilter = "Dia") {
        $db = $this->getDb();

        // Obtener fechas únicas según filtro
        if ($periodFilter === "Dia") {
            //$datesQuery = "SELECT DISTINCT DATE(created) as day
            $datesQuery = "SELECT DISTINCT date as day
                       FROM worker_jobs
                       ORDER BY day DESC
                       LIMIT " . intval($paginator['limit']) . " OFFSET " . intval($paginator['offset']);
        } else { // Mes
            $datesQuery = "SELECT DISTINCT DATE_FORMAT(date, '%Y-%m') as day
                       FROM worker_jobs
                       ORDER BY day DESC
                       LIMIT " . intval($paginator['limit']) . " OFFSET " . intval($paginator['offset']);
        }

        $dates = $db->fetch_all($datesQuery);
        $result = [];

        foreach ($dates as $d) {
            $day = $d['day'];

            // Traer todos los usuarios con sus trabajos y conteos en una sola consulta
            $query = "
            SELECT 
                u.id AS user_id,
                u.name AS user_name,
                dj.id AS job_id,
                dj.name AS job_name,
                dj.category AS job_category,
                COUNT(wj.id) AS countPeriod
            FROM users u
            LEFT JOIN worker_jobs wj 
                ON wj.worker_id = u.id
                " . ($periodFilter === "Dia" ? "AND wj.date = '" . addslashes($day) . "'" : "") . "
                " . ($periodFilter === "Mes" ? "AND DATE_FORMAT(wj.date, '%Y-%m') = '" . addslashes($day) . "'" : "") . "
            LEFT JOIN day_jobs dj ON dj.id = wj.job_id
            GROUP BY u.id, dj.id
            ORDER BY u.name ASC, dj.name ASC
        ";

            $rows = $db->fetch_all($query);

            $users = [];
            foreach ($rows as $row) {
                $userId = $row['user_id'];
                if (!isset($users[$userId])) {
                    $users[$userId] = [
                        'id' => $userId,
                        'name' => $row['user_name'],
                        'jobs' => []
                    ];
                }

                // Solo agregamos trabajos que tengan count > 0
                if ($row['job_id'] && intval($row['countPeriod']) > 0) {
                    $users[$userId]['jobs'][] = [
                        'id' => $row['job_id'],
                        'name' => $row['job_name'],
                        'category' => $row['job_category'],
                        'countPeriod' => intval($row['countPeriod'])
                    ];
                }
            }

            $result[] = [
                'day' => $day,
                'users' => array_values($users)
            ];
        }

        return $result;
    }

    function getUsersWithJobsByDayantultimofncionaANTFUNCIONA($paginator = [], $periodFilter = "Dia") {
        $db = $this->getDb();

        // Obtener fechas únicas según filtro
        if ($periodFilter === "Dia") {
            $datesQuery = "SELECT DISTINCT DATE(created) as day
                       FROM worker_jobs
                       ORDER BY day DESC
                       LIMIT " . intval($paginator['limit']) . " OFFSET " . intval($paginator['offset']);
        } else { // Mes
            $datesQuery = "SELECT DISTINCT DATE_FORMAT(created, '%Y-%m') as day
                       FROM worker_jobs
                       ORDER BY day DESC
                       LIMIT " . intval($paginator['limit']) . " OFFSET " . intval($paginator['offset']);
        }

        $dates = $db->fetch_all($datesQuery);
        $result = [];

        foreach ($dates as $d) {
            $day = $d['day'];

            // Traer todos los usuarios con sus trabajos y conteos en una sola consulta
            $query = "
            SELECT 
                u.id AS user_id,
                u.name AS user_name,
                dj.id AS job_id,
                dj.name AS job_name,
                dj.category AS job_category,
                COUNT(wj.id) AS countPeriod
            FROM users u
            LEFT JOIN worker_jobs wj 
                ON wj.worker_id = u.id
                " . ($periodFilter === "Dia" ? "AND DATE(wj.created) = '" . addslashes($day) . "'" : "") . "
                " . ($periodFilter === "Mes" ? "AND DATE_FORMAT(wj.created, '%Y-%m') = '" . addslashes($day) . "'" : "") . "
            LEFT JOIN day_jobs dj ON dj.id = wj.job_id
            GROUP BY u.id, dj.id
            ORDER BY u.name ASC, dj.name ASC
        ";

            $rows = $db->fetch_all($query);

            $users = [];
            foreach ($rows as $row) {
                $userId = $row['user_id'];
                if (!isset($users[$userId])) {
                    $users[$userId] = [
                        'id' => $userId,
                        'name' => $row['user_name'],
                        'jobs' => []
                    ];
                }

                // Solo agregamos trabajos que tengan count > 0
                if ($row['job_id'] && intval($row['countPeriod']) > 0) {
                    $users[$userId]['jobs'][] = [
                        'id' => $row['job_id'],
                        'name' => $row['job_name'],
                        'category' => $row['job_category'],
                        'countPeriod' => intval($row['countPeriod'])
                    ];
                }
            }

            $result[] = [
                'day' => $day,
                'users' => array_values($users)
            ];
        }

        return $result;
    }

    function getUsersWithJobsByDayantfunciona($paginator = [], $periodFilter = "Dia") {
        $db = $this->getDb();

        // Obtener fechas únicas según filtro
        if ($periodFilter === "Dia") {
            $datesQuery = "SELECT DISTINCT DATE(created) as day
                       FROM worker_jobs
                       ORDER BY day DESC
                       LIMIT " . intval($paginator['limit']) . " OFFSET " . intval($paginator['offset']);
        } else { // Mes
            $datesQuery = "SELECT DISTINCT DATE_FORMAT(created, '%Y-%m') as day
                       FROM worker_jobs
                       ORDER BY day DESC
                       LIMIT " . intval($paginator['limit']) . " OFFSET " . intval($paginator['offset']);
        }

        $dates = $db->fetch_all($datesQuery);
        $result = [];

        foreach ($dates as $d) {
            $day = $d['day'];

            // Traer todos los usuarios
            $usersQuery = "SELECT id AS user_id, name AS user_name FROM users ORDER BY name ASC";
            $allUsers = $db->fetch_all($usersQuery);

            $users = [];
            foreach ($allUsers as $user) {
                $userId = $user['user_id'];

                // Traer trabajos y conteo para ese usuario según filtro
                $jobsQuery = "
                SELECT dj.id AS job_id, dj.name AS job_name, dj.category AS job_category, COUNT(*) AS countPeriod
                FROM worker_jobs wj
                INNER JOIN day_jobs dj ON dj.id = wj.job_id
                WHERE wj.worker_id = {$userId}
                  " . ($periodFilter === "Dia" ? "AND DATE(wj.created) = '" . addslashes($day) . "'" : "") . "
                  " . ($periodFilter === "Mes" ? "AND DATE_FORMAT(wj.created, '%Y-%m') = '" . addslashes($day) . "'" : "") . "
                GROUP BY dj.id
            ";
                $jobsRows = $db->fetch_all($jobsQuery);

                $jobs = [];
                foreach ($jobsRows as $job) {
                    $jobs[] = [
                        'id' => $job['job_id'],
                        'name' => $job['job_name'],
                        'category' => $job['job_category'],
                        'countPeriod' => intval($job['countPeriod'])
                    ];
                }

                $users[$userId] = [
                    'id' => $userId,
                    'name' => $user['user_name'],
                    'jobs' => $jobs
                ];
            }

            $result[] = [
                'day' => $day,
                'users' => array_values($users)
            ];
        }

        return $result;
    }







    function getUsersWithJobsByDayANT($paginator = array()) {
        $db = $this->getDb();

        // Obtenemos fechas distintas con paginación
        $datesQuery = "SELECT DISTINCT DATE(date) as day FROM worker_jobs ORDER BY day DESC 
                   LIMIT ".$paginator['limit']." OFFSET ".$paginator['offset'];
        $dates = $db->fetch_all($datesQuery);

        $result = [];

        foreach ($dates as $d) {
            $day = $d['day'];

            // Traer usuarios y sus trabajos para esa fecha
            $query = "
            SELECT 
                u.id AS user_id,
                u.name AS user_name,
                dj.id AS job_id,
                dj.name AS job_name,
                dj.category AS job_category
            FROM users u 
            LEFT JOIN worker_jobs wj 
                ON wj.worker_id = u.id AND DATE(wj.created) = '" . addslashes($day) . "'
            LEFT JOIN day_jobs dj 
                ON dj.id = wj.job_id
            ORDER BY u.name ASC, dj.name ASC
        ";

            $rows = $db->fetch_all($query);

            // Agrupar trabajos por usuario
            $users = [];
            foreach ($rows as $row) {
                $userId = $row['user_id'];
                if (!isset($users[$userId])) {
                    $users[$userId] = [
                        'id' => $userId,
                        'name' => $row['user_name'],
                        'jobs' => []
                    ];
                }

                if ($row['job_id']) {
                    $users[$userId]['jobs'][] = [
                        'id' => $row['job_id'],
                        'name' => $row['job_name'],
                        'category' => $row['job_category']
                    ];
                }
            }

            $result[] = [
                'day' => $day,
                'users' => array_values($users)
            ];
        }

        return $result;
    }

    /**
     * Trae todos los usuarios con sus trabajos asignados en una fecha
     */
    function getUsersWithJobs($date = null, $paginator=array()) {
        $db = $this->getDb();
        $date = $date ?: date('Y-m-d');

        // Consulta única con JOIN entre users, worker_jobs y day_jobs
        $query = "
        SELECT 
            u.id AS user_id,
            u.name AS user_name,
            dj.id AS job_id,
            dj.name AS job_name,
            dj.category AS job_category
        FROM users u LEFT JOIN worker_jobs wj 
            ON wj.worker_id = u.id AND DATE(wj.created) = '" . addslashes($date) . "'
        LEFT JOIN day_jobs dj ON dj.id = wj.job_id ORDER BY u.name ASC, dj.name ASC LIMIT ".$paginator['limit'].' OFFSET '.$paginator['offset'];

        $rows = $db->fetch_all($query);
        $users = array();

        // Agrupar trabajos por usuario
        foreach ($rows as $row) {
            $userId = $row['user_id'];
            if (!isset($users[$userId])) {
                $users[$userId] = array(
                    'id' => $userId,
                    'name' => $row['user_name'],
                    'jobs' => array()
                );
            }

            if ($row['job_id']) { // solo si tiene trabajo
                $users[$userId]['jobs'][] = array(
                    'id' => $row['job_id'],
                    'name' => $row['job_name'],
                    'category' => $row['job_category']
                );
            }
        }

        // Convertir a array indexado
        return array_values($users);
    }



}