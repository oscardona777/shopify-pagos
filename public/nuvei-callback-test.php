<?php
// CORS headers
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

include 'config.php';

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

// Extraer datos clave
$tx = $input['transaction'];
$dev_reference = $tx['dev_reference'] ?? 'N/A';
$transaction_id = $tx['id'] ?? 'N/A';
$amount = $tx['amount'] ?? 0;
$status = strtoupper($tx['status'] ?? '');
$current_status = strtoupper($tx['current_status'] ?? $status);
$estado_final = ($current_status === 'CANCELLED') ? 'CANCELLED' : $status;

// Extraer correo del dev_reference
if (preg_match('/__correo=([a-zA-Z0-9.%_+-]+%40[a-zA-Z0-9.-]+\.[a-zA-Z]{2,})/', $dev_reference, $matches)) {
    $email = urldecode($matches[1]);
} else {
    $email = 'sin_email@honorstore.ec';
}

// Limpiar dev_reference → obtener solo el ID de orden Shopify
$order_id = str_replace('ORDER_', '', explode('__', $dev_reference)[0]);

// Reflejar cambios en payload
$payload_modificado = $input;
$payload_modificado['transaction']['final_status'] = $estado_final;
$payload_modificado['transaction']['email'] = $email;
$payload_modificado['transaction']['dev_reference'] = $order_id;

// Callback externo (opcional)
$callback_url = getenv('CALLBACK_REDIRECT_URL') ?: $CALLBACK_URL;
$ch = curl_init($callback_url);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload_modificado));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_exec($ch);
curl_close($ch);

// Enviar correo
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/PHPMailer.php';
require 'PHPMailer/SMTP.php';
require 'PHPMailer/Exception.php';

$mail = new PHPMailer(true);
try {
    $mail->isSMTP();
    $mail->Host       = 'smtp.gmail.com';
    $mail->SMTPAuth   = true;
    $mail->Username   = getenv('SMTP_USER');
    $mail->Password   = getenv('SMTP_PASS');
    $mail->SMTPSecure = 'tls';
    $mail->Port       = 587;

    $mail->setFrom('no-reply@honorstore.ec', 'HonorStore');
    $mail->CharSet = 'UTF-8';
    $mail->addAddress(filter_var($email, FILTER_VALIDATE_EMAIL) ? $email : 'backup@honorstore.ec');
    $mail->isHTML(true);
    $mail->Subject = "🧾 Transacción: {$estado_final}";
    $mail->Body = "
        <h2>📄 Detalles de la transacción</h2>
        <p><strong>Nro de orden:</strong> {$order_id}</p>
        <p><strong>Valor:</strong> \${$amount}</p>
        <p><strong>Estado final:</strong> {$estado_final}</p>
    ";
    $mail->send();
    $correo_enviado = true;
} catch (Exception $e) {
    $correo_enviado = false;
}

// 🔁 Enviar evento a Shopify
$shopify_notificado = false;
$shopify_url_base = $SHOPIFY_STORE_URL . "/admin/api/2024-04/orders/{$order_id}";

if ($estado_final === 'APPROVED') {
    $tx_url = $shopify_url_base . "/transactions.json";
    $tx_data = [
        'transaction' => [
            'kind' => 'capture',
            'status' => 'success',
            'amount' => $amount
        ]
    ];
    $ch_tx = curl_init($tx_url);
    curl_setopt($ch_tx, CURLOPT_POST, 1);
    curl_setopt($ch_tx, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch_tx, CURLOPT_POSTFIELDS, json_encode($tx_data));
    curl_setopt($ch_tx, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'X-Shopify-Access-Token: ' . getenv()
    ]);
    $response_tx = curl_exec($ch_tx);
    $code_tx = curl_getinfo($ch_tx, CURLINFO_HTTP_CODE);
    curl_close($ch_tx);
    $shopify_notificado = ($code_tx === 201);

} elseif ($estado_final === 'CANCELLED') {
    $cancel_url = $shopify_url_base . "/cancel.json";
    $cancel_data = [
        'reason' => 'customer',
        'email' => true,
        'restock' => false
    ];
    $ch_cancel = curl_init($cancel_url);
    curl_setopt($ch_cancel, CURLOPT_POST, 1);
    curl_setopt($ch_cancel, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch_cancel, CURLOPT_POSTFIELDS, json_encode($cancel_data));
    curl_setopt($ch_cancel, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'X-Shopify-Access-Token: ' . getenv('SHOPIFY_ACCESS_TOKEN')
    ]);
    $response_cancel = curl_exec($ch_cancel);
    $code_cancel = curl_getinfo($ch_cancel, CURLINFO_HTTP_CODE);
    curl_close($ch_cancel);
    $shopify_notificado = ($code_cancel === 200);
}

// Reenviar a destino real (Shopify o backend)
$callback_url = 'https://webhook.site/6810f4af-d15c-4caf-9b99-d95905ef73ce'; // cambiar por tu URL

$payload_modificado = $input;
$payload_modificado['transaction']['final_status'] = $estado_final;

$ch = curl_init($callback_url);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload_modificado));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_exec($ch);
curl_close($ch);


// Respuesta final
http_response_code(200);
echo json_encode([
    "success" => true,
    "transaction_id" => $transaction_id,
    "final_status" => $estado_final,
    "dev_reference" => $order_id,
    "email" => $email,
    "correo_enviado" => $correo_enviado,
    "shopify_notificado" => $shopify_notificado
]);
