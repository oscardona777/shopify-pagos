<?php
$client_app_code = "TESTECUADORSTG-EC-CLIENT";
$client_app_key = "d4pUmVHgVpw2mJ66rWwtfWaO2bAWV6";
$timestamp = time();
$token_hash = hash('sha256', $client_app_key . $timestamp);
$auth_token = base64_encode($client_app_code . ";" . $timestamp . ";" . $token_hash);

$order_data = array(
    "user" => array(
        "id" => "user_checkout_001",
        "email" => "cliente@example.com",
        "country" => "EC"
    ),
    "order" => array(
        "amount" => 1.00,
        "description" => "Validación tarjeta via Checkout",
        "dev_reference" => "checkout_" . $timestamp,
        "installments" => 1,
        "currency" => "USD"
    ),
    "billing" => array(
        "first_name" => "Cliente",
        "last_name" => "Demo",
        "address" => "Av. Principal 123",
        "city" => "Quito",
        "zip_code" => "170101",
        "country" => "EC",
        "phone" => "+593000000000"
    )
);

$payload = json_encode($order_data);

$headers = array(
    "Content-Type: application/json",
    "Auth-Token: " . $auth_token,
    "Auth-Timestamp: " . $timestamp
);

$ch = curl_init("https://ccapi-stg.paymentez.com/v2/transaction/init_checkout");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
$response = curl_exec($ch);
$http_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

$checkout_url = "#";
if ($http_status === 200) {
    $result = json_decode($response, true);
    if (isset($result["checkout_url"])) {
        $checkout_url = $result["checkout_url"];
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Agregar Tarjeta</title>
  <style>
    body { font-family: Arial, sans-serif; padding: 20px; }
    .btn { background: #0069d9; color: white; padding: 10px 20px; border: none; cursor: pointer; font-size: 16px; }
    .btn:hover { background: #0053ba; }
    #error-msg { color: red; margin-top: 20px; }
  </style>
</head>
<body>
  <h3>Validar Tarjeta vía Checkout</h3>
  <button id="checkout-btn" class="btn">Agregar tarjeta</button>
  <div id="error-msg"></div>

  <script src="https://cdn.paymentez.com/ccapi/sdk/payment_checkout_stable.min.js"
          onload="sdkReady()" onerror="sdkFailed()"></script>

  <script>
    var url = "<?php echo $checkout_url; ?>";

    function sdkReady() {
      console.log("✅ SDK cargado");
      document.getElementById("checkout-btn").addEventListener("click", function () {
        if (typeof openModal === "function") {
          openModal(url);
        } else {
          showError("La función openModal no está disponible.");
        }
      });
    }

    function sdkFailed() {
      showError("❌ Error al cargar el SDK de Paymentez.");
    }

    function showError(msg) {
      console.error(msg);
      document.getElementById("error-msg").textContent = msg;
    }
  </script>
</body>
</html>
