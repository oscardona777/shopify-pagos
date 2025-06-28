<?php
// nuvei-callback-test.php

header('Content-Type: application/json');

// 1. Capturar JSON crudo y guardar
$inputJSON = file_get_contents("php://input");
file_put_contents("callback_debug_log.txt", date("Y-m-d H:i:s") . "\n" . $inputJSON . "\n\n", FILE_APPEND);

// 2. Decodificar el JSON
$input = json_decode($inputJSON, true);

// 3. Mostrar el contenido recibido (temporal)
http_response_code(200);
echo json_encode(['success' => true, 'raw_input' => $input]);
