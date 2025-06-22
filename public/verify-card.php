<?php
// Usa credenciales sandbox si aún no tienes propias
define("APP_SERVER_CODE", "TESTECUADORSTG-EC-SERVER");
define("APP_SERVER_KEY", "67vVmLALRrbSaQHiEer40gjb49peos");

header('Content-Type: text/plain');

// Leer datos enviados desde el frontend
$input = json_decode(file_get_contents("php://input"), true);
$token = $input['token'] ?? '';
$bin = $input['bin'] ?? '';
$user_id = $input['user_id'] ?? '';

// Mostrar valores recibidos para depuración
echo "🟡 Datos recibidos:\n";
var_dump(['token' => $token, 'bin' => $bin, 'user_id' => $user_id]);

if (!$token || !$bin || !$user_id) {
  http_response_code(400);
  echo "\n❌ Datos incompletos.";
  exit;
}

// Generar Auth-Token
function generateAuthToken($params, $app_code, $app_key) {
  ksort($params);
  $query = [];
  foreach ($params as $key => $value) {
    $query[] = $key . '=' . urlencode($value);
  }
  $nonce = uniqid('', true);
  $timestamp = time();
  $signature_base = implode('&', $query) . "&timestamp=" . $timestamp . $app_key;
  $token = hash('sha256', $signature_base);

  return "application_code={$app_code},nonce={$nonce},timestamp={$timestamp},token={$token}";
}

$params = [
  "application_code" => APP_SERVER_CODE,
  "bin" => $bin,
  "token" => $token,
  "user_id" => $user_id
];

$auth_token = generateAuthToken($params, APP_SERVER_CODE, APP_SERVER_KEY);
echo "\n🟢 Auth-Token generado:\n$auth_token\n";

// Cuerpo de la petición
$payload = json_encode([
  "token" => $token,
  "user" => ["id" => $user_id]
]);

echo "\n📦 Payload enviado:\n$payload\n";

// Llamar a la API
$ch = curl_init("https://ccapi-stg.paymentez.com/v2/card/verify/");
curl_setopt($ch, CURLOPT_HTTPHEADER, [
  "Content-Type: application/json",
  "Auth-Token: $auth_token"
]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
curl_setopt($ch, CURLOPT_POST, true);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curl_error = curl_error($ch);
curl_close($ch);

echo "\n🌐 Código HTTP: $http_code\n";
echo "🔽 Respuesta completa:\n$response\n";
if ($curl_error) echo "❌ CURL Error: $curl_error\n";
?>

