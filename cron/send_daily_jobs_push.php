<?php
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', '/var/log/apache2/error.log');
error_reporting(E_ALL);

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
    error_log('ERROR DB PUSH CRON: ' . mysqli_connect_error());
    exit;
}

// ---------- TRAER TOKENS ----------
$res = $db->query("SELECT token FROM push_tokens");

if (!$res || $res->num_rows === 0) {
    exit;
}

$messages = [];

while ($row = $res->fetch_assoc()) {
    $messages[] = [
        'to' => $row['token'],
        'sound' => 'default',
        'title' => 'LOA',
        'body' => 'Recordatorio: cargá los trabajos del día 📝',
        'data' => [
            'screen' => 'DailyJobsScreen'
        ]
    ];
}

// ---------- ENVÍO A EXPO ----------
$chunks = array_chunk($messages, 100);

foreach ($chunks as $chunk) {

    $ch = curl_init('https://exp.host/--/api/v2/push/send');

    curl_setopt_array($ch, [
        CURLOPT_POST => true,
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/json',
            'Accept: application/json'
        ],
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POSTFIELDS => json_encode($chunk),
    ]);

    $response = curl_exec($ch);
    curl_close($ch);

    file_put_contents(
        __DIR__ . '/push.log',
        date('Y-m-d H:i:s') . ' ' . $response . PHP_EOL,
        FILE_APPEND
    );
}
