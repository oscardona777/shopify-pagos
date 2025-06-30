<?php
header('Content-Type: application/json');

define('PAYMENTEZ_APP_CODE', 'TESTECUADORSTG-EC-CLIENT');
define('PAYMENTEZ_APP_KEY', 'd4pUmVHgVpw2mJ66rWwtfWaO2bAWV6');

// 🔐 CREDENCIALES SERVIDOR (para PHP backend)
define('PAYMENTEZ_SERVER_APP_CODE', 'TESTECUADORSTG-EC-SERVER');
define('PAYMENTEZ_SERVER_APP_KEY',  '67vVmLALRrbSaQHiEer40gjb49peos');

// 🧪 Usa entorno sandbox = true / producción = false
define('PAYMENTEZ_SANDBOX', true);
define('REDIRECT_URL', 'https://honorstore.ec'); // Redirección si no hay datos válidos

/**
 * Retorna los headers necesarios para autenticación en llamadas desde PHP (cliente)
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

function get_headers_auth_server() {
    $server_application_code = PAYMENTEZ_SERVER_APP_CODE;
    $server_app_key = PAYMENTEZ_SERVER_APP_KEY ;
    $date = new DateTime();
    $unix_timestamp = $date->getTimestamp();
    $uniq_token_string = $server_app_key . $unix_timestamp;
    $uniq_token_hash = hash('sha256', $uniq_token_string);
    $auth_token_server = base64_encode($server_application_code . ";" . $unix_timestamp . ";" . $uniq_token_hash);
    return [
        "Auth-Token: " . $auth_token_server,
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

//
// VARIABLES ADICIONALES PARA EL PROYECTO (💡 NO TOCAR FUNCIONES ARRIBA)
//

// 💳 Transacción
$DEFAULT_AMOUNT = 18.4;
$DEFAULT_TAX_PERCENTAGE = 0.15;
$DEFAULT_CURRENCY = "USD";
$DEFAULT_DESCRIPTION = "ingrese el modelo";
$DEFAULT_INSTALLMENTS_TYPE = 0;
$DEFAULT_INSTALLMENTS = 1;

// 👤 Usuario (modo pruebas)
$DEFAULT_USER_ID = "ingrese un usuario ABC123";
$DEFAULT_USER_EMAIL = "ingresa un correo electronico";

// 🌎 Idioma
$DEFAULT_LOCALE = "es";

// 🔁 URLs
$SUCCESS_URL = "https://shopify-pagos.onrender.com/nuvei-callback-test.php";
$FAILURE_URL = "https://shopify-pagos.onrender.com/nuvei-callback-test.php";
$PENDING_URL = "https://shopify-pagos.onrender.com/nuvei-callback-test.php";
$REVIEW_URL  = "https://shopify-pagos.onrender.com/nuvei-callback-test.php";
$CALLBACK_URL = "https://shopify-pagos.onrender.com/nuvei-callback-test.php";

// 📊 Analytics (opcional)
$USE_GOOGLE_ANALYTICS = true;
$GA_TRACKING_ID = "G-XXXXXXXXXX";

// 🛒 Shopify (opcional)
$SHOPIFY_STORE_URL = "https://tutienda.myshopify.com/";
?>
