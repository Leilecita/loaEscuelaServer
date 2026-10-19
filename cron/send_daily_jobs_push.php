
<?php

// LOA - Recordatorios diarios de trabajos
//
// PRUEBAS:
// php send_daily_jobs_push.php --slot=18
// php send_daily_jobs_push.php --slot=20
//
// ENVIOS REALES:
// php send_daily_jobs_push.php --slot=18 --send
// php send_daily_jobs_push.php --slot=20 --send

if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit('Acceso no permitido');
}

ini_set('display_errors', 0);
ini_set('log_errors', 1);
error_reporting(E_ALL);

date_default_timezone_set('America/Argentina/Buenos_Aires');

require __DIR__ . '/../config/config.php';

global $DBCONFIG;

// ---------------------------------------
// PARAMETROS
// ---------------------------------------

$slot = null;
$send = false;

foreach ($argv as $arg) {
    if ($arg === '--slot=18') {
        $slot = 18;
    } elseif ($arg === '--slot=20') {
        $slot = 20;
    } elseif ($arg === '--send') {
        $send = true;
    }
}

if ($slot === null) {
    exit("Indica --slot=18 o --slot=20\n");
}

$logFile = __DIR__ . '/push.log';

function pushLog($text)
{
    global $logFile;

    $line = date('Y-m-d H:i:s') . ' ART ' . $text;

    echo $line . PHP_EOL;

    file_put_contents(
        $logFile,
        $line . PHP_EOL,
        FILE_APPEND | LOCK_EX
    );
}

// ---------------------------------------
// CONEXION MYSQL
// ---------------------------------------

$db = mysqli_connect(
    $DBCONFIG['HOST'],
    $DBCONFIG['USERNAME'],
    $DBCONFIG['PASSWORD'],
    $DBCONFIG['DATABASE']
);

if (!$db) {
    pushLog('ERROR: conexion MySQL');
    exit(1);
}

mysqli_set_charset($db, 'utf8mb4');

// Fecha actual en Argentina
$hoy = date('Y-m-d');

// ---------------------------------------
// USUARIOS SIN TRABAJOS CARGADOS
// ---------------------------------------

$sql = "
    SELECT DISTINCT
        pt.token,
        u.id AS user_id,
        u.name
    FROM push_tokens pt
    INNER JOIN users u
        ON u.id = pt.user_id
    WHERE pt.token IS NOT NULL
      AND (
          pt.token LIKE 'ExponentPushToken[%'
          OR pt.token LIKE 'ExpoPushToken[%'
      )
      AND NOT EXISTS (
          SELECT 1
          FROM worker_jobs wj
          WHERE wj.worker_id = u.id
            AND wj.date = ?
      )
    ORDER BY u.id
";

$stmt = $db->prepare($sql);

if (!$stmt) {
    pushLog('ERROR SQL: ' . $db->error);
    exit(1);
}

$stmt->bind_param('s', $hoy);

if (!$stmt->execute()) {
    pushLog('ERROR CONSULTA: ' . $stmt->error);
    exit(1);
}

$result = $stmt->get_result();

$messages = [];
$destinatarios = [];
$tokensEnviados = [];

while ($row = $result->fetch_assoc()) {

    $token = $row['token'];
    $userId = (int) $row['user_id'];

    // No repetir un mismo token
    if (isset($tokensEnviados[$token])) {
        continue;
    }

    $tokensEnviados[$token] = true;

    $nombre = trim($row['name'] ?? '');

    if ($nombre === '') {
        $nombre = 'equipo';
    } else {
        $nombre = ucfirst($nombre);
    }

    // -----------------------------------
    // MENSAJES PERSONALIZADOS
    // -----------------------------------

    if ($slot === 18) {

        $titulo = 'LOA · Trabajos del dia';

        $mensaje = "¡Hola {$nombre}! 💙 No te olvides de cargar los trabajos de hoy 📝";

    } else {

        $titulo = 'LOA · Recordatorio';

        $mensaje = "¡Hola {$nombre}! 🌊 Todavía no registraste tus trabajos de hoy. Acordate de cargarlos.";
    }

    $messages[] = [
        'to' => $token,
        'sound' => 'default',
        'title' => $titulo,
        'body' => $mensaje,
        'data' => [
            'screen' => 'DailyJobsScreen'
        ]
    ];

    $destinatarios[] = [
        'id' => $userId,
        'nombre' => $nombre,
        'mensaje' => $mensaje
    ];
}

$stmt->close();

$total = count($messages);

// ---------------------------------------
// MODO PRUEBA
// ---------------------------------------

if (!$send) {

    echo "MODO PRUEBA - No se envio nada.\n";
    echo "Fecha Argentina: {$hoy}\n";
    echo "Horario: {$slot}:00\n";
    echo "Dispositivos: {$total}\n\n";

    foreach ($destinatarios as $item) {

        echo "Usuario {$item['id']} - {$item['nombre']}\n";
        echo $item['mensaje'] . "\n\n";
    }

    mysqli_close($db);
    exit(0);
}

// ---------------------------------------
// ENVIO REAL A EXPO
// ---------------------------------------

pushLog(
    "Horario {$slot}:00 - Fecha {$hoy} - Dispositivos: {$total}"
);

if ($total === 0) {

    pushLog('No hay notificaciones pendientes');

    mysqli_close($db);
    exit(0);
}

$chunks = array_chunk($messages, 100);

$hayErrores = false;

foreach ($chunks as $chunk) {

    $ch = curl_init(
        'https://exp.host/--/api/v2/push/send'
    );

    curl_setopt_array($ch, [
        CURLOPT_POST => true,
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/json',
            'Accept: application/json'
        ],
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POSTFIELDS => json_encode(
            $chunk,
            JSON_UNESCAPED_UNICODE
        ),
        CURLOPT_CONNECTTIMEOUT => 10,
        CURLOPT_TIMEOUT => 30
    ]);

    $response = curl_exec($ch);

    $httpCode = curl_getinfo(
        $ch,
        CURLINFO_HTTP_CODE
    );

    $curlError = curl_error($ch);

    curl_close($ch);

    if ($response === false || $httpCode !== 200) {

        pushLog(
            "ERROR EXPO HTTP {$httpCode}: {$curlError} " .
            substr((string) $response, 0, 500)
        );

        $hayErrores = true;
        continue;
    }

    $json = json_decode($response, true);

    if (!is_array($json) || !isset($json['data'])) {

        pushLog('ERROR: respuesta Expo inesperada');

        $hayErrores = true;
        continue;
    }

    $tickets = $json['data'];

    if (isset($tickets['status'])) {
        $tickets = [$tickets];
    }

    $aceptados = 0;
    $rechazados = 0;

    foreach ($tickets as $ticket) {

        if (($ticket['status'] ?? '') === 'ok') {

            $aceptados++;

        } else {

            $rechazados++;
            $hayErrores = true;

            pushLog(
                'ERROR TICKET: ' .
                json_encode(
                    $ticket,
                    JSON_UNESCAPED_UNICODE
                )
            );
        }
    }

    pushLog(
        "Expo acepto {$aceptados} mensajes. " .
        "Rechazados: {$rechazados}"
    );
}

mysqli_close($db);

exit($hayErrores ? 1 : 0);
