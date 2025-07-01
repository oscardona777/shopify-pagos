<?php
// CORS headers
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

// Preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// PHPMailer
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require 'PHPMailer/PHPMailer.php';
require 'PHPMailer/SMTP.php';
require 'PHPMailer/Exception.php';

// Leer input
$inputJSON = file_get_contents('php://input');
$input = json_decode($inputJSON, true);

if (!isset($input['transaction'])) {
    http_response_code(400);
    echo json_encode([
        "success" => false,
        "error" => "Transacción no recibida",
        "debug_input" => $inputJSON
    ]);
    exit;
}

$tx = $input['transaction'];
$dev_reference = $tx['dev_reference'] ?? 'N/A';
$transaction_id = $tx['id'] ?? 'N/A';
$amount = $tx['amount'] ?? 0;
$email = $tx['email'] ?? 'sin_email@honorstore.ec';
$status = strtoupper($tx['status'] ?? '');
$current_status = strtoupper($tx['current_status'] ?? $status);
$estado_final = ($current_status === 'CANCELLED') ? 'CANCELLED' : $status;

// Reenviar a webhook externo (opcional)
$callback_url = 'https://webhook.site/6810f4af-d15c-4caf-9b99-d95905ef73ce';
$payload_modificado = $input;
$payload_modificado['transaction']['final_status'] = $estado_final;

$ch = curl_init($callback_url);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload_modificado));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_exec($ch);
curl_close($ch);

// Enviar correo vía SMTP
$mail = new PHPMailer(true);

try {
    // Configuración SMTP
    $mail->isSMTP();
    $mail->Host       = 'smtp.gmail.com';
    $mail->SMTPAuth   = true;
    $mail->Username   = 'cardona.cardona@gmail.com';         // TU CORREO
    $mail->Password   = 'hcus yphn tpsk aflc';         // CONTRASEÑA DE APLICACIÓN
    $mail->SMTPSecure = 'tls';
    $mail->Port       = 587;

    // Correo
    $mail->setFrom('cardona.cardona@gmail.com', 'HonorStore');
    $mail->addAddress('cardona.cardona@gmail.com'); // Puedes cambiar esto si quieres otro destino
    $mail->isHTML(true);
    $mail->Subject = "🧾 Transacción: {$estado_final}";
    $mail->Body    = "
        <h2>📄 Detalles de la transacción</h2>
        <p><strong>Nro de orden:</strong> {$dev_reference}</p>
        <p><strong>Valor:</strong> \${$amount}</p>
        <p><strong>Estado final:</strong> {$estado_final}</p>
    ";

    $mail->send();
    $correo_enviado = true;
} catch (Exception $e) {
    $correo_enviado = false;
}

// Respuesta final
http_response_code(200);
echo json_encode([
    "success" => true,
    "transaction_id" => $transaction_id,
    "final_status" => $estado_final,
    "dev_reference" => $dev_reference,
    "correo_enviado" => $correo_enviado
]);
