<!DOCTYPE html>
<html lang="en">
<?php
/* 
  accesskey:90336
  order_id:123
  name:username
  address_line1:jubeli_circle
  postal_code:12345 {must be in 5 digit}
  city:bhuj
  amount:123456
*/
// include_once '../includes/crud.php';
// $db = new Database();
// $db->connect();
// include_once 'stripe.php';
// $st = new Stripe();
// include_once '../includes/custom-functions.php';
// $function = new custom_functions();

// $credentials = $st->get_credentials();
// $access_key = 90336;
// if (isset($_POST['accesskey']) && $_POST['accesskey'] == $access_key) {
//   if(empty($_POST['name']) || empty($_POST['postal_code']) || empty($_POST['city']) || empty($_POST['amount']) || empty($_POST['order_id'])){
//     $response['error'] = true;
//     $response['message'] = "Some data is missing";
//     echo json_encode($response);
//   }
  
//   $order_id = $db->escapeString($function->xss_clean($_POST['order_id']));
//   $name = $db->escapeString($function->xss_clean($_POST['name']));
//   $line1 = (isset($_POST['address_line1']) && $_POST['address_line1'] != '') ? $db->escapeString($function->xss_clean($_POST['address_line1'])) : "address";
//   $postal_code = $db->escapeString($function->xss_clean($_POST['postal_code']));
//   $city = $db->escapeString($function->xss_clean($_POST['city']));
//   $amount = $db->escapeString($function->xss_clean($_POST['amount']));
// } else {
//   $response['error'] = true;
//   $response['message'] = "Invalid Access Key";
//   echo json_encode($response);
// }

?>

<head>
  <meta charset="utf-8" />
  <title>Stripe Card Elements sample</title>
  <meta name="description" content="A demo of Stripe Payment Intents" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />

  <link rel="icon" type="image/ico" href="dist/img/logo.png">
  <link rel="stylesheet" href="css/normalize.css" />
  <link rel="stylesheet" href="css/global.css" />
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>

  <script src="https://js.stripe.com/v3/"></script>

  <script src="script.js" defer></script>
  <script>
      
    var name = "client1";
    var line1 = "jubeli circle";
    var amount = 1890;
    var postal_code = "12345";
    var city = "BHUJ";
    var order_id = "4068";
    // var name = '<?= $name?>';
    // var line1 = '<?= $line1?>';
    // var amount = parseInt('<?= $amount?>');
    // var postal_code = '<?= $postal_code ?>';
    // var city = '<?= $city?>';
    // var order_id = '<?= $order_id?>';
  </script>
</head>

<body>

  <pre id="payment_result" name="payment_result"></pre>
  <div class="sr-root">
    <div class="sr-main">
      <form id="payment-form" class="sr-payment-form">
        <div class="sr-combo-inputs-row">
          <div class="sr-input sr-card-element" id="card-element"></div>
        </div>
        <div class="sr-field-error" id="card-errors" role="alert"></div>
        <button id="submit">
          <div class="spinner hidden" id="spinner"></div>
          <span id="button-text">Pay</span><span id="order-amount"></span>
        </button>
      </form>

    </div>
  </div>

</body>

<script>
 
</script>

</html>