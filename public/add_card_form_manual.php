<?php
define("APP_CLIENT_CODE", "TESTECUADORSTG-EC-CLIENT");  // Reemplaza con tu App Code (cliente)
define("APP_CLIENT_KEY", "d4pUmVHgVpw2mJ66rWwtfWaO2bAWV6");    // Reemplaza con tu App Key (cliente)
$user_id = "user_demo_123"; // ID único de usuario
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Agregar Tarjeta</title>
  <script src="js/payment_sdk_stable.min.js" charset="UTF-8"></script>
  <style>
    body {
      background-color: #f3f4f6;
      font-family: 'Segoe UI', sans-serif;
      display: flex;
      justify-content: center;
      align-items: center;
      height: 100vh;
    }

    .card-box {
      background-color: #fff;
      border-radius: 8px;
      padding: 25px;
      width: 360px;
      box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }

    .field-group {
      display: flex;
      align-items: center;
      background: #f9f9f9;
      border: 1px solid #ccc;
      border-radius: 5px;
      padding: 10px;
      margin-bottom: 15px;
    }

    .field-group input {
      border: none;
      background: transparent;
      width: 100%;
      font-size: 14px;
      outline: none;
    }

    .row {
      display: flex;
      gap: 10px;
    }

    .row .field-group {
      flex: 1;
    }

    button {
      background-color: #111827;
      color: white;
      padding: 12px;
      width: 100%;
      border: none;
      border-radius: 5px;
      font-size: 15px;
      cursor: pointer;
    }

    #resultado {
      margin-top: 15px;
      padding: 12px;
      border-radius: 6px;
      font-size: 14px;
      font-weight: 500;
      display: none;
      white-space: pre-wrap;
    }

    .msg-success {
      background-color: #e6f8ea;
      border-left: 5px solid #4CAF50;
      color: #1c5530;
    }

    .msg-error {
      background-color: #fdecea;
      border-left: 5px solid #f44336;
      color: #7a1f1f;
    }
  </style>
</head>
<body>

  <div class="card-box">
    <form id="card-form">
      <div class="field-group">
        <input type="text" id="card-name" placeholder="Nombre del titular" required>
      </div>

      <div class="field-group">
        <input type="text" id="card-number" placeholder="Número de tarjeta" required>
      </div>

      <div class="row">
        <div class="field-group">
          <input type="text" id="card-exp-month" placeholder="MM" required>
        </div>
        <div class="field-group">
          <input type="text" id="card-exp-year" placeholder="YYYY" required>
        </div>
        <div class="field-group">
          <input type="text" id="card-cvv" placeholder="CVC" required>
        </div>
      </div>

      <div class="field-group">
        <input type="email" id="email" placeholder="Correo electrónico" required>
      </div>

      <button type="submit">Agregar tarjeta</button>
      <div id="resultado"></div>
    </form>
  </div>

  <script>
    window.addEventListener('DOMContentLoaded', function () {
      if (typeof PaymentSDK === "undefined") {
        alert("❌ SDK de Paymentez no cargó correctamente.\nAsegúrate de usar http://localhost o https:// con ngrok.\nNo uses file://");
        return;
      }

      const paymentez = new PaymentSDK("<?php echo APP_CLIENT_CODE; ?>", "<?php echo APP_CLIENT_KEY; ?>", true);
      const userId = "<?php echo $user_id; ?>";

      document.getElementById("card-form").addEventListener("submit", function(e) {
        e.preventDefault();

        const cardData = {
          card: {
            number: document.getElementById("card-number").value.trim(),
            holder_name: document.getElementById("card-name").value.trim(),
            expiry_month: document.getElementById("card-exp-month").value.trim(),
            expiry_year: document.getElementById("card-exp-year").value.trim(),
            cvc: document.getElementById("card-cvv").value.trim()
          },
          user: {
            id: userId,
            email: document.getElementById("email").value.trim()
          }
        };

        const resultDiv = document.getElementById("resultado");
        resultDiv.style.display = "block";
        resultDiv.className = "";
        resultDiv.innerHTML = "⏳ Procesando...";

        paymentez.addCard(cardData, function(response) {
          if (response.card) {
            const token = response.card.token;
            resultDiv.className = "msg-success";
            resultDiv.innerHTML = `✅ Token generado: ${token}<br><em>Verificando tarjeta...</em>`;

            fetch("verify_card.php", {
              method: "POST",
              headers: { "Content-Type": "application/json" },
              body: JSON.stringify({
                user_id: userId,
                token: token,
                bin: response.card.bin
              })
            })
            .then(res => res.text())
            .then(msg => {
              console.log("Respuesta del servidor:", msg);
              const isOk = msg.includes("✅") || msg.includes("success");
              resultDiv.className = isOk ? "msg-success" : "msg-error";
              resultDiv.innerHTML += `<br><strong>${msg}</strong>`;
            })
            .catch(err => {
              resultDiv.className = "msg-error";
              resultDiv.innerHTML += `<br><strong>❌ Error al contactar el servidor</strong>`;
              console.error(err);
            });

          } else {
            resultDiv.className = "msg-error";
            resultDiv.innerHTML = `❌ Error: ${response.error?.message || "Tokenización fallida"}`;
          }
        });
      });
    });
  </script>

</body>
</html>
