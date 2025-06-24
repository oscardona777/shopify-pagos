
<?php
include 'config.php';
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Agregar Tarjeta</title>
  <script src="https://cdn.paymentez.com/ccapi/sdk/payment_sdk_stable.min.js"></script>
  <style>
    body {
      font-family: Arial, sans-serif;
      background: #f9f9f9;
      padding: 20px;
    }
    #card-form {
      max-width: 400px;
      margin: auto;
      background: #fff;
      padding: 20px;
      border-radius: 10px;
      box-shadow: 0 0 10px rgba(0,0,0,0.1);
    }
    #btnAddCard {
      width: 100%;
      background-color: #0a74da;
      color: #fff;
      border: none;
      padding: 12px;
      border-radius: 6px;
      font-size: 16px;
      cursor: pointer;
    }
    #result {
      margin-top: 20px;
      font-size: 14px;
      color: #333;
    }
  </style>
</head>
<body>
  <div id="card-form"></div>
  <button id="btnAddCard">Agregar Tarjeta</button>
  <div id="result"></div>

  <script>
    const paymentez = new PaymentSDK(
      "<?php echo addslashes(APP_CLIENT_CODE); ?>",
      "<?php echo addslashes(APP_CLIENT_KEY); ?>",
      true
    );

    document.getElementById("btnAddCard").addEventListener("click", function () {
      paymentez.addCard({
        user: {
          id: "user-001",
          email: "cliente@ejemplo.com"
        },
        configuration: {
          partial_payment: false,
          expiration_days: 1,
          allowed_payment_methods: ["card"]
        },
        containerID: "card-form",
        onSuccess: function (response) {
          document.getElementById("result").innerText = "✅ Tarjeta agregada con éxito. Token: " + response.card.token;
          console.log("✅ Success:", response);
        },
        onError: function (error) {
          document.getElementById("result").innerText = "❌ Error: " + JSON.stringify(error);
          console.error("❌ Error:", error);
        }
      });
    });
  </script>
</body>
</html>
