<?php
define("APP_CLIENT_CODE", "TESTECUADORSTG-EC-CLIENT");
define("APP_CLIENT_KEY", "d4pUmVHgVpw2mJ66rWwtfWaO2bAWV6");
$user_id = "user_demo_456";
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Agregar Tarjeta Manual</title>
  <script src="https://cdn.paymentez.com/ccapi/sdk/payment_sdk_stable.min.js" charset="UTF-8"></script>
  <style>
    body {
      font-family: sans-serif;
      background: #f8f8f8;
      padding: 40px 0;
    }
    .container {
      width: 100%;
      max-width: 480px;
      margin: 0 auto;
      background: #fff;
      padding: 25px;
      border-radius: 8px;
      box-shadow: 0 4px 10px rgba(0,0,0,0.1);
    }
    h2 {
      text-align: center;
      margin-bottom: 20px;
    }
    .tok_btn {
      display: block;
      width: 100%;
      margin-top: 15px;
      background: #28a745;
      color: #fff;
      font-weight: bold;
      padding: 12px;
      font-size: 16px;
      border: none;
      border-radius: 5px;
      cursor: pointer;
    }
    .tok_btn:disabled {
      background: #94d3a2;
      cursor: not-allowed;
    }
    #response {
      margin-top: 15px;
      white-space: pre-wrap;
      font-size: 14px;
    }
    .msg-success {
      background: #e6f8ea;
      border-left: 4px solid #28a745;
      padding: 10px;
      color: #2e6b3c;
    }
    .msg-error {
      background: #fdecea;
      border-left: 4px solid #f44336;
      padding: 10px;
      color: #7a1f1f;
    }
  </style>
</head>
<body>

  <div class="container">
    <h2>Agregar Tarjeta</h2>
    <div id="tokenize_example"></div>
    <div id="response"></div>
    <button id="tokenize_btn" class="tok_btn" disabled>Agregar tarjeta</button>
    <button id="retry_btn" class="tok_btn" style="display:none; background:#007bff;">Agregar nueva tarjeta</button>
  </div>

  <script>
    window.addEventListener('DOMContentLoaded', () => {
      if (typeof PaymentGateway === 'undefined') {
        document.getElementById("response").innerHTML =
          '<div class="msg-error">❌ Error: SDK de Paymentez no cargó correctamente.</div>';
        return;
      }

      const sdk = new PaymentGateway(
        "<?php echo APP_CLIENT_CODE; ?>",
        "<?php echo APP_CLIENT_KEY; ?>",
        "stg"
      );

      const btn = document.getElementById("tokenize_btn");
      const retry = document.getElementById("retry_btn");
      const resp = document.getElementById("response");

      const getData = () => ({
        locale: "es",
        user: { id: "<?php echo $user_id; ?>", email: "" },
        configuration: { default_country: "EC" }
      });

      const onResponse = (response) => {
        const isSuccess = response.card && response.card.status === "valid";
        resp.className = isSuccess ? "msg-success" : "msg-error";
        resp.textContent = JSON.stringify(response, null, 2);
        btn.style.display = "none";
        retry.style.display = "block";

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
            resp.textContent += "\n🟢 Servidor: " + msg;
          })
          .catch(err => {
            resp.textContent += "\n❌ Error al contactar verify_card.php";
            console.error(err);
          });
        }
      };

      const onIncomplete = (msg) => {
        resp.className = "msg-error";
        resp.textContent = "Formulario incompleto: " + msg;
        btn.textContent = "Agregar tarjeta";
        btn.disabled = false;
      };

      sdk.generate_tokenize(getData(), "#tokenize_example", onResponse, onIncomplete);
      btn.disabled = false;

      btn.addEventListener("click", e => {
        btn.textContent = "Procesando...";
        btn.disabled = true;
        sdk.tokenize();
        e.preventDefault();
      });

      retry.addEventListener("click", e => {
        resp.textContent = "";
        resp.className = "";
        btn.textContent = "Agregar tarjeta";
        btn.disabled = false;
        btn.style.display = "block";
        retry.style.display = "none";
        sdk.generate_tokenize(getData(), "#tokenize_example", onResponse, onIncomplete);
        e.preventDefault();
      });
    });
  </script>
</body>
</html>
