<?php
// Credenciales CLIENTE (correctas para init_checkout)
$client_app_code = "TESTECUADORSTG-EC-CLIENT";
$client_app_key = "d4pUmVHgVpw2mJ66rWwtfWaO2bAWV6";

// Timestamp y Auth-Token base64 (no HMAC)
$timestamp = time();
$auth_token = base64_encode("{$client_app_code};{$timestamp};{$client_app_key}");

// JSON corregido: incluye el campo obligatorio "order"
$data = [
    "user" => [
        "id" => "user_php_bypass_cliente_02",
        "email" => "cliente@correo.com",
        "country" => "EC"
    ],
    "order" => [
        "amount" => 1.00,
        "description" => "Verificación directa con credenciales de cliente",
        "dev_reference" => "ref_cli_" . time(),
        "installments" => 1,
        "currency" => "USD"
    ],
    "billing" => [
        "first_name" => "Cliente",
        "last_name" => "PHP",
        "address" => "Av 123",
        "city" => "Quito",
        "zip_code" => "170101",
        "country" => "EC",
        "phone" => "+593000000000"
    ]
];

// Cabeceras válidas
$headers = [
    "Content-Type: application/json",
    "Auth-Token: {$auth_token}",
    "Auth-Timestamp: {$timestamp}"
];

// cURL request
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
