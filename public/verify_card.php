<?php
include 'config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Método no permitido']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$card_token = $input['card_token'] ?? null;

if (!$card_token) {
    http_response_code(400);
    echo json_encode(['error' => 'Token de tarjeta no recibido']);
    exit;
}

$timestamp = time();
$token_hash = hash('sha256', $client_app_key . $timestamp);
$auth_token = base64_encode($client_app_code . ';' . $timestamp . ';' . $token_hash);

$payload = [
    "user" => [
        "id" => "user-001",
        "email" => "cliente@ejemplo.com",
        "ip_address" => $_SERVER['REMOTE_ADDR']
    ],
    "order" => [
        "amount" => 1.00,
        "description" => "Verificación de tarjeta",
        "dev_reference" => "verify_" . $timestamp,
        "currency" => "USD"
    ],
    "card" => [
        "token" => $card_token
    ]
];

$ch = curl_init("https://ccapi-stg.paymentez.com/v2/transaction/debit_cc");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Content-Type: application/json",
    "Auth-Token: $auth_token"
]);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
$response = curl_exec($ch);
$http_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error_msg = curl_error($ch);
curl_close($ch);

http_response_code($http_status);

if ($response === false) {
    echo json_encode(['error' => 'Error en la solicitud cURL', 'details' => $error_msg]);
} else {
    echo $response;
}
?>
