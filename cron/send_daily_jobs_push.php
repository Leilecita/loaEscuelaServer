<?php
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', '/var/log/apache2/error.log');
error_reporting(E_ALL);

// ---------- DB ----------
include __DIR__ . '/../config/config.php';
global $DBCONFIG;

$db = mysqli_connect(
    $DBCONFIG['HOST'],
    $DBCONFIG['USERNAME'],
    $DBCONFIG['PASSWORD'],
    $DBCONFIG['DATABASE']
);


if (!$db) {
    error_log('ERROR DB PUSH CRON: ' . mysqli_connect_error());
    exit;
}

// ---------- TOKEN DE PRUEBA (PEGÁ ACÁ UNO REAL) ----------
$messages = [
    [
        'to' => 'ExponentPushToken[PEGÁ_ACÁ_TU_TOKEN_REAL]',
        'sound' => 'default',
        'title' => 'LOA TEST',
        'body' => 'Push de prueba 🚀',
        'data' => [
            'screen' => 'DailyJobsScreen'
        ]
    ]
];

// ---------- ENVÍO A EXPO ----------
$ch = curl_init('https://exp.host/--/api/v2/push/send');

curl_setopt_array($ch, [
    CURLOPT_POST => true,
    CURLOPT_HTTPHEADER => [
        'Content-Type: application/json',
        'Accept: application/json'
    ],
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POSTFIELDS => json_encode($messages),
]);

$response = curl_exec($ch);
curl_close($ch);

echo $response . PHP_EOL;