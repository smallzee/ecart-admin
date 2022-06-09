<?php
include('../includes/crud.php');
include('../includes/custom-functions.php');
include_once('../includes/variables.php');

$db = new Database();
$db->connect();
$fn = new custom_functions();

if(isset($_POST['accesskey']) && $_POST['accesskey'] == $access_key ){
	$data = $fn->get_settings('payment_methods',true);
	if(empty($data) || $data['paypal_payment_method'] == 0){ 
		header("location:".DOMAIN_URL."paypal/payment_status.php?tx=disabled");
		return false;
		exit();
	}
	if($data['paypal_mode'] == "sandbox") {
		$paypalUrl = 'https://www.sandbox.paypal.com/cgi-bin/webscr';
		$paypal_email = $data['paypal_business_email'];
	}
	if($data['paypal_mode'] == "production")
	{
		$paypalUrl = 'https://www.paypal.com/cgi-bin/webscr';
		$paypal_email = $data['paypal_business_email'];
	}

	$return_url = DOMAIN_URL.'paypal/payment_status.php';
	$cancel_url = DOMAIN_URL.'paypal/payment_status.php?tx=failure';
	$notify_url = DOMAIN_URL.'paypal/ipn.php';
	$txn_id = "eKart-".time()."-".rand();
	
	// $first_name = $_POST['first_name'];
	// $last_name = $_POST['last_name'];
	// $payer_email = $_POST['payer_email'];
	// $item_name = $_POST['item_name']; 
	// $item_number = $_POST['item_number'];
	// $amount = $_POST['amount'];
	$currency = $data['paypal_currency_code'];

	$querystring = "";
	$post = array(
		'cmd' => '_xclick',
		'no_note' => 1,
		'lc' => "",
		'currency_code' => $currency,
		'bn' => "PP-BuyNowBF:btn_buynow_LG.gif:NonHostedGuest",
		'txn_id' => $txn_id,
	);
	unset($_POST['accesskey']);
	$post = array_merge($post,$_POST);
	if(isset($txn_id) && !empty($return_url) && !empty($_POST['amount']))
	{
		$querystring .= "?business=".urlencode($paypal_email)."&";	
		
		foreach($post as $key => $value){
			$value = urlencode(stripslashes($value));
			$querystring .= "$key=$value&";
		}

		$querystring .= "return=".urlencode(stripslashes($return_url))."&";
		$querystring .= "cancel_return=".urlencode($cancel_url)."&";
		$querystring .= "notify_url=".urlencode($notify_url);
		echo $paypalUrl . $querystring;
		$db->disconnect();
		exit();
	}
}else{
    $response['error'] = true;
    $response['message'] = "Invalid Access Key";
    echo json_encode($response);
}
?>