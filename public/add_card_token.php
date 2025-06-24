
<?php
// config.php
$client_app_code = "TESTECUADORSTG-EC-CLIENT";
$client_app_key  = "d4pUmVHgVpw2mJ66rWwtfWaO2bAWV6";
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Agregar tarjeta</title>
  <script src="https://cdn.paymentez.com/ccapi/sdk/payment_sdk_stable.min.js"></script>
  <style>
    body { font-family: Arial; padding: 20px; }
    .form-group { margin-bottom: 15px; }
    button { padding: 10px 20px; background: #007BFF; color: white; border: none; cursor: pointer; }
    #form-container { margin-top: 20px; max-width: 400px; }
    #result { margin-top: 20px; white-space: pre-wrap; }
  </style>
</head>
<body>
<h2>Agregar tarjeta</h2>

<div id="form-container"></div>
<button id="add-card-btn">Agregar tarjeta</button>

<div id="result"></div>

<script>
  const APP_CODE = "<?php echo $client_app_code; ?>";
  const APP_KEY = "<?php echo $client_app_key; ?>";

  const paymentez = new PaymentGateway('stg', APP_CODE, APP_KEY);

  paymentez.generate_tokenize(
    {
      user_id: "user-001",
      email: "cliente@ejemplo.com",
      cookie: true
    },
    '#form-container',
    function (response) {
      document.getElementById("result").innerText = "✅ Token generado: " + response.card.token;

      fetch('verify_card.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ card_token: response.card.token })
      })
      .then(res => res.json())
      .then(data => {
        document.getElementById("result").innerText += "\n\n🔁 Verificación:\n" + JSON.stringify(data, null, 2);
      })
      .catch(err => console.error('❌ Error en verificación:', err));
    },
    function (error) {
      console.error('❌ Error en generación de token:', error);
      document.getElementById("result").innerText = "❌ Error al generar token.";
    }
  );

  document.getElementById("add-card-btn").addEventListener("click", function () {
    paymentez.tokenize();
  });
</script>
</body>
</html>
