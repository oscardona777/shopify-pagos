<?php
header('Content-Type: application/json');
include 'config.php';

header('Content-Type: application/json');

// 💰 Monto e impuesto
$amount = isset($_POST['amount']) ? floatval($_POST['amount']) : $DEFAULT_AMOUNT;
$tax_percentage = $DEFAULT_TAX_PERCENTAGE;
$vat = floatval(number_format(round(($amount / (1 + $tax_percentage)), 2) * $tax_percentage, 2, '.', ''));
$taxable_amount = round($amount - $vat, 2);

// 📦 Datos del pedido
$order = array(
    "amount" => $amount,
    "vat" => $vat,
    "description" => $_POST['description'] ?? $DEFAULT_DESCRIPTION,
    "dev_reference" => uniqid("ORDER_"),
    "installments_type" => $DEFAULT_INSTALLMENTS_TYPE,
    "installments" => $DEFAULT_INSTALLMENTS,
    "currency" => $DEFAULT_CURRENCY
);

// 👤 Usuario
$user = array(
    "id" => $_POST['user_id'] ?? $DEFAULT_USER_ID,
    "email" => $_POST['email'] ?? $DEFAULT_USER_EMAIL
);

// 🌎 Idioma
$locale = $_POST['locale'] ?? $DEFAULT_LOCALE;

// ⚙️ Configuración de URLs de redirección
$conf = array(
    "success_url" => $SUCCESS_URL,
    "failure_url" => $FAILURE_URL,
    "pending_url" => $PENDING_URL,
    "review_url"  => $REVIEW_URL,
    "callback_url" => $CALLBACK_URL,
    "expiration_minutes" => 15
);

// 🧾 Payload final
$payload = array(
    "locale" => $locale,
    "order" => $order,
    "user" => $user,
    "conf" => $conf
);

// 🌐 Endpoint
$url = api_url('/v2/transaction/init_reference/');
$headers = get_headers_auth_server();

// 🚀 Ejecutar solicitud
$ch = curl_init($url);
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
curl_setopt($ch, CURLOPT_POST, true);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

// 📤 Devolver respuesta al frontend
$response_data = json_decode($response, true);

if ($http_code === 200 && isset($response_data['reference'])) {
    echo json_encode(array(
        "success" => true,
        "reference" => $response_data['reference'],
        "checkout_url" => $response_data['checkout_url'],
        "dev_reference" => $order["dev_reference"]
    ));
} else {
    echo json_encode(array(
        "success" => false,
        "error" => isset($response_data['detail']) ? $response_data['detail'] : 'Error inesperado al generar la referencia',
        "http_code" => $http_code,
        "raw_response" => $response
    ));
}
?>
