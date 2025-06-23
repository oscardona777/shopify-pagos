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
</head>
<body>
  <button id="add_card_btn">Agregar tarjeta</button>
  <div id="response"></div>
  <script>
    const btn = document.getElementById("add_card_btn");
    const responseDiv = document.getElementById("response");

    const paymentezCheckout = new PaymentCheckout.modal({
      client_app_code: "<?php echo APP_CLIENT_CODE; ?>",
      client_app_key: "<?php echo APP_CLIENT_KEY; ?>",
      locale: "es",
      env_mode: "stg",
      onResponse: response => {
        console.log("Respuesta del modal:", response);
        responseDiv.textContent = JSON.stringify(response, null, 2);
      }
    });

    btn.addEventListener("click", () => {
      const data = {
        user: { id: "<?php echo $user_id; ?>", email: "<?php echo $user_email; ?>", country: "EC" },
        amount: 1.00,
        currency: "USD",
        description: "Validación de tarjeta",
        reference: "verify_" + Date.now(),
        installments: 1,
        billing: {
          first_name: "Test",
          last_name: "User",
          address: "Av. Siempre Viva 742",
          city: "Quito",
          zip_code: "170101",
          country: "EC",
          phone: "+593000000000"
        }
      };

      console.log("🧾 Data enviada:", JSON.stringify(data, null, 2));
      paymentezCheckout.open(data);
    });
  </script>
</body>
</html>
