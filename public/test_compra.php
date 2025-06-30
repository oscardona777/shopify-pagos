<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Simulación de Compra</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
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

    .form-container {
      max-width: 480px;
      margin: 3rem auto;
      background: #ffffff;
      padding: 2rem;
      border-radius: 12px;
      box-shadow: 0 8px 20px rgba(0, 0, 0, 0.08);
    }

    h2 {
      text-align: center;
      margin-bottom: 1.5rem;
      color: #222;
    }

    label {
      display: block;
      margin-bottom: 0.5rem;
      font-weight: 600;
      color: #333;
    }

    input {
      width: 100%;
      padding: 0.75rem;
      margin-bottom: 1.5rem;
      border: 1px solid #ccc;
      border-radius: 8px;
      font-size: 1rem;
    }

    button {
      width: 100%;
      padding: 0.9rem;
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

    .footer-note {
      text-align: center;
      font-size: 0.85rem;
      color: #999;
      margin-top: 1rem;
    }
  </style>
</head>
<body>

  <div class="form-container">
    <h2>Simulación de Compra</h2>
    <form action="index.php" method="POST">
      <label for="user_id">ID Usuario</label>
      <input type="text" id="user_id" name="user_id" placeholder="ingrese un usuario abc123" required>

      <label for="email">Correo electrónico</label>
      <input type="email" id="email" name="email" placeholder="usuario@correo.com" required>

      <label for="amount">Monto (USD)</label>
      <input type="number" step="0.01" id="amount" name="amount" placeholder="ingrese un monto con iva incluido" required>

      <label for="description">Descripción del pedido</label>
      <input type="text" id="description" name="description" placeholder="Escriba un modelo de telefono" required>

      <button type="submit">Iniciar Pago</button>
    </form>

    <p class="footer-note">* Esta es una simulación usando entorno de pruebas.</p>
  </div>

</body>
</html>
