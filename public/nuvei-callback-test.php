<?php
// webhook.php

header('Content-Type: application/json');

// 🔐 Credenciales del servidor
$server_app_code = 'TESTECUADORSTG-EC-SERVER';
$server_app_key = '67vVmLALRrbSaQHiEer40gjb49peos';

// 🌐 Función para construir headers de autenticación
function get_headers_auth_server($app_code, $app_key) {
    $timestamp = time();
    $uniq_token_string = $app_key . $timestamp;
    $uniq_token_hash = hash('sha256', $uniq_token_string);
    $auth_token = base64_encode($app_code . ";" . $timestamp . ";" . $uniq_token_hash);

    return [
        'Content-Type: application/json',
        'Auth-Token: ' . $auth_token
    ];
}

// 🌐 Función auxiliar para obtener endpoint base
function api_url($path) {
    return "https://ccapi-stg.paymentez.com" . $path;
}

// 📥 Recibir y decodificar el JSON
$inputJSON = file_get_contents('php://input');
$input = json_decode($inputJSON, true);

// ✅ Validar estructura básica
if (!isset($input['transaction']['id'], $input['transaction']['authorization_code'], $input['user']['id'])) {
    http_response_code(400);
    echo json_encode(["success" => false, "error" => "Faltan parámetros obligatorios"]);
    exit;
}

$transaction_id = $input['transaction']['id'];
$authorization_code = $input['transaction']['authorization_code'];
$user_id = $input['user']['id'];

// 🛠 Armar payload de verificación
$verify_payload = [
    "transaction" => [ "id" => $transaction_id ],
    "user" => [ "id" => $user_id ],
    "type" => "BY_AUTH_CODE",
    "value" => $authorization_code,
    "more_info" => true
];

// 🚀 Enviar verificación a Paymentez
$verify_url = api_url("/v2/transaction/verify/");
$headers = get_headers_auth_server($server_app_code, $server_app_key);

$ch = curl_init($verify_url);
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($verify_payload));
curl_setopt($ch, CURLOPT_POST, true);
$response = curl_exec($ch);
$verify_http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

// 📤 Enviar copia del callback a webhook.site para pruebas
$webhook_test_url = 'https://webhook.site/6810f4af-d15c-4caf-9b99-d95905ef73ce';
$ch = curl_init($webhook_test_url);
curl_setopt($ch, CURLOPT_POSTFIELDS, $inputJSON);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_exec($ch);
curl_close($ch);

// 📦 Responder al sistema
echo json_encode([
    "success" => true,
    "verify_http_code" => $verify_http_code,
    "webhook_http_code" => 200,
    "payload_sent" => $verify_payload,
    "response" => json_decode($response, true)
]);
