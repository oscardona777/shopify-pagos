<?php
// Siempre incluir encabezados CORS
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

// 1. Manejar petición preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// 2. Leer y decodificar el JSON
$inputJSON = file_get_contents('php://input');
$input = json_decode($inputJSON, true);

// 3. Validar datos esperados para Checkout Modal (no exige stoken)
if (!isset($input['transaction'])) {
    http_response_code(400);
    echo json_encode([
        "success" => false,
        "error" => "Transacción no recibida",
        "debug_input" => $inputJSON
    ]);
    exit;
}

// 4. Reenviar a webhook.site (debug opcional)
$ch = curl_init("https://webhook.site/6810f4af-d15c-4caf-9b99-d95905ef73ce");
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, $inputJSON);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_exec($ch);
curl_close($ch);

// 5. Confirmar recepción
http_response_code(200);
echo json_encode([
    "success" => true,
    "transaction_id" => $input['transaction']['id'] ?? null,
    "status" => $input['transaction']['status'] ?? null,
    "dev_reference" => $input['transaction']['dev_reference'] ?? null
]);
