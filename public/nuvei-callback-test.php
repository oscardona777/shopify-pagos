<?php
// CORS headers
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// 🛍 Shopify credentials (embebidas)
$SHOPIFY_STORE_URL = "https://honortest.myshopify.com";
$SHOPIFY_ACCESS_TOKEN = "xxx";
$SHOPIFY_API_VERSION = "2024-04";

// Leer y parsear input
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

// Limpiar dev_reference → solo ID de orden Shopify
$order_id = str_replace('ORDER_', '', explode('__', $dev_reference)[0]);

// Enviar correo (opcional)
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require 'PHPMailer/PHPMailer.php';
require 'PHPMailer/SMTP.php';
require 'PHPMailer/Exception.php';

$correo_enviado = false;
$mail = new PHPMailer(true);
try {
    $mail->isSMTP();
    $mail->Host       = 'smtp.gmail.com';
    $mail->SMTPAuth   = true;
    $mail->Username   = 'cardona.cardona@gmail.com';
    $mail->Password   = 'hcus yphn tpsk aflc';
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
$shopify_url_base = $SHOPIFY_STORE_URL . "/admin/api/{$SHOPIFY_API_VERSION}/orders/{$order_id}";

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
        'X-Shopify-Access-Token: ' . $SHOPIFY_ACCESS_TOKEN
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
        'X-Shopify-Access-Token: ' . $SHOPIFY_ACCESS_TOKEN
    ]);
    $response_cancel = curl_exec($ch_cancel);
    $code_cancel = curl_getinfo($ch_cancel, CURLINFO_HTTP_CODE);
    curl_close($ch_cancel);
    $shopify_notificado = ($code_cancel === 200);
}

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
