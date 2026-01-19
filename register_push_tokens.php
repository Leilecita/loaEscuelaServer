<?php

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(204);
    exit;
}

ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', '/var/log/apache2/error.log');
error_reporting(E_ALL);

include __DIR__ . '/config/config.php';
require __DIR__ . '/libs/dbhelper.php';

global $DBCONFIG;

// ---------- INPUT JSON ----------
$data = json_decode(file_get_contents('php://input'), true);

if (!$data) {
    http_response_code(400);
    error_log('PUSH TOKEN: JSON inválido');
    exit('JSON inválido');
}

if (!isset($data['token']) || $data['token'] === '') {
    http_response_code(400);
    exit('Token requerido');
}

$token    = $data['token'];
$user_id  = isset($data['user_id']) ? $data['user_id'] : null;
$platform = isset($data['platform']) ? $data['platform'] : null;

// ---------- DB ----------
$db = mysqli_connect(
    $DBCONFIG['HOST'],
    $DBCONFIG['USERNAME'],
    $DBCONFIG['PASSWORD'],
    $DBCONFIG['DATABASE']
);

if (!$db) {
    error_log('ERROR DB PUSH: ' . mysqli_connect_error());
    http_response_code(500);
    exit('DB error');
}

// ---------- INSERT / UPDATE ----------
$sql = "
INSERT INTO push_tokens (user_id, token, platform, last_seen_at)
VALUES (?, ?, ?, NOW())
ON DUPLICATE KEY UPDATE
    user_id = VALUES(user_id),
    platform = VALUES(platform),
    last_seen_at = NOW()
";

$stmt = $db->prepare($sql);
$stmt->bind_param('iss', $user_id, $token, $platform);

if (!$stmt->execute()) {
    error_log('ERROR PUSH TOKEN INSERT: ' . $stmt->error);
    http_response_code(500);
    exit('Error guardando token');
}

echo json_encode(['status' => 'ok']);
