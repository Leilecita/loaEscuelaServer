<?php

$data = json_decode(file_get_contents('php://input'), true);
file_put_contents(
    __DIR__ . '/push_debug.log',
    date('Y-m-d H:i:s') . ' ' . json_encode($data) . PHP_EOL,
    FILE_APPEND
);
echo json_encode(['ok' => true]);
