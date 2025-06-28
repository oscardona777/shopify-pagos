<?php
// nuvei-callback-test.php

header('Content-Type: application/json');

// 1. Leer el contenido crudo enviado por Nuvei
$inputJSON = file_get_contents("php://input");

// 2. Reenviar el contenido crudo a webhook.site
$webhook_url = 'https://webhook.site/6810f4af-d15c-4caf-9b99-d95905ef73ce';

$ch = curl_init($webhook_url);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $inputJSON);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_exec($ch);
curl_close($ch);

// 3. Devolver respuesta 200 a Nuvei
http_response_code(200);
echo json_encode([
    'success' => true,
    'message' => 'Webhook recibido y reenviado a webhook.site'
]);
