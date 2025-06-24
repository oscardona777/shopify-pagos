<?php
// Guarda logs
file_put_contents("webhook_log.txt", file_get_contents("php://input") . PHP_EOL, FILE_APPEND);

// Procesamiento
$data = json_decode(file_get_contents("php://input"), true);

if (isset($data['type']) && $data['type'] === 'transaction') {
    $status = $data['transaction']['status'];
    $dev_reference = $data['transaction']['dev_reference'];
    // procesar estado de la transacción
    http_response_code(200);
    echo "OK";
} else {
    http_response_code(400);
    echo "Invalid";
}
