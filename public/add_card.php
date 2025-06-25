<?php include 'config.php'; ?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Agregar tarjeta y verificar</title>
  <script src="https://cdn.paymentez.com/ccapi/sdk/payment_sdk_stable.min.js" charset="UTF-8"></script>
</head>
<body>
  <h2>Agregar nueva tarjeta</h2>
  <div id="card-form"></div>
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
      console.log("📦 Respuesta del SDK:", response);

      if (response && response.card && typeof response.card.token === 'string') {
        const token = response.card.token;
        alert("✅ Token generado: " + token);

        fetch('verify_card.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
          body: 'token=' + encodeURIComponent(token)
        })
        .then(res => res.json())
        .then(data => {
          console.log('🔍 Verificación:', data);
          if (data.verify?.success) {
            alert('✅ Verificación exitosa: ' + data.verify.message);
          } else {
            alert('⚠️ Verificación no confirmada');
          }
        })
        .catch(err => {
          console.error('❌ Error verificando tarjeta:', err);
          alert('Error verificando tarjeta.');
        });

      } else {
        console.warn("❌ Token no recibido o estructura inválida:", response);
        alert("No se generó un token válido.");
      }
    }

    function onFormIncomplete(error) {
      console.warn("⚠️ Formulario incompleto:", error);
      alert("Por favor, completa correctamente los campos de la tarjeta.");
    }
  </script>
</body>
</html>
