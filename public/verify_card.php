<?php
// Recibe JSON
$body = json_decode(file_get_contents("php://input"), true);

if (!$body || !isset($body['token']) || !isset($body['bin']) || !isset($body['user_id'])) {
    http_response_code(400);
    echo "❌ Datos incompletos o mal formateados.";
    exit;
}

// Datos necesarios
$token = $body['token'];
$bin = $body['bin'];
$user_id = $body['user_id'];

// Credenciales del servidor Paymentez sandbox
$server_app_code = "TESTECUADORSTG-EC-SERVER";
$server_app_key = "67vVmLALRrbSaQHiEer40gjb49peos";
$unix_timestamp = time();
$auth_string = base64_encode(hash('sha256', $server_app_code . $server_app_key . $unix_timestamp, true));

// Construcción del header
$headers = [
    "Auth-Token: {$auth_string}",
    "Auth-Login: {$server_app_code}",
    "Auth-Time: {$unix_timestamp}",
    "Content-Type: application/json"
];

// Payload
$data = json_encode([
    "card" => [
        "token" => $token,
        "bin" => $bin
    ],
    "user" => [
        "id" => $user_id
    ]
]);

$ch = curl_init("https://ccapi-stg.paymentez.com/v2/card/verify/");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
$response = curl_exec($ch);
$httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

if (curl_errno($ch)) {
    http_response_code(500);
    echo "❌ Error CURL: " . curl_error($ch);
} else {
    http_response_code($httpcode);
    echo $response;
}
curl_close($ch);
?>
