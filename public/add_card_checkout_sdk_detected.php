<?php
// Credenciales de CLIENTE
$client_app_code = "TESTECUADORSTG-EC-CLIENT";
$client_app_key = "d4pUmVHgVpw2mJ66rWwtfWaO2bAWV6";

// Timestamp y autenticación
$timestamp = time();
$token_hash = hash('sha256', $client_app_key . $timestamp);
$auth_token = base64_encode("{$client_app_code};{$timestamp};{$token_hash}");

// JSON con orden y usuario
$data = [
    "user" => [
        "id" => "user_addcard_001",
        "email" => "cliente@example.com",
        "country" => "EC"
    ],
    "order" => [
        "amount" => 1.00,
        "description" => "Validación tarjeta con Checkout",
        "dev_reference" => "ref_addcard_" . time(),
        "installments" => 1,
        "currency" => "USD"
    ],
    "billing" => [
        "first_name" => "Cliente",
        "last_name" => "Demo",
        "address" => "Av. Demo 123",
        "city" => "Quito",
        "zip_code" => "170101",
        "country" => "EC",
        "phone" => "+593000000000"
    ]
];

// Headers
$headers = [
    "Content-Type: application/json",
    "Auth-Token: {$auth_token}",
    "Auth-Timestamp: {$timestamp}"
];

// cURL
$ch = curl_init("https://ccapi-stg.paymentez.com/v2/transaction/init_checkout");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

$response = curl_exec($ch);
$http_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

$checkout_url = "#";
if ($http_status == 200) {
    $result = json_decode($response, true);
    $checkout_url = $result["checkout_url"] ?? "#";
}
?>

<!DOCTYPE html>
<html lang='es'>
<head>
  <meta charset='UTF-8'>
  <title>Agregar Tarjeta</title>
  <style>
    body { font-family: Arial, sans-serif; padding: 20px; }
    .btn { background: #0069d9; color: white; padding: 10px 20px; border: none; cursor: pointer; font-size: 16px; }
    .btn:hover { background: #0053ba; }
  </style>
</head>
<body>
  <h3>Haga clic para agregar su tarjeta</h3>
  <button class='btn' id='open-btn'>Agregar tarjeta</button>

  <script src='https://cdn.paymentez.com/ccapi/sdk/payment_checkout_stable.min.js' onload='sdkLoaded()' onerror='sdkError()'></script>
  <script>
    let checkoutUrl = "<?php echo $checkout_url; ?>";

    function sdkLoaded() {
      console.log("✅ SDK de Paymentez cargado correctamente.");
      document.getElementById("open-btn").addEventListener("click", function () {
        if (typeof openModal === "function") {
          openModal(checkoutUrl);
        } else {
          console.error("❌ openModal no está definido.");
          alert("El SDK se cargó pero la función openModal no está disponible.");
        }
      });
    }

    function sdkError() {
      console.error("❌ No se pudo cargar el SDK de Paymentez desde el CDN.");
      alert("No se pudo cargar el SDK de Paymentez.");
    }
  </script>
</body>
</html>
