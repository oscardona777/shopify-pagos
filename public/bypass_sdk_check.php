<?php
// Credenciales de CLIENTE (correctas para init_checkout en entorno STG)
$client_app_code = "TESTECUADORSTG-EC-CLIENT";
$client_app_key = "d4pUmVHgVpw2mJ66rWwtfWaO2bAWV6";

$timestamp = time();
$token_hash = hash('sha256', $client_app_key . $timestamp);
$auth_token = base64_encode("{$client_app_code};{$timestamp};{$token_hash}");

// Estructura JSON válida (según documentación oficial)
$data = [
    "user" => [
        "id" => "user_php_final_01",
        "email" => "testuser@example.com",
        "country" => "EC"
    ],
    "order" => [
        "amount" => 1.00,
        "description" => "Validación de tarjeta desde PHP final",
        "dev_reference" => "ref_final_" . time(),
        "installments" => 1,
        "currency" => "USD"
    ],
    "billing" => [
        "first_name" => "Test",
        "last_name" => "User",
        "address" => "Av Siempre Viva 742",
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

// Ejecutar solicitud cURL
$ch = curl_init("https://ccapi-stg.paymentez.com/v2/transaction/init_checkout");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

// Mostrar resultado
$response = curl_exec($ch);
$http_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);

if (curl_errno($ch)) {
    echo "❌ cURL error: " . curl_error($ch);
} else {
    echo "<strong>✅ HTTP Status:</strong> {$http_status}<br><pre>{$response}</pre>";
}

curl_close($ch);
?>
