<?php
// Configuración
$server_app_code = "TESTECUADORSTG-EC-SERVER";
$server_app_key = "67vVmLALRrbSaQHiEer40gjb49peos";

// Datos para el JSON
$data = [
    "user" => [
        "id" => "user_php_bypass_01",
        "email" => "prueba@correo.com",
        "country" => "EC"
    ],
    "amount" => 1.00,
    "currency" => "USD",
    "description" => "Verificación directa sin SDK",
    "reference" => "ref_php_" . time(),
    "installments" => 1,
    "billing" => [
        "first_name" => "PHP",
        "last_name" => "Bypass",
        "address" => "Calle Falsa 123",
        "city" => "Quito",
        "zip_code" => "170101",
        "country" => "EC",
        "phone" => "+593000000000"
    ]
];

// Autenticación HMAC
$uniq_token = uniqid();
$timestamp = time();
$auth_string = $server_app_code . $uniq_token . $timestamp;
$auth_token = hash_hmac("sha256", $auth_string, $server_app_key);

// Cabeceras
$headers = [
    "Content-Type: application/json",
    "Auth-Token: {$auth_token}",
    "Auth-Nonce: {$uniq_token}",
    "Auth-Timestamp: {$timestamp}",
    "Auth-App-Code: {$server_app_code}"
];

// Inicializar cURL
$ch = curl_init("https://ccapi-stg.paymentez.com/v2/transaction/init_checkout");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

// Ejecutar y mostrar resultado
$response = curl_exec($ch);
$http_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);

if (curl_errno($ch)) {
    echo "cURL error: " . curl_error($ch);
} else {
    echo "<strong>HTTP Status:</strong> {$http_status}<br><pre>{$response}</pre>";
}
curl_close($ch);
?>
