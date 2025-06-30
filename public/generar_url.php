<?php
// generar_url.php

include 'config.php'; // solo si defines la clave ahí

$key = 'rV9!uZx4#WqLp2T7@Nc3$MfG8YjB6sAh'; // clave secreta del servidor (32 caracteres)

if (
  empty($_POST['user_id']) || 
  empty($_POST['email']) || 
  empty($_POST['amount']) || 
  empty($_POST['description'])
) {
  header("Location: https://honorstore.ec");
  exit;
}

$data = [
  'user_id' => $_POST['user_id'],
  'email' => $_POST['email'],
  'amount' => $_POST['amount'],
  'description' => $_POST['description']
];

$json = json_encode($data, JSON_UNESCAPED_UNICODE);
$iv = openssl_random_pseudo_bytes(16);
$encrypted = openssl_encrypt($json, 'AES-256-CBC', $key, OPENSSL_RAW_DATA, $iv);
$payload = base64_encode($iv . $encrypted);

// Redirigir a index.php con datos cifrados
$redir = 'index.php?data=' . urlencode($payload);
header("Location: $redir");
exit;
