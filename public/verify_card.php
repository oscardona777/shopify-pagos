<?php
// === verify_card.php ===
include 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $input = json_decode(file_get_contents('php://input'), true);
  $card_token = $input['card_token'] ?? null;

  if (!$card_token) {
    http_response_code(400);
    echo json_encode(["error" => "Token de tarjeta no recibido."]);
    exit;
  }

  $timestamp = time();
  $token_hash = hash('sha256', $client_app_key . $timestamp);
  $auth_token = base64_encode($client_app_code . ";" . $timestamp . ";" . $token_hash);

  $data = [
    "user" => [
      "id" => "user-001",
      "email" => "cliente@example.com",
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

  $headers = [
    "Content-Type: application/json",
    "Auth-Token: $auth_token"
  ];

  $ch = curl_init("https://ccapi-stg.paymentez.com/v2/transaction/debit_cc");
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
  curl_setopt($ch, CURLOPT_POST, true);
  curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
  curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
  $response = curl_exec($ch);
  $http_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
  curl_close($ch);

  http_response_code($http_status);
  echo $response;
} else {
  http_response_code(405);
  echo json_encode(["error" => "Método no permitido"]);
}
?>