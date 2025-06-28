<?php
header('Content-Type: application/json');

// Leer contenido crudo del POST
$input = file_get_contents("php://input");

// URL del webhook externo (como Webhook.site)
$webhook_url = 'https://webhook.site/6810f4af-d15c-4caf-9b99-d95905ef73ce';

// Enviar el mismo payload al webhook
$ch = curl_init($webhook_url);
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
curl_setopt($ch, CURLOPT_POSTFIELDS, $input);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Content-Length: ' . strlen($input)
]);
curl_exec($ch);
curl_close($ch);

// Confirmar recepción al emisor original
echo json_encode(["success" => true]);
?>
