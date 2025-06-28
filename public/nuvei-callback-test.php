<?php
include 'config.php';
header('Content-Type: application/json');

// 🗂 Crear carpeta de logs si no existe
$logDir = __DIR__ . '/logs';
if (!file_exists($logDir)) {
    mkdir($logDir, 0755, true);
}

// 📥 Leer contenido crudo del POST
$input = file_get_contents("php://input");

// 🧾 Guardar en log con fecha
$logFile = $logDir . '/callback_log.txt';
file_put_contents($logFile, date("Y-m-d H:i:s") . "\n" . $input . "\n\n", FILE_APPEND);

// 🌐 Reenviar a Webhook.site
$webhook_url = 'https://webhook.site/6810f4af-d15c-4caf-9b99-d95905ef73ce';

$ch = curl_init($webhook_url);
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
curl_setopt($ch, CURLOPT_POSTFIELDS, $input);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Content-Length: ' . strlen($input)
]);
$response = curl_exec($ch);
curl_close($ch);

// 🟢 Confirmar recepción al emisor original
echo json_encode(["success" => true]);
?>
