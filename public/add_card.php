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
    // Inicializa el SDK con tus credenciales
    const pg_sdk = new PaymentGateway(
      "<?php echo PAYMENTEZ_SANDBOX ? 'stg' : 'prod'; ?>",
      "<?php echo PAYMENTEZ_APP_CODE; ?>",
      "<?php echo PAYMENTEZ_APP_KEY; ?>"
    );

    // Datos necesarios para la tokenización
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

    // Genera el formulario de tarjeta en el contenedor #card-form
    pg_sdk.generate_tokenize(tokenizeData, '#card-form', onTokenizeResponse, onFormIncomplete);

    // Botón para ejecutar la tokenización
    document.getElementById('save-card-btn').addEventListener('click', function (e) {
      e.preventDefault();
      pg_sdk.tokenize(); // ejecuta la tokenización de los datos ingresados
    });

    // Callback exitoso
    function onTokenizeResponse(response) {
      if (response.card && response.card.token) {
        console.log("✅ Tarjeta tokenizada:", response);
        alert("Token generado: " + response.card.token);
      } else {
        console.warn("⚠️ Respuesta sin token:", response);
        alert("No se pudo obtener el token.");
      }
    }

    // Callback de error o formulario incompleto
    function onFormIncomplete(error) {
      console.error("❌ Formulario incompleto o inválido:", error);
      alert("Formulario incompleto o con errores.");
    }
  </script>
</body>
</html>
