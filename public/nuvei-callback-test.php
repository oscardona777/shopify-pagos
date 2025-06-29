<?php
// 🔓 Permitir solicitudes desde cualquier origen (solo para pruebas)
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

// ⚙️ Manejar preflight OPTIONS
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// 🧾 Leer y decodificar el JSON crudo
$inputJSON = file_get_contents('php://input');
$input = json_decode($inputJSON, true);

// ✅ Validar estructura mínima
if (!isset($input['transaction']['id'], $input['transaction']['stoken'], $input['transaction']['application_code'], $input['user']['id'])) {
    http_response_code(400);
    // 👇 Agrega encabezados CORS incluso en errores
    header("Access-Control-Allow-Origin: *");
    echo json_encode(["success" => false, "error" => "Estructura inválida"]);
    exit;
}

// 🔐 Extraer campos
$transaction_id     = $input['transaction']['id'];
$application_code   = $input['transaction']['application_code'];
$user_id            = $input['user']['id'];
$stoken_received    = $input['transaction']['stoken'];
$app_key            = '67vVmLALRrbSaQHiEer40gjb49peos';

// 🔑 Generar y validar stoken
$stoken_expected = md5($transaction_id . '_' . $application_code . '_' . $user_id . '_' . $app_key);

if ($stoken_received !== $stoken_expected) {
    http_response_code(203);
    header("Access-Control-Allow-Origin: *");
    echo json_encode(["success" => false, "error" => "Token inválido"]);
    exit;
}

// 🔁 Reenvío a webhook.site (solo pruebas)
$debug_url = "https://webhook.site/6810f4af-d15c-4caf-9b99-d95905ef73ce";
$ch = curl_init($debug_url);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, $inputJSON);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_exec($ch);
curl_close($ch);

// ✅ Confirmar recepción exitosa
header("Access-Control-Allow-Origin: *");
echo json_encode([
    "success" => true,
    "transaction_id" => $transaction_id,
    "status" => $input['transaction']['status'],
    "dev_reference" => $input['transaction']['dev_reference'] ?? null
]);
