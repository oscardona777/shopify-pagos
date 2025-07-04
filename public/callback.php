<?php
// ⚙️ CORS
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

ob_start(); // evita errores de headers

// ✅ Configuración
include 'config.php';

// 🧠 Leer JSON entrante
$inputJSON = file_get_contents('php://input');
$input = json_decode($inputJSON, true);

// 🧪 Debug inicial
file_put_contents('callback_debug.log', date("Y-m-d H:i:s") . " | Payload: " . $inputJSON . PHP_EOL, FILE_APPEND);

if (!$input || !isset($input['transaction'])) {
    http_response_code(400);
    echo json_encode(["success" => false, "error" => "Transacción no recibida"]);
    exit;
}

// 📦 Datos clave
$tx = $input['transaction'];
$transaction_id = $tx['id'] ?? 'N/A';
$amount = $tx['amount'] ?? 0;
$dev_reference = $tx['dev_reference'] ?? '';
$status = strtoupper($tx['status'] ?? '');
$current_status = strtoupper($tx['current_status'] ?? $status);
$estado_final = ($current_status === 'CANCELLED') ? 'CANCELLED' : $status;
$email = $tx['user']['email'] ?? 'sin_email@honorstore.ec';
$order_id = $dev_reference;
$gid = "gid://shopify/Order/{$order_id}";

// 📧 Enviar correo estilo gracias.php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require 'PHPMailer/PHPMailer.php';
require 'PHPMailer/SMTP.php';
require 'PHPMailer/Exception.php';

$correo_enviado = false;
try {
    $mail = new PHPMailer(true);
    $mail->isSMTP();
    $mail->Host       = SMTP_HOST;
    $mail->SMTPAuth   = true;
    $mail->Username   = SMTP_USER;
    $mail->Password   = SMTP_PASS;
    $mail->SMTPSecure = 'tls';
    $mail->Port       = SMTP_PORT;
    $mail->setFrom(SMTP_FROM_EMAIL, SMTP_FROM_NAME);
    $mail->addAddress(filter_var($email, FILTER_VALIDATE_EMAIL) ? $email : 'backup@honorstore.ec');
    $mail->CharSet = 'UTF-8';
    $mail->isHTML(true);
    $mail->Subject = "📟 Transacción: {$estado_final}";

    $mensaje = $estado_final === 'APPROVED'
        ? 'Te confirmamos que tu pedido ha sido aprobado.'
        : 'Lamentamos que no hayas podido finalizar la transacción o cancelado tu pedido.';

    $svg_icono = $estado_final === 'APPROVED' ? '
        <svg viewBox="0 0 24 24" width="80" height="80">
            <circle cx="12" cy="12" r="10" fill="#2ecc71"/>
            <path d="M7 12l3 3 7-7" stroke="#fff" stroke-width="2" fill="none"/>
        </svg>'
        :
        '<svg viewBox="0 0 24 24" width="80" height="80">
            <circle cx="12" cy="12" r="10" fill="#e74c3c"/>
            <path d="M15 9l-6 6M9 9l6 6" stroke="#fff" stroke-width="2" fill="none"/>
        </svg>';

    $mail->Body = "
    <html><body style='background:#f2f2f2;padding:2rem;text-align:center;color:#333;font-family:Roboto,sans-serif'>
      <div style='background:#fff;max-width:500px;margin:auto;padding:2rem;border-radius:12px;box-shadow:0 4px 20px rgba(0,0,0,0.1)'>
        <div style='font-size:1.2rem;margin-bottom:1.2rem;font-weight:500'>Pedido #{$order_id}</div>
        <div class='icono' style='margin:1.5rem auto 1rem;'>{$svg_icono}</div>
        <div style='font-size:1rem;margin-bottom:1rem;line-height:1.5'>{$mensaje}</div>
        <div style='font-size:1rem;margin-bottom:1.5rem;line-height:1.5'>Te invitamos a visitar nuestra página Honor Store Ecuador</div>
        <a href='https://honorstore.ec' style='display:inline-block;background:#111;color:white;padding:1rem 2rem;border-radius:8px;text-decoration:none;font-weight:500;'>Continuar comprando</a>
      </div>
    </body></html>";

    $mail->send();
    $correo_enviado = true;
} catch (Exception $e) {
    file_put_contents('callback_debug.log', "Error correo: " . $e->getMessage() . PHP_EOL, FILE_APPEND);
    $correo_enviado = false;
}

// 🔁 Shopify mutación GraphQL
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
    if (curl_errno($ch)) {
        file_put_contents('callback_debug.log', "❌ Shopify curl error: " . curl_error($ch) . PHP_EOL, FILE_APPEND);
    }
    curl_close($ch);
    return json_decode($res, true);
}

$shopify_notificado = false;
if ($estado_final === 'SUCCESS' || $estado_final === 'APPROVED') {
    $query = "mutation { orderMarkAsPaid(input: { id: \"{$gid}\" }) { order { id displayFinancialStatus } userErrors { field message } } }";
    $res = ejecutarMutacionShopify($query);
    $shopify_notificado = empty($res['data']['orderMarkAsPaid']['userErrors']);
} elseif ($estado_final === 'CANCELLED') {
    $query = "mutation { orderCancel(orderId: \"{$gid}\", refund: false, restock: true, reason: CUSTOMER, notifyCustomer: false) { job { id done } orderCancelUserErrors { field message code } } }";
    $res = ejecutarMutacionShopify($query);
    $shopify_notificado = empty($res['data']['orderCancel']['orderCancelUserErrors']);
}

// 📤 Reenviar payload a webhook.site
$callback_url = 'https://webhook.site/c9afb9c5-af47-4a91-a01b-83503d284e28';
$payload_modificado = $input;
$payload_modificado['transaction']['final_status'] = $estado_final;

$ch = curl_init($callback_url);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload_modificado));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
$response_webhook = curl_exec($ch);
if (curl_errno($ch)) {
    file_put_contents('callback_debug.log', "❌ Webhook curl error: " . curl_error($ch) . PHP_EOL, FILE_APPEND);
}
curl_close($ch);

// ✅ Respuesta final
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
