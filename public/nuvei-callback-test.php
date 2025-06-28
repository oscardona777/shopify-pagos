<?php
// nuvei-callback-test.php

header('Content-Type: application/json');

// 🔐 APP KEY del servidor (reemplaza con la tuya si cambia)
$server_app_key = '67vVmLALRrbSaQHiEer40gjb49peos';

// 1. Recibir y decodificar el JSON
$inputJSON = file_get_contents('php://input');
$input = json_decode($inputJSON, true);

// 2. Verificar estructura esperada
if (!isset($input['transaction'], $input['user'], $input['transaction']['stoken'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Estructura inválida']);
    exit;
}

// 3. Extraer valores necesarios
$transaction     = $input['transaction'];
$user            = $input['user'];
$card            = isset($input['card']) ? $input['card'] : null;

$transaction_id  = $transaction['id'] ?? '';
$app_code        = $transaction['application_code'] ?? '';
$user_id         = $user['id'] ?? '';
$stoken_recibido = $transaction['stoken'] ?? '';

// 4. Validar stoken
$stoken_calculado = md5("{$transaction_id}_{$app_code}_{$user_id}_{$server_app_key}");

if ($stoken_recibido !== $stoken_calculado) {
    http_response_code(203);
    echo json_encode(['success' => false, 'error' => 'stoken inválido']);
    exit;
}

// 5. Mostrar todos los parámetros clave (debug)
$response = [
    'transaction' => [
        'status'             => $transaction['status'] ?? '',
        'order_description'  => $transaction['order_description'] ?? '',
        'authorization_code' => $transaction['authorization_code'] ?? '',
        'status_detail'      => $transaction['status_detail'] ?? '',
        'date'               => $transaction['date'] ?? '',
        'message'            => $transaction['message'] ?? '',
        'id'                 => $transaction['id'] ?? '',
        'dev_reference'      => $transaction['dev_reference'] ?? '',
        'carrier_code'       => $transaction['carrier_code'] ?? '',
        'amount'             => $transaction['amount'] ?? '',
        'paid_date'          => $transaction['paid_date'] ?? '',
        'installments'       => $transaction['installments'] ?? '',
        'ltp_id'             => $transaction['ltp_id'] ?? '',
        'stoken'             => $transaction['stoken'] ?? '',
        'application_code'   => $transaction['application_code'] ?? '',
        'terminal_code'      => $transaction['terminal_code'] ?? '',
        'payment_method_type'=> $transaction['payment_method_type'] ?? '',
    ],
    'user' => [
        'id'    => $user['id'] ?? '',
        'email' => $user['email'] ?? '',
    ],
    'card' => $card ? [
        'bin'           => $card['bin'] ?? '',
        'holder_name'   => $card['holder_name'] ?? '',
        'type'          => $card['type'] ?? '',
        'number'        => $card['number'] ?? '',
        'origin'        => $card['origin'] ?? '',
        'fiscal_number' => $card['fiscal_number'] ?? '',
    ] : null
];

// 6. Guardar log (opcional)
file_put_contents("callback_nuvei_log.txt", date("Y-m-d H:i:s") . "\n" . json_encode($response, JSON_PRETTY_PRINT) . "\n\n", FILE_APPEND);

// 7. Confirmar recepción a Paymentez
http_response_code(200);
echo json_encode(['success' => true, 'message' => 'Webhook recibido y verificado', 'data' => $response]);
?>
