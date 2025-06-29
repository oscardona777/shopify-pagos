<?php
// nuvei-callback.php

header('Content-Type: application/json');

// 1. Leer el contenido crudo (JSON del modal)
$inputJSON = file_get_contents('php://input');
$input = json_decode($inputJSON, true);

// 2. Validar estructura básica
if (!isset($input['transaction'], $input['user'])) {
    http_response_code(400);
    echo json_encode([
        "success" => false,
        "error" => "Estructura inválida"
    ]);
    exit;
}

// 3. Procesamiento interno (puedes guardar en BD aquí si quieres)
$response = [
    "success" => true,
    "transaction_id" => $input['transaction']['id'] ?? null,
    "status" => $input['transaction']['status'] ?? null,
    "user_id" => $input['user']['id'] ?? null,
    "dev_reference" => $input['transaction']['dev_reference'] ?? null
];

// 4. (Opcional) Reenvío al webhook.site para inspección
$webhook_url = "https://webhook.site/6810f4af-d15c-4caf-9b99-d95905ef73ce"; // Puedes cambiarlo por uno tuyo
$ch = curl_init($webhook_url);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($input));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$webhook_response = curl_exec($ch);
curl_close($ch);

// 5. Devolver confirmación
echo json_encode($response);
