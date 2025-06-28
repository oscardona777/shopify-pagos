<?php
include 'config.php';
header('Content-Type: application/json');

// 🗂 Crear carpeta 'logs' si no existe
$logDir = __DIR__ . '/logs';
if (!file_exists($logDir)) {
    mkdir($logDir, 0755, true);
}

// 📥 Leer contenido crudo del POST
$input = file_get_contents("php://input");

// 🧾 Guardar en log con fecha
$logFile = $logDir . '/callback_log.txt';

file_put_contents($logFile, date("Y-m-d H:i:s") . "\n" . $input . "\n\n", FILE_APPEND);

// 🧠 Decodificar JSON (por si quieres procesar)
$data = json_decode($input, true);

// ✅ Validación básica (opcional)
if (isset($data['transaction']['status'])) {
    $status = $data['transaction']['status'];
    $dev_reference = $data['transaction']['dev_reference'] ?? 'N/A';
    $amount = $data['transaction']['amount'] ?? 'N/A';

    // Aquí podrías hacer algo como:
    // guardar en BD, disparar evento de compra, enviar notificación, etc.
}

// 🟢 Respuesta al emisor del callback
echo json_encode(["success" => true]);
?>
