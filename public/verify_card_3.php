<?php
$client_app_code = "TESTECUADORSTG-EC-CLIENT";
$client_app_key  = "d4pUmVHgVpw2mJ66rWwtfWaO2bAWV6";

$timestamp = time();
$token_hash = hash('sha256', $client_app_key . $timestamp);
$auth_token = base64_encode($client_app_code . ";" . $timestamp . ";" . $token_hash);

$data = [
  "user" => [
    "id" => "user_checkout_001",
    "email" => "cliente@example.com",
    "country" => "EC"
  ],
  "order" => [
    "amount" => 1.00,
    "description" => "Verificación de tarjeta",
    "dev_reference" => "verify_" . $timestamp,
    "installments" => 1,
    "currency" => "USD"
  ],
  "billing" => [
    "first_name" => "Cliente",
    "last_name" => "Demo",
    "address" => "Av. Principal 123",
    "city" => "Quito",
    "zip_code" => "170101",
    "country" => "EC",
    "phone" => "+593000000000"
  ]
];

$payload = json_encode($data);

$headers = [
  "Content-Type: application/json",
  "Auth-Token: $auth_token",
  "Auth-Timestamp: $timestamp"
];

$ch = curl_init("https://ccapi-stg.paymentez.com/v2/transaction/init_checkout");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
$response = curl_exec($ch);
$http_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

header("Content-Type: application/json");
if ($http_status == 200) {
  echo $response;
} else {
  http_response_code($http_status);
  echo json_encode([
    "error" => [
      "http_status" => $http_status,
      "message" => "Error verificando tarjeta",
      "response" => json_decode($response, true)
    ]
  ]);
}
?>
