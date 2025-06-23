<?php
// Credenciales del servidor (no deben compartirse en frontend)
define("APP_SERVER_CODE", "TESTECUADORSTG-EC-SERVER");
define("APP_SERVER_KEY", "67vVmLALRrbSaQHiEer40gjb49peos");

function generate_auth_token() {
    $uniq_token = uniqid();
    $auth_timestamp = time();
    $auth_string = APP_SERVER_KEY . $uniq_token . $auth_timestamp;
    $auth_token = base64_encode(hash("sha256", $auth_string, true));
    return [
        "Auth-Token" => $auth_token,
        "Auth-Nonce" => $uniq_token,
        "Auth-Timestamp" => $auth_timestamp
    ];
}

// Leer datos JSON enviados desde JS
$input = json_decode(file_get_contents("php://input"), true);

if (!$input || !isset($input["token"]) || !isset($input["bin"]) || !isset($input["user_id"])) {
    http_response_code(400);
    echo "❌ Datos incompletos para verificar la tarjeta.";
    exit;
}

$token = $input["token"];
$bin = $input["bin"];
$user_id = $input["user_id"];

// Construir cuerpo del POST
$body = json_encode([
    "user" => [ "id" => $user_id ],
    "card" => [ "token" => $token, "bin" => $bin ]
]);

// Encabezados
$auth_headers = generate_auth_token();
$headers = [
    "Content-Type: application/json",
    "Auth-Token: " . $auth_headers["Auth-Token"],
    "Auth-Nonce: " . $auth_headers["Auth-Nonce"],
    "Auth-Timestamp: " . $auth_headers["Auth-Timestamp"],
    "Auth-Client-Code: " . APP_SERVER_CODE
];

// Hacer la solicitud a Paymentez (sandbox endpoint)
$ch = curl_init("https://ccapi-stg.paymentez.com/v2/card/verify/");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
curl_setopt($ch, CURLOPT_POST, true);
$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

// Mostrar resultado
if ($http_code === 200) {
    echo "✅ Verificación exitosa:\n" . $response;
} else {
    echo "❌ Error al verificar tarjeta (HTTP $http_code):\n" . $response;
    if ($error) {
        echo "\nError de CURL: $error";
    }
}
?>
