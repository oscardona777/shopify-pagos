<?php
include 'config.php';

$curl = curl_init();
curl_setopt_array($curl, [
    CURLOPT_URL => api_url("/v2/transaction/debit"),
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER => get_headers_auth(),
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => json_encode([
        'user' => ['id' => 'user123', 'email' => 'test@test.com'],
        'order' => [
            'amount' => 10,
            'description' => 'Recurring Test',
            'dev_reference' => uniqid("ref_"),
            'vat' => 0
        ],
        'card' => ['token' => 'REPLACE_WITH_CARD_TOKEN']
    ])
]);

$response = curl_exec($curl);
curl_close($curl);

header('Content-Type: application/json');
echo $response;