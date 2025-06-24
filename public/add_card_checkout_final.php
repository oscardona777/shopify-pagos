<?php
// Credenciales
$client_app_code = "TESTECUADORSTG-EC-CLIENT";
$client_app_key  = "d4pUmVHgVpw2mJ66rWwtfWaO2bAWV6";

// Auth Token
$timestamp = time();
$token_hash = hash('sha256', $client_app_key . $timestamp);
$auth_token = base64_encode($client_app_code . ";" . $timestamp . ";" . $token_hash);

// Datos de la transacción
$data = [
  "user" => [
    "id" => "user_checkout_001",
    "email" => "cliente@example.com",
    "country" => "EC"
  ],
  "order" => [
    "amount" => 1.00,
    "description" => "Verificación de tarjeta",
    "dev_reference" => "checkout_" . $timestamp,
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

if ($http_status == 200) {
  $result = json_decode($response, true);
  echo "<h3>✅ Checkout generado correctamente</h3>";
  echo "<p><a href='" . $result["checkout_url"] . "' target='_blank'>Abrir checkout</a></p>";
} else {
  echo "<h3>❌ Error HTTP $http_status</h3>";
  echo "<pre>$response</pre>";
}
?>
