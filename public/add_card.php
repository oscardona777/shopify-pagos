<?php include 'config.php'; ?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Agregar Tarjeta</title>
  <script src="https://cdn.paymentez.com/ccapi/sdk/payment_sdk_stable.min.js" charset="UTF-8"></script>
</head>
<body>
  <h2>Agregar nueva tarjeta</h2>

  <!-- Formulario generado dinámicamente -->
  <div id="card-form"></div>

  <!-- Botón para guardar -->
  <button id="save-card-btn">Guardar tarjeta</button>

  <script>
    // Inicializa el SDK con tus credenciales
    const pg_sdk = new PaymentGateway(
      "<?php echo PAYMENTEZ_SANDBOX ? 'stg' : 'prod'; ?>",
      "<?php echo PAYMENTEZ_APP_CODE; ?>",
      "<?php echo PAYMENTEZ_APP_KEY; ?>"
    );

    // Configuración del formulario
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

    // Renderiza los campos del formulario
    pg_sdk.generate_tokenize(tokenizeData, '#card-form', onTokenizeResponse, onFormIncomplete);

    // Evento del botón
    document.getElementById('save-card-btn').addEventListener('click', function (e) {
      e.preventDefault();
      pg_sdk.tokenize();
    });

    // Callback cuando se genera el token
    function onTokenizeResponse(response) {
      console.log("📦 Respuesta del SDK:", response);

      if (response && response.card && typeof response.card.token === 'string') {
        const token = response.card.token;
        console.log("✅ Token generado:", token);
        alert("Token generado exitosamente: " + token);
      } else {
        console.warn("❌ Token no recibido o estructura inválida:", response);
        alert("No se generó un token válido.");
      }
    }

    // Callback si el formulario está incompleto o con errores
    function onFormIncomplete(error) {
      console.warn("⚠️ Formulario incompleto:", error);
      alert("Por favor completa correctamente los campos.");
    }
  </script>
</body>
</html>
