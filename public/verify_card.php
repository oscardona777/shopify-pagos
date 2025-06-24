<?php
$server_app_code = "TESTECUADORSTG-EC-SERVER";
$server_app_key = "67vVmLALRrbSaQHiEer40gjb49peos";

$reference = $_GET['reference'] ?? null;

if (!$reference) {
    echo "❌ Error: Falta el parámetro 'reference' en la URL.";
    exit;
}

$timestamp = time();
$token_hash = hash('sha256', $server_app_key . $timestamp);
$auth_token = base64_encode("{\$server_app_code};{\$timestamp};{\$token_hash}");

$headers = array(
    "Content-Type: application/json",
    "Auth-Token: " . $auth_token,
    "Auth-Timestamp: " . $timestamp
);

$url = "https://ccapi-stg.paymentez.com/v2/transaction/get_by_reference?dev_reference=" . urlencode($reference);
$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
$response = curl_exec($ch);
$http_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

header("Content-Type: application/json; charset=utf-8");
http_response_code($http_status);
echo $response;
?>
