<?php
// CORS headers
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

// Preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Leer y parsear input
$inputJSON = file_get_contents('php://input');
$input = json_decode($inputJSON, true);

// Validación mínima
if (!isset($input['transaction'])) {
    http_response_code(400);
    echo json_encode([
        "success" => false,
        "error" => "Transacción no recibida",
        "debug_input" => $inputJSON
    ]);
    exit;
}

// Variables clave
$tx = $input['transaction'];
$dev_reference = $tx['dev_reference'] ?? null;
$transaction_id = $tx['id'] ?? null;
$status = strtoupper($tx['status'] ?? '');
$current_status = strtoupper($tx['current_status'] ?? $status);

// ⚠️ Aquí es donde priorizamos CANCELLED
$estado_final = ($current_status === 'CANCELLED') ? 'CANCELLED' : $status;

// 🔄 Simulación lógica de actualización de orden (ejemplo):
// En tu sistema real deberías cargar el estado previo de la orden y decidir si sobrescribes
// Aquí simplemente reenviamos, priorizando CANCELLED si aplica

// Reenviar a destino real (Shopify o backend)
$callback_url = 'https://webhook.site/6810f4af-d15c-4caf-9b99-d95905ef73ce'; // cambiar por tu URL

$payload_modificado = $input;
$payload_modificado['transaction']['final_status'] = $estado_final;

$ch = curl_init($callback_url);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload_modificado));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_exec($ch);
curl_close($ch);

// Confirmar
http_response_code(200);
echo json_encode([
    "success" => true,
    "transaction_id" => $transaction_id,
    "final_status" => $estado_final,
    "dev_reference" => $dev_reference
]);
