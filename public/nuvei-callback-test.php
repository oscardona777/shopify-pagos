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

// Leer y parsear input
$inputJSON = file_get_contents('php://input');
$input = json_decode($inputJSON, true);

// Validación mínima
if (!isset($input['transaction'])) {
    http_response_code(400);
    echo json_encode([
        "success" => false,
        "error" => "Transacción no recibida",
        "debug_input" => $inputJSON
    ]);
    exit;
}

// Extraer campos clave
$tx = $input['transaction'];
$dev_reference = $tx['dev_reference'] ?? 'N/A';
$transaction_id = $tx['id'] ?? 'N/A';
$amount = $tx['amount'] ?? 0;
$status = strtoupper($tx['status'] ?? '');
$current_status = strtoupper($tx['current_status'] ?? $status);
$estado_final = ($current_status === 'CANCELLED') ? 'CANCELLED' : $status;

// Extraer el correo desde dev_reference
if (preg_match('/__correo=([a-zA-Z0-9.%_+-]+%40[a-zA-Z0-9.-]+\.[a-zA-Z]{2,})/', $dev_reference, $matches)) {
    $email = urldecode($matches[1]);
} else {
    $email = 'sin_email@honorstore.ec';
}

// Limpiar dev_reference → solo ID de orden
$dev_reference = explode('__', $dev_reference)[0];

// Reflejar los cambios en el payload que se reenviará
$payload_modificado = $input;
$payload_modificado['transaction']['final_status'] = $estado_final;
$payload_modificado['transaction']['email'] = $email;
$payload_modificado['transaction']['dev_reference'] = $dev_reference;

// Enviar callback a backend o webhook.site
$callback_url = getenv('CALLBACK_REDIRECT_URL') ?: 'https://webhook.site/6810f4af-d15c-4caf-9b99-d95905ef73ce';
$ch = curl_init($callback_url);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload_modificado));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_exec($ch);
curl_close($ch);

// Enviar correo al cliente
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/PHPMailer.php';
require 'PHPMailer/SMTP.php';
require 'PHPMailer/Exception.php';
// Si usas Composer, reemplaza por: require 'vendor/autoload.php';

$mail = new PHPMailer(true);
try {
    $mail->isSMTP();
    $mail->Host       = 'smtp.gmail.com';
    $mail->SMTPAuth   = true;
    $mail->Username   = getenv('SMTP_USER');
    $mail->Password   = getenv('SMTP_PASS');
    $mail->SMTPSecure = 'tls';
    $mail->Port       = 587;

    $mail->setFrom(getenv('SMTP_USER'), 'HonorStore');
    $mail->CharSet = 'UTF-8';

    if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $mail->addAddress($email);
    } else {
        $mail->addAddress('backup@honorstore.ec');
    }

    $mail->isHTML(true);
    $mail->Subject = "🧾 Transacción: {$estado_final}";
    $mail->Body = "
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

// Respuesta JSON
http_response_code(200);
echo json_encode([
    "success" => true,
    "transaction_id" => $transaction_id,
    "final_status" => $estado_final,
    "dev_reference" => $dev_reference,
    "email" => $email,
    "correo_enviado" => $correo_enviado
]);
