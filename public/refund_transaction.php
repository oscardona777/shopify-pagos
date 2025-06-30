<?php

header('Content-Type: application/json');
include 'config.php';
header('Content-Type: application/json');

// 1. Validar entrada obligatoria
$transaction_reference = $_POST['transaction_reference'] ?? null;
if (!$transaction_reference) {
    http_response_code(400);
    echo json_encode(["error" => "Falta transaction_reference"]);
    exit;
}

// 2. Leer parámetros opcionales
$amount = isset($_POST['amount']) ? floatval($_POST['amount']) : null;

// 3. Construir payload
$payload = [
    "transaction" => ["id" => $transaction_reference],
    "more_info" => true
];

// 4. Agregar el monto si fue enviado
if (!is_null($amount)) {
    $payload["order"] = ["amount" => $amount];
}

// 5. Codificar payload a JSON
$payload_json = json_encode($payload);

// 6. Enviar solicitud de refund a Nuvei
$ch = curl_init(api_url('/v2/transaction/refund'));
curl_setopt($ch, CURLOPT_HTTPHEADER, get_headers_auth_server());
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $payload_json);
$response = curl_exec($ch);
curl_close($ch);

// 7. Procesar respuesta y reenviar al callback si es exitosa
$response_array = json_decode($response, true);

// Enviar respuesta al cliente
echo $response;

// 8. Reenviar al callback si el refund fue exitoso
if (isset($response_array['status']) && $response_array['status'] === 'success') {
    // URL oficial de callback
    $callback_url = $CALLBACK_URL ?? null;

    if ($callback_url) {
        $ch_cb = curl_init($callback_url);
        curl_setopt($ch_cb, CURLOPT_POST, 1);
        curl_setopt($ch_cb, CURLOPT_POSTFIELDS, json_encode($response_array));
        curl_setopt($ch_cb, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch_cb, CURLOPT_RETURNTRANSFER, true);
        curl_exec($ch_cb);
        curl_close($ch_cb);
    }

    // Opcional: también enviar a webhook.site (para pruebas)
    //$debug_url = 'https://webhook.site/6810f4af-d15c-4caf-9b99-d95905ef73ce';
    //$ch_debug = curl_init($debug_url);
    //curl_setopt($ch_debug, CURLOPT_POST, 1);
    //curl_setopt($ch_debug, CURLOPT_POSTFIELDS, json_encode($response_array));
    //curl_setopt($ch_debug, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    //curl_setopt($ch_debug, CURLOPT_RETURNTRANSFER, true);
    //curl_exec($ch_debug);
    //curl_close($ch_debug);
}
