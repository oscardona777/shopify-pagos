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
      if (response.card && response.card.token) {
        console.log("✅ Token generado:", response.card.token);
        alert("Token generado: " + response.card.token);

        // Llamada a verify_card.php
        fetch('verify_card.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
          body: 'token=' + encodeURIComponent(response.card.token)
        })
        .then(res => res.json())
        .then(data => {
          console.log('🔍 Resultado de verificación:', data);
          if (data.verify?.success) {
            alert('✅ Verificación exitosa: ' + data.verify.message);
          } else {
            alert('⚠️ Verificación fallida o parcial');
          }
        })
        .catch(error => {
          console.error('❌ Error en verificación:', error);
          alert('Error verificando tarjeta.');
        });

      } else {
        alert("No se pudo obtener el token de la tarjeta.");
      }
    }

    function onFormIncomplete(error) {
      console.warn("Formulario incompleto:", error);
      alert("Faltan datos o hay errores.");
    }
  </script>
</body>
</html>
