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

// 1. Leer y decodificar el JSON crudo
$inputJSON = file_get_contents('php://input');
$input = json_decode($inputJSON, true);

// 2. Verificar estructura mínima requerida
if (!isset($input['transaction']['id'], $input['transaction']['stoken'], $input['transaction']['application_code'], $input['user']['id'])) {
    http_response_code(400);
    echo json_encode(["success" => false, "error" => "Estructura inválida"]);
    exit;
}

// 3. Obtener datos clave
$transaction_id     = $input['transaction']['id'];
$application_code   = $input['transaction']['application_code'];
$user_id            = $input['user']['id'];
$stoken_received    = $input['transaction']['stoken'];
$app_key            = '67vVmLALRrbSaQHiEer40gjb49peos'; // 🔐 clave secreta

// 4. Generar stoken esperado (según la doc oficial)
$stoken_expected = md5($transaction_id . '_' . $application_code . '_' . $user_id . '_' . $app_key);

// 5. Validar stoken
if ($stoken_received !== $stoken_expected) {
    http_response_code(203);
    echo json_encode(["success" => false, "error" => "Token inválido"]);
    exit;
}

// 6. Aquí podrías verificar si ya procesaste esta transacción en tu base de datos
// Si ya existe, puedes responder igual con 200 para evitar reintentos

// 7. Procesar transacción (aquí puedes guardar en tu DB)

// 8. Reenvío opcional a webhook.site (solo para depuración)
$debug_url = "https://webhook.site/6810f4af-d15c-4caf-9b99-d95905ef73ce";
$ch = curl_init($debug_url);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, $inputJSON);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_exec($ch);
curl_close($ch);

// 9. Responder con 200 OK para que Nuvei no reintente
echo json_encode([
    "success" => true,
    "transaction_id" => $transaction_id,
    "status" => $input['transaction']['status'],
    "dev_reference" => $input['transaction']['dev_reference'] ?? null
]);
