<?php
// Webhook que procesa notificaciones de Paymentez

header('Content-Type: application/json');

// 🔐 Tu APP KEY servidor para validar stoken
$server_app_key = '67vVmLALRrbSaQHiEer40gjb49peos';

// 1. Recibir y decodificar JSON
$inputJSON = file_get_contents('php://input');
$input = json_decode($inputJSON, true);

// 2. Detectar el tipo de estructura recibida
if (isset($input['transaction'], $input['user'], $input['transaction']['stoken'])) {
    // 👉 Estructura tipo Webhook de transacción

    $transaction = $input['transaction'];
    $user = $input['user'];
    $stoken_recibido = $transaction['stoken'];
    $transaction_id = $transaction['id'];
    $app_code = $transaction['application_code'];
    $user_id = $user['id'];

    // Calcular stoken esperado
    $stoken_calculado = md5("{$transaction_id}_{$app_code}_{$user_id}_{$server_app_key}");

    // Comparar
    if ($stoken_recibido !== $stoken_calculado) {
        http_response_code(203);
        echo json_encode(['success' => false, 'error' => 'stoken inválido']);
        exit;
    }

    // Aquí puedes procesar la transacción si es válida
    http_response_code(200);
    echo json_encode(['success' => true]);

} elseif (isset($input['success'], $input['data']['order']['id'])) {
    // 👉 Estructura tipo respuesta a LinkToPay (init_order)

    // Puedes almacenar esta respuesta o simplemente confirmarla
    http_response_code(200);
    echo json_encode(['received' => true, 'type' => 'ltp_response']);

} else {
    // ❌ Estructura no reconocida
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'estructura no reconocida']);
}
?>
