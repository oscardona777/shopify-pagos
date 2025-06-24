<?php
include 'config.php';

$transaction_id = 'REPLACE_WITH_TX_ID';

$curl = curl_init();
curl_setopt_array($curl, [
    CURLOPT_URL => api_url("/v2/transaction/refund"),
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER => get_headers_auth(),
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => json_encode([
        'transaction' => ['id' => $transaction_id]
    ])
]);

$response = curl_exec($curl);
curl_close($curl);

header('Content-Type: application/json');
echo $response;