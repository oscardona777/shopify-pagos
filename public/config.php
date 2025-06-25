<?php
define('PAYMENTEZ_APP_CODE', 'TU_APP_CODE_SANDBOX');
define('PAYMENTEZ_APP_KEY', 'TU_APP_KEY_SANDBOX');
define('PAYMENTEZ_SANDBOX', true);

function get_headers_auth() {
    $timestamp = time();
    $token_string = PAYMENTEZ_APP_CODE . ";" . $timestamp . ";" . hash_hmac('sha256', $timestamp, PAYMENTEZ_APP_KEY);
    $auth_token = base64_encode($token_string);
    return [
        "Auth-Token: " . $auth_token,
        "Content-Type: application/json"
    ];
}

function api_url($endpoint) {
    return (PAYMENTEZ_SANDBOX ? "https://ccapi-stg.paymentez.com" : "https://ccapi.paymentez.com") . $endpoint;
}
