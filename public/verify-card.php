<?php
define("APP_SERVER_CODE", "TESTECUADORSTG-EC-SERVER");
define("APP_SERVER_KEY", "67vVmLALRrbSaQHiEer40gjb49peos");

$input = json_decode(file_get_contents("php://input"), true);
$token = $input['token'] ?? '';
$bin = $input['bin'] ?? '';
$user_id = $input['user_id'] ?? '';

if (!$token || !$bin || !$user_id) {
  http_response_code(400);
  echo "❌ Datos incompletos.";
  exit;
}

$auth_string = base64_encode(APP_SERVER_CODE . ":" . APP_SERVER_KEY);

$payload = json_encode([
  "token" => $token,
  "bin" => $bin,
  "user" => ["id" => $user_id]
]);

$ch = curl_init("https://ccapi-stg.paymentez.com/v2/card/verify/");
curl_setopt($ch, CURLOPT_HTTPHEADER, [
  "Content-Type: application/json",
  "Auth-Token: $auth_string"
]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
curl_setopt($ch, CURLOPT_POST, true);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($http_code == 200) {
  echo "✅ Tarjeta verificada correctamente.";
} else {
  echo "❌ Verificación fallida. Código HTTP: $http_code\nRespuesta: $response";
}
