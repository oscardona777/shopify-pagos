<?php // === add_card_token.php === ?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Agregar tarjeta</title>
  <script src="https://cdn.paymentez.com/ccapi/sdk/payment_sdk_stable.min.js"></script>
  <style>
    body { font-family: Arial; padding: 20px; }
    #form-container { margin-top: 20px; max-width: 400px; }
    .form-group { margin-bottom: 10px; }
    button { padding: 10px 20px; background: #1a73e8; color: white; border: none; cursor: pointer; }
    #result { margin-top: 20px; white-space: pre-wrap; }
  </style>
</head>
<body>

<h2>Agregar tarjeta</h2>

<div id="form-container"></div>
<button id="add-card-btn">Agregar tarjeta</button>
<div id="result"></div>

<script>
  const APP_CODE = "<?php include 'config.php'; echo $client_app_code; ?>";
  const APP_KEY = "<?php echo $client_app_key; ?>";

  const pg = new PaymentGateway('stg', APP_CODE, APP_KEY);

  const data = {
    user_id: 'user-001',
    email: 'cliente@example.com',
    cookie: true
  };

  const formContainer = '#form-container';

  pg.generate_tokenize(data, formContainer, function(response) {
    console.log('✅ Token generado:', response);
    document.getElementById("result").innerHTML = "Token generado: " + response.card.token;

    fetch('verify_card.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ card_token: response.card.token })
    })
    .then(res => res.json())
    .then(data => {
      console.log('🔁 Respuesta verificación:', data);
      document.getElementById("result").innerHTML += '\n\nVerificación: ' + JSON.stringify(data, null, 2);
    })
    .catch(err => console.error("❌ Error en verificación:", err));

  }, function(error) {
    console.error('❌ Formulario incompleto:', error);
    document.getElementById("result").innerHTML = "Formulario incompleto o error.";
  });

  document.getElementById("add-card-btn").addEventListener("click", function() {
    pg.tokenize();
  });
</script>

</body>
</html>