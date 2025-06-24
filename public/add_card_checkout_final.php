<?php
// Credenciales del entorno sandbox
$client_app_code = "TESTECUADORSTG-EC-CLIENT";
$client_app_key  = "d4pUmVHgVpw2mJ66rWwtfWaO2bAWV6";

// Generar Auth-Token
$timestamp = time();
$token_hash = hash("sha256", $client_app_key . $timestamp);
$auth_token = base64_encode($client_app_code . ";" . $timestamp . ";" . $token_hash);

// Estructura de datos para la creación del checkout
$data = array(
  "user" => array(
    "id" => "user_checkout_001",
    "email" => "cliente@example.com",
    "country" => "EC"
  ),
  "order" => array(
    "amount" => 1.00,
    "description" => "Validación de tarjeta",
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

$payload = json_encode($data);

// Encabezados
$headers = array(
  "Content-Type: application/json",
  "Auth-Token: " . $auth_token,
  "Auth-Timestamp: " . $timestamp
);

// Enviar solicitud cURL
$ch = curl_init("https://ccapi-stg.paymentez.com/v2/transaction/init_checkout");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
$response = curl_exec($ch);
$http_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

// Verificar respuesta
$checkout_url = "";
if ($http_status == 200) {
  $result = json_decode($response, true);
  if (!empty($result["checkout_url"])) {
    $checkout_url = $result["checkout_url"];
  } else {
    error_log("❌ No se recibió checkout_url: " . $response);
  }
} else {
  error_log("❌ HTTP $http_status - Respuesta: " . $response);
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Agregar Tarjeta</title>
  <style>
    body { font-family: Arial, sans-serif; padding: 20px; }
    .btn {
      background: #007bff;
      color: white;
      padding: 10px 20px;
      border: none;
      cursor: pointer;
      font-size: 16px;
      border-radius: 5px;
    }
    .btn:hover { background: #0056b3; }
    #error-msg { color: red; margin-top: 20px; }
  </style>
</head>
<body>

  <h3>Agregar tarjeta vía Checkout</h3>
  <button id="checkout-btn" class="btn">Agregar tarjeta</button>
  <div id="error-msg"></div>

  <script>
    function showError(msg) {
      console.error("❌ Error:", msg);
      document.getElementById("error-msg").textContent = msg;
    }

    function sdkReady() {
      console.log("✅ SDK cargado correctamente");

      const url = "<?php echo $checkout_url; ?>";

      if (!url || url === "#") {
        showError("No se pudo generar el enlace de checkout.");
        return;
      }

      const btn = document.getElementById("checkout-btn");
      if (!btn) {
        showError("Botón no encontrado en el DOM.");
        return;
      }

      btn.addEventListener("click", function () {
        if (typeof openModal === "function") {
          console.log("🔔 Abriendo modal con:", url);
          openModal(url);
        } else {
          showError("La función openModal no está disponible. Verifica si el SDK cargó correctamente.");
        }
      });
    }

    function sdkFailed() {
      showError("Error al cargar el SDK de Paymentez.");
    }

    // Cargar SDK dinámicamente
    document.addEventListener("DOMContentLoaded", function () {
      const script = document.createElement("script");
      script.src = "https://cdn.paymentez.com/ccapi/sdk/payment_checkout_stable.min.js";
      script.onload = sdkReady;
      script.onerror = sdkFailed;
      document.body.appendChild(script);
    });
  </script>

</body>
</html>
