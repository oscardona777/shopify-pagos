<?php include 'config.php'; ?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Agregar Tarjeta - Paymentez</title>
  <script src="https://cdn.paymentez.com/ccapi/sdk/payment_sdk_stable.min.js" charset="UTF-8"></script>
</head>
<body>
  <h2>Agregar nueva tarjeta</h2>

  <!-- Contenedor donde el SDK insertará los campos -->
  <div id="card-form"></div>

  <!-- Botón para enviar el formulario -->
  <button id="save-card-btn">Guardar tarjeta</button>

<script>
  const pg_sdk = new PaymentGateway(
    "<?php echo PAYMENTEZ_SANDBOX ? 'stg' : 'prod'; ?>",
    "<?php echo PAYMENTEZ_APP_CODE; ?>",
    "<?php echo PAYMENTEZ_APP_KEY; ?>"
  );

  const tokenizeData = {
    locale: 'es',
    user: {
      id: 'user123',
      email: 'usuario@example.com'
    },
    configuration: {
      default_country: 'ECU'
    }
  };

  pg_sdk.generate_tokenize(tokenizeData, '#card-form', onTokenizeResponse, onFormIncomplete);

  document.getElementById('save-card-btn').addEventListener('click', function (e) {
    e.preventDefault();
    pg_sdk.tokenize();
  });

  function onTokenizeResponse(response) {
    console.log("Token generado:", response);
    alert("Token: " + response.card.token);
  }

  function onFormIncomplete(error) {
    console.warn("Formulario incompleto:", error);
    alert("Faltan datos o hay errores.");
  }
</script>
  
</body>
</html>