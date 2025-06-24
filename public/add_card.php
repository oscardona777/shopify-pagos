<?php include 'config.php'; ?>
<!DOCTYPE html>
<html>
<head>
  <script src="https://cdn.paymentez.com/ccapi/sdk/payment_sdk_stable.min.js"></script>
</head>
<body>
  <form id="add-card-form">
    <input type="text" id="card_number" placeholder="Card Number"><br>
    <input type="text" id="holder_name" placeholder="Cardholder Name"><br>
    <input type="text" id="expiry_month" placeholder="MM"><br>
    <input type="text" id="expiry_year" placeholder="YYYY"><br>
    <input type="text" id="cvv" placeholder="CVV"><br>
    <button type="button" onclick="addCard()">Add Card</button>
  </form>

  <script>
    function addCard() {
      Paymentez.init(
        "<?php echo PAYMENTEZ_APP_CODE; ?>",
        "<?php echo PAYMENTEZ_APP_KEY; ?>",
        "<?php echo PAYMENTEZ_SANDBOX ? 'stg' : 'prod'; ?>"
      );

      Paymentez.addCard({
        user_id: "user123",
        user_email: "test@test.com",
        card_number: document.getElementById("card_number").value,
        holder_name: document.getElementById("holder_name").value,
        expiry_month: document.getElementById("expiry_month").value,
        expiry_year: document.getElementById("expiry_year").value,
        cvv: document.getElementById("cvv").value,
        success: function(response) {
          console.log("Card added:", response);
          alert("Token: " + response.card.token);
        },
        error: function(error) {
          console.error("Add card error:", error);
          alert("Error: " + JSON.stringify(error));
        }
      });
    }
  </script>
</body>
</html>
