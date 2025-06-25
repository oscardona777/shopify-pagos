<?php
define('PAYMENTEZ_APP_CODE', 'TESTECUADORSTG-EC-CLIENT');
define('PAYMENTEZ_APP_KEY', 'd4pUmVHgVpw2mJ66rWwtfWaO2bAWV6X');
define('PAYMENTEZ_SERVER_APP_CODE', 'TESTECUADORSTG-EC-SERVER');
define('PAYMENTEZ_SERVER_APP_KEY', '67vVmLALRrbSaQHiEer40gjb49peos');
define('PAYMENTEZ_SANDBOX', true); // false en producción

function get_headers_auth() {
    $timestamp = time();
    $auth_token = base64_encode(PAYMENTEZ_SERVER_APP_CODE . ";" . $timestamp . ";" . hash_hmac('sha256', $timestamp, PAYMENTEZ_SERVER_APP_KEY));
    return [
        'Auth-Token: ' . $auth_token,
        'Content-Type: application/json'
    ];
}

function api_url($endpoint) {
    return (PAYMENTEZ_SANDBOX ? "https://ccapi-stg.paymentez.com" : "https://ccapi.paymentez.com") . $endpoint;
}
?>
