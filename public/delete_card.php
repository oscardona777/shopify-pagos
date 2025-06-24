<?php
include 'config.php';

$user_id = 'user123';
$token = $_GET['token'];

$curl = curl_init();
curl_setopt_array($curl, [
    CURLOPT_URL => api_url("/v2/card/delete"),
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER => get_headers_auth(),
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => json_encode([
        'user' => ['id' => $user_id],
        'token' => $token
    ])
]);

$response = curl_exec($curl);
curl_close($curl);

header('Content-Type: application/json');
echo $response;
