<?php include 'config.php'; ?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Agregar Tarjeta y Verificar</title>
  <script src="https://cdn.paymentez.com/ccapi/sdk/payment_sdk_stable.min.js" charset="UTF-8"></script>
</head>
<body>
  <h2>Agregar nueva tarjeta</h2>

  <!-- Contenedor del formulario dinámico de Paymentez -->
  <div id="card-form"></div>

  <!-- Botón para tokenizar -->
  <button id="save-card-btn">Guardar tarjeta</button>

  <script>
    // Inicializa el SDK de Paymentez
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

    // Genera el formulario dinámico
    pg_sdk.generate_tokenize(tokenizeData, '#card-form', onTokenizeResponse, onFormIncomplete);

    document.getElementById('save-card-btn').addEventListener('click', function (e) {
      e.preventDefault();
      pg_sdk.tokenize(); // dispara el proceso de tokenización
    });

    function onTokenizeResponse(response) {
      console.log("📦 Respuesta del SDK:", response);

      if (response && response.card && typeof response.card.token === 'string') {
        const token = response.card.token;
        alert("✅ Token generado: " + token);

        // Enviar a verify_card.php vía POST
        fetch('verify_card.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
          body: 'token=' + encodeURIComponent(token)
        })
        .then(res => res.json())
        .then(data => {
          console.log('🔍 Verificación de tarjeta:', data);
          if (data.verify?.success) {
            alert('✅ Verificación exitosa: ' + data.verify.message);
          } else {
            alert('⚠️ Verificación fallida o parcial');
          }
        })
        .catch(error => {
          console.error('❌ Error verificando tarjeta:', error);
          alert('Error verificando tarjeta.');
        });

      } else {
        console.warn("❌ Token no recibido o estructura inesperada:", response);
        alert("No se generó un token válido.");
      }
    }

    function onFormIncomplete(error) {
      console.warn("⚠️ Formulario incompleto:", error);
      alert("Faltan datos o hay errores.");
    }
  </script>
</body>
</html>
