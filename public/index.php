<?php
ob_start();
include 'config.php';

// Redirigir si no vienen datos del formulario
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || 
    empty($_POST['user_id']) || 
    empty($_POST['email']) || 
    empty($_POST['amount']) || 
    empty($_POST['description'])) {
  header("Location: https://honorstore.ec");
  exit;
}

// Pasar datos a JS
$user_id = $_POST['user_id'];
$email = $_POST['email'];
$amount = $_POST['amount'];
$description = $_POST['description'];
<?php ob_end_flush();
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Pago con Paymentez</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <!-- SDK y jQuery -->
  <script src="https://code.jquery.com/jquery-3.5.0.min.js"></script>
  <script src="https://cdn.paymentez.com/ccapi/sdk/payment_checkout_3.0.0.min.js"></script>
  <style>
    * {
      box-sizing: border-box;
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }
    body {
      margin: 0;
      padding: 0;
      background-color: #f9f9f9;
    }
    .container {
      max-width: 480px;
      margin: 3rem auto;
      background: #ffffff;
      padding: 2rem;
      border-radius: 12px;
      box-shadow: 0 8px 20px rgba(0, 0, 0, 0.08);
      text-align: center;
    }
    h2 {
      margin-bottom: 1.5rem;
      color: #222;
    }
    button {
      display: block;
      width: 100%;
      padding: 0.9rem;
      margin-bottom: 1rem;
      background-color: #1e1e1e;
      color: #fff;
      font-weight: 600;
      font-size: 1rem;
      border: none;
      border-radius: 8px;
      cursor: pointer;
      transition: background-color 0.3s;
    }
    button:hover {
      background-color: #333;
    }
    .btn-refund-alert {
      background-color: #e74c3c !important;
    }
    .btn-refund-alert:hover {
      background-color: #c0392b !important;
    }
    #respuesta, #refund {
      white-space: pre-wrap;
      background: #f5f5f5;
      padding: 1rem;
      margin-top: 1rem;
      border: 1px solid #ccc;
      border-radius: 8px;
      text-align: left;
      display: none; /* ocultos por defecto */
    }
  </style>
</head>
<body>

<div class="container">
  <h2>💳 Realizar pago con Paymentez</h2>
  <button id="btn-pagar">Pago corriente</button>
  <button id="btn-cuotas">Pago diferido</button>

  <div id="respuesta"></div>
  <div id="refund"></div>
</div>

<script>
  const paymentCheckout = new PaymentCheckout.modal({
    env_mode: "<?= PAYMENTEZ_SANDBOX ? 'stg' : 'prod' ?>",
    onOpen: () => console.log("🔓 Modal abierto"),
    onClose: () => console.log("❌ Modal cerrado"),
    onResponse: function (response) {
      console.log("📩 Respuesta del modal:", response);
      const trans = response.transaction;

      // Enviar manualmente al callback
      fetch("<?= $CALLBACK_URL ?>", {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(response)
      }).then(res => res.json())
        .then(data => console.log("📬 Callback procesado:", data))
        .catch(err => console.error("❌ Error enviando al callback:", err));

      const respuestaEl = document.getElementById("respuesta");

      if (trans && trans.id) {
        lastTransactionId = trans.id;
        const estado = estadoTraducido[trans.status.toLowerCase()];
        const mensaje = estado ? estado.texto : `🔍 Estado desconocido: ${trans.status}`;
        respuestaEl.innerHTML =
          `<strong style="color:${estado?.color || '#000'}; font-size: 1.2rem;">${mensaje}</strong>`;
        respuestaEl.style.display = "block";
        if (trans.status === "success") {
          mostrarBotonRefund();
        }
      } else {
        respuestaEl.innerText = "⚠️ No se recibió información de la transacción.";
        respuestaEl.style.display = "block";
      }
    }
  });

  const estadoTraducido = {
    "pending": { texto: "⏳ Pago Pendiente", color: "#e0c300" },
    "success": { texto: "✅ Pago Aprobado", color: "#27ae60" },
    "cancelled": { texto: "🚫 Pago Cancelado", color: "#c0392b" },
    "rejected": { texto: "❌ Pago Rechazado", color: "#c0392b" },
    "expired": { texto: "❌ Pago Rechazado", color: "#c0392b" },
    "failure": { texto: "❌ Pago Rechazado", color: "#c0392b" }
  };

  let lastTransactionId = null;

  function iniciarPago(endpoint, boton) {
    boton.disabled = true;
    const textoOriginal = boton.innerText;
    boton.innerText = "Generando referencia...";

    const formData = new FormData();
    formData.append("user_id", "<?= $user_id ?>");
    formData.append("email", "<?= $email ?>");
    formData.append("amount", "<?= $amount ?>");
    formData.append("description", "<?= $description ?>");

    fetch(endpoint, {
      method: "POST",
      body: formData
    })
    .then(res => res.json())
    .then(data => {
      console.log(`🔎 Respuesta de ${endpoint}:`, data);
      if (data.success && data.reference) {
        paymentCheckout.open({ reference: data.reference });
      } else {
        alert("❌ Error al generar la referencia.");
      }
    })
    .catch(err => {
      console.error("❌ Error en fetch:", err);
      alert("❌ Error de conexión.");
    })
    .finally(() => {
      boton.disabled = false;
      boton.innerText = textoOriginal;
    });
  }

  document.getElementById("btn-pagar").addEventListener("click", function () {
    iniciarPago("init_reference_c.php", this);
  });

  document.getElementById("btn-cuotas").addEventListener("click", function () {
    iniciarPago("init_reference_d.php", this);
  });

  function mostrarBotonRefund() {
    const div = document.getElementById("refund");
    div.innerHTML = `<button id="btn-refund" class="btn-refund-alert">🔁 Cancelar transacción</button>`;
    div.style.display = "block";

    document.getElementById("btn-refund").addEventListener("click", function () {
      if (!lastTransactionId) return alert("No hay ID de transacción.");

      if (confirm("¿Estás seguro que deseas cancelar la transacción?")) {
        fetch("refund_transaction.php", {
          method: "POST",
          headers: { "Content-Type": "application/x-www-form-urlencoded" },
          body: `transaction_reference=${encodeURIComponent(lastTransactionId)}`
        })
        .then(res => res.json())
        .then(data => {
          if (data.status === "success") {
            document.getElementById("btn-refund").remove();
            const mensaje = estadoTraducido["cancelled"];
            const respuestaEl = document.getElementById("respuesta");
            respuestaEl.innerHTML =
              `<strong style="color:${mensaje.color}; font-size: 1.2rem;">${mensaje.texto}</strong>`;
            respuestaEl.style.display = "block";
          } else {
            const respuestaEl = document.getElementById("respuesta");
            respuestaEl.innerHTML =
              `<strong style="color:#c0392b; font-size: 1rem;">⚠️ No es posible cancelar la transacción</strong>`;
            respuestaEl.style.display = "block";
          }
        })
        .catch(err => {
          console.error("❌ Error refund:", err);
          const respuestaEl = document.getElementById("respuesta");
          respuestaEl.innerHTML =
            `<strong style="color:#c0392b; font-size: 1rem;">❌ Error al procesar la cancelación</strong>`;
          respuestaEl.style.display = "block";
        });
      }
    });
  }
</script>

</body>
</html>
