<?php
// === CONFIGURACIÓN SERVIDOR (USA CREDENCIALES DE SERVIDOR) ===
define("APP_SERVER_CODE", "TU_APP_CODE_SERVIDOR"); // ejemplo: PE-XXXXXXXX
define("APP_SERVER_KEY", "TU_APP_KEY_SERVIDOR");   // clave secreta
define("URL_API", "https://sandbox-api.paymentez.com/v2");

// Leer datos enviados desde el frontend (JSON)
$data = json_decode(file_get_contents("php://input"), true);
$user_id = $data["user_id"] ?? null;
$token   = $data["token"] ?? null;
$bin     = $data["bin"] ?? null;

if (!$user_id || !$token || !$bin) {
  http_response_code(400);
  echo "❌ Faltan datos obligatorios.";
  exit;
}

// Verificamos si es tarjeta Diners (puedes ajustar el rango si es necesario)
$is_diners = preg_match('/^(36|38|39|389507)/', $bin);

if ($is_diners) {
  // Verificar tarjeta Diners con /card/verify
  $payload = [
    "user" => [ "id" => $user_id ],
    "card" => [ "token" => $token ]
  ];

  $response = api_call("/card/verify/", $payload);
  if (isset($response['status']) && $response['status'] === 'success') {
    echo "✅ Verificación Diners exitosa";
  } else {
    echo "❌ Verificación Diners fallida: " . json_encode($response);
  }
} else {
  // No requiere verificación
  echo "✅ Tarjeta registrada correctamente (no es Diners)";
}


// ============================
// Función de autenticación segura
// ============================
function auth_token() {
  $uuid = uniqid();
  $timestamp = time();
  $token_string = APP_SERVER_KEY . $uuid . $timestamp;
  $token_hash = hash('sha256', $token_string);

  return "server_code=" . APP_SERVER_CODE .
         ",nonce=" . $uuid .
         ",timestamp=" . $timestamp .
         ",token=" . $token_hash;
}


// ============================
// Función para hacer POST a la API
// ============================
function api_call($endpoint, $body) {
  $url = URL_API . $endpoint;
  $ch = curl_init($url);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
  curl_setopt($ch, CURLOPT_POST, true);
  curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Content-Type: application/json",
    "Auth-Token: " . auth_token()
  ]);
  curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body));

  $response = curl_exec($ch);
  $error = curl_error($ch);
  curl_close($ch);

  return $error ? ["error" => $error] : json_decode($response, true);
}
