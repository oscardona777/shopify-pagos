<?php
// CORS headers
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Shopify credentials
$SHOPIFY_STORE_URL = "https://honortest.myshopify.com";
$SHOPIFY_ACCESS_TOKEN = getenv("SHOPIFY_ACCESS_TOKEN");
$SHOPIFY_API_VERSION = "2024-04";

// Leer JSON de entrada
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

// Email desde Paymentez
$email = $tx['user']['email'] ?? 'sin_email@honorstore.ec';

// Shopify order_id real
$order_id = $dev_reference;
$gid = "gid://shopify/Order/{$order_id}";

// Enviar correo
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
    $mail->Username   = getenv('SMTP_USER');
    $mail->Password   = getenv('SMTP_PASS');
    $mail->SMTPSecure = 'tls';
    $mail->Port       = 587;
    $mail->setFrom('no-reply@honorstore.ec', 'HonorStore');
    $mail->CharSet = 'UTF-8';
    $mail->addAddress(filter_var($email, FILTER_VALIDATE_EMAIL) ? $email : 'backup@honorstore.ec');
    $mail->isHTML(true);
    $mail->Subject = "📟 Transacción: {$estado_final}";
    $mail->Body = "<h2>📄 Detalles de la transacción</h2>
        <p><strong>Nro de orden:</strong> {$order_id}</p>
        <p><strong>Valor:</strong> \${$amount}</p>
        <p><strong>Estado final:</strong> {$estado_final}</p>";
    $mail->send();
    $correo_enviado = true;
} catch (Exception $e) {
    $correo_enviado = false;
}

// Ejecutar mutación GraphQL
function ejecutarMutacionShopify($query) {
    global $SHOPIFY_STORE_URL, $SHOPIFY_ACCESS_TOKEN;
    $url = "$SHOPIFY_STORE_URL/admin/api/2024-01/graphql.json";
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(["query" => $query]));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Content-Type: application/json",
        "X-Shopify-Access-Token: {$SHOPIFY_ACCESS_TOKEN}"
    ]);
    $res = curl_exec($ch);
    curl_close($ch);
    return json_decode($res, true);
}

$shopify_notificado = false;
if ($estado_final === 'SUCCESS') {
    $query = "mutation { orderMarkAsPaid(input: { id: \"{$gid}\" }) { order { id displayFinancialStatus } userErrors { field message } } }";
    $res = ejecutarMutacionShopify($query);
    $shopify_notificado = empty($res['data']['orderMarkAsPaid']['userErrors']);
} else {
    $query = "mutation { orderCancel(orderId: \"{$gid}\", refund: false, restock: true, reason: CUSTOMER, notifyCustomer: false) { job { id done } orderCancelUserErrors { field message code } } }";
    $res = ejecutarMutacionShopify($query);
    $shopify_notificado = empty($res['data']['orderCancel']['orderCancelUserErrors']);
}

// Reenviar payload a webhook.site para pruebas
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
