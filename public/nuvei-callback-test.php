<?php
// Permitir solicitudes desde cualquier origen (solo para pruebas)
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

// Manejar preflight OPTIONS
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Leer JSON crudo
$inputJSON = file_get_contents('php://input');
$input = json_decode($inputJSON, true);

// Validar si al menos llegó algo
if (!$input || !isset($input['transaction'])) {
    http_response_code(400);
    echo json_encode([
        "success" => false,
        "error" => "No se recibió una transacción válida",
        "debug_input" => $inputJSON
    ]);
    exit;
}

// Reenviar a webhook.site para depuración
$debug_url = "https://webhook.site/6810f4af-d15c-4caf-9b99-d95905ef73ce";
$ch = curl_init($debug_url);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, $inputJSON);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_exec($ch);
curl_close($ch);

// Confirmar éxito
echo json_encode([
    "success" => true,
    "transaction_id" => $input['transaction']['id'] ?? null,
    "status" => $input['transaction']['status'] ?? null,
    "dev_reference" => $input['transaction']['dev_reference'] ?? null
]);
