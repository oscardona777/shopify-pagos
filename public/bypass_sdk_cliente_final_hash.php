<?php
// Credenciales CLIENTE (para entorno STG)
$client_app_code = "TESTECUADORSTG-EC-CLIENT";
$client_app_key = "d4pUmVHgVpw2mJ66rWwtfWaO2bAWV6";

// Generación de token con hash SHA256
$timestamp = time();
$token_hash = hash('sha256', $client_app_key . $timestamp);
$auth_token = base64_encode("{$client_app_code};{$timestamp};{$token_hash}");

// Cuerpo del JSON con estructura válida
$data = [
    "user" => [
        "id" => "user_php_hash_01",
        "email" => "test@cliente.com",
        "country" => "EC"
    ],
    "order" => [
        "amount" => 1.00,
        "description" => "Validación con hash SHA256",
        "dev_reference" => "hash_" . time(),
        "installments" => 1,
        "currency" => "USD"
    ],
    "billing" => [
        "first_name" => "Prueba",
        "last_name" => "SHA",
        "address" => "Calle Falsa 123",
        "city" => "Quito",
        "zip_code" => "170101",
        "country" => "EC",
        "phone" => "+593000000000"
    ]
];

// Headers requeridos
$headers = [
    "Content-Type: application/json",
    "Auth-Token: {$auth_token}",
    "Auth-Timestamp: {$timestamp}"
];

// cURL
$ch = curl_init("https://ccapi-stg.paymentez.com/v2/transaction/init_checkout");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

// Resultado
$response = curl_exec($ch);
$http_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);

if (curl_errno($ch)) {
    echo "❌ cURL error: " . curl_error($ch);
} else {
    echo "<strong>✅ HTTP Status:</strong> {$http_status}<br><pre>{$response}</pre>";
}

curl_close($ch);
?>
