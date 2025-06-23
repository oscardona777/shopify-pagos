<?php
// Credenciales de SERVIDOR (para verificar transacción)
$server_app_code = "TESTECUADORSTG-EC-SERVER";
$server_app_key = "67vVmLALRrbSaQHiEer40gjb49peos";

// Timestamp y token hash
$timestamp = time();
$token_hash = hash('sha256', $server_app_key . $timestamp);
$auth_token = base64_encode("{$server_app_code};{$timestamp};{$token_hash}");

// REFERENCIA a verificar (puede ser dinámica)
$reference = $_GET["reference"] ?? "ref_addcard_0000"; // reemplaza con valor real si es necesario

// Headers
$headers = [
    "Content-Type: application/json",
    "Auth-Token: {$auth_token}",
    "Auth-Timestamp: {$timestamp}"
];

// cURL
$url = "https://ccapi-stg.paymentez.com/v2/transaction/verify/{$reference}";
$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
$response = curl_exec($ch);
$http_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

// Mostrar resultado
echo "<strong>✅ Verificando referencia:</strong> {$reference}<br>";
echo "<strong>HTTP Status:</strong> {$http_status}<br>";
echo "<pre>{$response}</pre>";
?>
