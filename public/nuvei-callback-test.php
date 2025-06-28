<?php
// 👉 Este archivo recibe la notificación de Paymentez (Nuvei) y valida la autenticidad del stoken

// Tu APP KEY de servidor directamente embebida
$server_app_key = '67vVmLALRrbSaQHiEer40gjb49peos';

// 1. Recibir y decodificar JSON crudo
$inputJSON = file_get_contents('php://input');
$input = json_decode($inputJSON, true);

// 2. Verificar que existan los campos clave
if (!isset($input['transaction'], $input['user'], $input['transaction']['stoken'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Estructura de datos inválida.']);
    exit;
}

// 3. Extraer datos para validación
$transaction = $input['transaction'];
$user = $input['user'];
$stoken_recibido = $transaction['stoken'];
$transaction_id = $transaction['id'];
$app_code = $transaction['application_code'];
$user_id = $user['id'];

// 4. Calcular stoken esperado
$stoken_calculado = md5("{$transaction_id}_{$app_code}_{$user_id}_{$server_app_key}");

// 5. Comparar stoken recibido con el calculado
if ($stoken_recibido !== $stoken_calculado) {
    http_response_code(203); // ❌ stoken inválido
    echo json_encode(['success' => false, 'error' => 'stoken inválido']);
    exit;
}

// 6. Aquí podrías almacenar en base de datos, procesar la transacción, etc.

// 7. Respuesta exitosa
http_response_code(200);
header('Content-Type: application/json');
echo json_encode(['success' => true]);
?>
