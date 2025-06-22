<?php
define("APP_CLIENT_CODE", "TESTECUADORSTG-EC-CLIENT");
define("APP_CLIENT_KEY", "d4pUmVHgVpw2mJ66rWwtfWaO2bAWV6");
$user_id = "user_demo_123";
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Agregar Tarjeta - Checkout</title>
  <script src="https://cdn.paymentez.com/ccapi/sdk/payment_sdk_stable.min.js" charset="UTF-8"></script>
  <style>
    #payment_example_div {
      max-width: 600px;
      min-width: 400px;
      margin: 20px auto;
    }
    #payment_example_div > * {
      margin: 10px auto;
    }
    .tok_btn {
      background: linear-gradient(to bottom, rgba(140,197,65,1), rgba(20,167,81,1));
      color: #fff;
      width: 80%;
      border: none;
      border-radius: 4px;
      font-size: 17px;
      line-height: 34px;
      font-weight: bold;
      cursor: pointer;
    }
    .tok_btn:disabled {
      opacity: 0.65;
      cursor: not-allowed;
    }
    #response {
      max-width: 600px;
      margin: 15px auto;
      padding: 12px;
      font-size: 14px;
      white-space: pre-wrap;
    }
    .msg-success { background: #e6f8ea; border-left: 5px solid #4caf50; color: #1c5530; }
    .msg-error   { background: #fdecea; border-left: 5px solid #f44336; color: #7a1f1f; }
  </style>
</head>
<body>

  <div id="payment_example_div">
    <div id="tokenize_example"></div>
    <div id="response"></div>
    <button id="tokenize_btn" class="tok_btn" disabled>Agregar tarjeta</button>
    <button id="retry_btn" class="tok_btn" style="display:none">Agregar nueva tarjeta</button>
  </div>

  <script>
    window.addEventListener('DOMContentLoaded', () => {
      if (typeof PaymentGateway === 'undefined') {
        alert("SDK no cargó correctamente. Verifica que estás en entorno HTTPS.");
        return;
      }

      const sdk = new PaymentGateway(
        '<?php echo APP_CLIENT_CODE; ?>',
        '<?php echo APP_CLIENT_KEY; ?>',
        'stg'
      );

      const btn = document.getElementById('tokenize_btn');
      const retry = document.getElementById('retry_btn');
      const resp = document.getElementById('response');

      const getData = () => ({
        locale: 'es',
        user: { id: '<?php echo $user_id; ?>', email: '' },
        configuration: { default_country: 'COL' }
      });

      const onIncomplete = (msg) => {
        resp.className = 'msg-error';
        resp.textContent = `Formulario incompleto: ${msg}`;
        btn.textContent = 'Agregar tarjeta';
        btn.disabled = false;
      };

      const onResponse = (response) => {
        resp.className = response.card ? 'msg-success' : 'msg-error';
        resp.textContent = JSON.stringify(response, null, 2);
        btn.style.display = 'none';
        retry.style.display = 'block';

        if (response.card && response.card.token) {
          fetch("verify_card.php", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({
              token: response.card.token,
              bin: response.card.bin,
              user_id: '<?php echo $user_id; ?>'
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

      sdk.generate_tokenize(getData(), '#tokenize_example', onResponse, onIncomplete);
      btn.disabled = false;

      btn.addEventListener('click', (e) => {
        btn.textContent = 'Procesando...';
        btn.disabled = true;
        sdk.tokenize();
        e.preventDefault();
      });

      retry.addEventListener('click', (e) => {
        resp.textContent = '';
        resp.className = '';
        btn.textContent = 'Agregar tarjeta';
        btn.disabled = false;
        btn.style.display = 'block';
        retry.style.display = 'none';
        sdk.generate_tokenize(getData(), '#tokenize_example', onResponse, onIncomplete);
        e.preventDefault();
      });
    });
  </script>
</body>
</html>
