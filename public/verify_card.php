<?php
include 'config.php';

if (!isset($_POST['token']) || empty($_POST['token'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Token no recibido']);
    exit;
}

$token = $_POST['token'];

$curl = curl_init();
curl_setopt_array($curl, [
    CURLOPT_URL => api_url("/v2/card/verify"),
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER => get_headers_auth(),
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => json_encode(['token' => $token])
]);

$response = curl_exec($curl);
$http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
curl_close($curl);

http_response_code($http_code);
header('Content-Type: application/json');
echo $response;
