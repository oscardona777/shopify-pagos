<?php
define('PAYMENTEZ_APP_CODE', 'TESTECUADORSTG-EC-CLIENT');
define('PAYMENTEZ_APP_KEY', 'd4pUmVHgVpw2mJ66rWwtfWaO2bAWV6');

$timestamp = time();
$hash = hash_hmac('sha256', $timestamp, PAYMENTEZ_APP_KEY);
$token = base64_encode(PAYMENTEZ_APP_CODE . ";" . $timestamp . ";" . $hash);

echo "Token generado:<br><pre>$token</pre>";
?>
