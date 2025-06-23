<?php
define("APP_CLIENT_CODE", "TESTECUADORSTG-EC-CLIENT");
define("APP_CLIENT_KEY", "d4pUmVHgVpw2mJ66rWwtfWaO2bAWV6");
$user_id = "user_test_checkout_01";
$user_email = "checkoutuser@example.com";
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Agregar Tarjeta - Checkout</title>
  <script src="https://cdn.paymentez.com/ccapi/sdk/payment_checkout_stable.min.js" charset="UTF-8"></script>
  <style>
    body {
      font-family: Arial, sans-serif;
      background-color: #f4f4f4;
      padding: 40px 0;
    }
    .container {
      width: 100%;
      max-width: 500px;
      margin: auto;
      background: white;
      padding: 25px;
      border-radius: 8px;
      box-shadow: 0 4px 10px rgba(0,0,0,0.1);
    }
    h2 {
      text-align: center;
    }
    #add_card_btn {
      width: 100%;
      padding: 12px;
      margin-top: 20px;
      font-size: 16px;
      background: #007bff;
      color: white;
      border: none;
      border-radius: 5px;
      cursor: pointer;
    }
    #response {
      margin-top: 20px;
      font-size: 14px;
      white-space: pre-wrap;
    }
    .msg-success {
      background: #e8f5e9;
      border-left: 4px solid #4caf50;
      color: #256029;
      padding: 10px;
    }
    .msg-error {
      background: #fdecea;
      border-left: 4px solid #f44336;
      color: #7a1f1f;
      padding: 10px;
    }
  </style>
</head>
<body>
  <div class="container">
    <h2>Agregar Tarjeta</h2>
    <button id="add_card_btn">Agregar tarjeta</button>
    <div id="response"></div>
  </div>

  <script>
    const btn = document.getElementById("add_card_btn");
    const responseDiv = document.getElementById("response");

    const paymentezCheckout = new PaymentCheckout.modal({
      client_app_code: "<?php echo APP_CLIENT_CODE; ?>",
      client_app_key: "<?php echo APP_CLIENT_KEY; ?>",
      locale: "es",
      env_mode: "stg",
      onOpen: function() {
        console.log("Modal abierto");
      },
      onClose: function() {
        console.log("Modal cerrado");
      },
      onResponse: function(response) {
        console.log("Respuesta recibida:", response);
        if (response.card && response.card.status === "valid") {
          responseDiv.className = "msg-success";
        } else {
          responseDiv.className = "msg-error";
        }
        responseDiv.textContent = JSON.stringify(response, null, 2);

        // ✅ Verificar tarjeta automáticamente
        if (response.card && response.card.token) {
          fetch("verify_card.php", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({
              token: response.card.token,
              bin: response.card.bin,
              user_id: "<?php echo $user_id; ?>"
            })
          })
          .then(res => res.text())
          .then(msg => {
            responseDiv.textContent += "\n\n🟢 Verificación:\n" + msg;
          })
          .catch(err => {
            responseDiv.textContent += "\n\n❌ Error en verify_card.php";
            console.error(err);
          });
        }
      }
    });

    btn.addEventListener("click", function(e) {
      paymentezCheckout.open({
        user: {
          id: "<?php echo $user_id; ?>",
          email: "<?php echo $user_email; ?>"
        },
        amount: 1.00,
        currency: "USD",
        description: "Validación de tarjeta"
      });
    });
  </script>
</body>
</html>
