<?php
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(204);
    exit;
}

ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', '/var/log/apache2/error.log');
error_reporting(E_ALL);

// ---------- INPUT JSON ----------
$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['token']) || $data['token'] === '') {
    http_response_code(400);
    exit('Token requerido');
}

$token    = $data['token'];
$user_id  = $data['user_id'] ?? null;
$platform = $data['platform'] ?? null;

// ---------- DB ----------
include __DIR__ . '/../config/config.php';
global $DBCONFIG_WEB_ALUMNOS;

$db = mysqli_connect(
    $DBCONFIG_WEB_ALUMNOS['HOST'],
    $DBCONFIG_WEB_ALUMNOS['USERNAME'],
    $DBCONFIG_WEB_ALUMNOS['PASSWORD'],
    $DBCONFIG_WEB_ALUMNOS['DATABASE']
);

if (!$db) {
    error_log('ERROR DB PUSH: ' . mysqli_connect_error());
    http_response_code(500);
    exit('DB error');
}

// ---------- UPSERT TOKEN ----------
$sql = "
INSERT INTO push_tokens (user_id, token, platform, last_seen_at)
VALUES (?, ?, ?, NOW())
ON DUPLICATE KEY UPDATE
    user_id = VALUES(user_id),
    platform = VALUES(platform),
    last_seen_at = NOW(),
    updated_at = NOW()
";

$stmt = $db->prepare($sql);
$stmt->bind_param(
    "iss",
    $user_id,
    $token,
    $platform
);

$ok = $stmt->execute();

if (!$ok) {
    error_log('ERROR PUSH TOKEN INSERT: ' . $stmt->error);
    http_response_code(500);
    exit('Error guardando token');
}

echo json_encode(['status' => 'ok']);
