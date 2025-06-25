
<?php
define('PAYMENTEZ_APP_CODE', 'TESTECUADORSTG-EC-CLIENT');
define('PAYMENTEZ_APP_KEY', 'd4pUmVHgVpw2mJ66rWwtfWaO2bAWV6X');
define('PAYMENTEZ_SANDBOX', true);

/**
 * Retorna los headers necesarios para autenticación en llamadas desde PHP (servidor)
 */
function get_headers_auth() {
    $timestamp = time();
    $token_string = PAYMENTEZ_APP_CODE . ";" . $timestamp . ";" . hash_hmac('sha256', $timestamp, PAYMENTEZ_APP_KEY);
    $auth_token = base64_encode($token_string);
    return [
        "Auth-Token: " . $auth_token,
        "Content-Type: application/json"
    ];
}

/**
 * Devuelve la URL base de la API según el entorno (sandbox o producción)
 */
function api_url($endpoint) {
    $base_url = PAYMENTEZ_SANDBOX
        ? "https://ccapi-stg.paymentez.com"
        : "https://ccapi.paymentez.com";
    return $base_url . $endpoint;
}
?>
