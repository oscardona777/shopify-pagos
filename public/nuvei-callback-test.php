<?php
include 'config.php';
header('Content-Type: application/json');

// Leer contenido crudo
$input = file_get_contents("php://input");

// Guardar en log
file_put_contents("callback_log.txt", date("Y-m-d H:i:s") . "\n" . $input . "\n\n", FILE_APPEND);

// Confirmar recepción
echo json_encode(["success" => true]);
?>
