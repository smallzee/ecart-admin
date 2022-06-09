<?php 
/* ?amt=1.00&cc=USD&item_name=Product%20name&item_number=12&st=Completed&tx=48024375RJ1616846 */
if(isset($_GET) && !empty($_GET)){
	$response = array();
	/* When payment is completed */
	if(isset($_GET['amt']) && isset($_GET['st']) && $_GET['st'] == 'Completed'){
		$response['error'] = false;
		$response['message'] = "Payment completed successfully";
		$response['status'] = $_GET['st'];
		$response['amount'] = $_GET['amt'];
		$response['item_name'] = $_GET['item_name'];
		$response['item_number'] = $_GET['item_number'];
		$response['paypal_txn_id'] = $_GET['tx'];
		echo json_encode($response);
		return false;
	}elseif(isset($_GET['amt']) && isset($_GET['st']) && $_GET['st'] == 'Authorize'){
		$response['error'] = false;
		$response['message'] = "Payment is authorized successfully. Your order will be fulfilled once we capture the transaction. ";
		$response['status'] = $_GET['st'];
		$response['amount'] = $_GET['amt'];
		$response['item_name'] = $_GET['item_name'];
		$response['item_number'] = $_GET['item_number'];
		$response['paypal_txn_id'] = $_GET['tx'];
		echo json_encode($response);
		return false;
	}elseif(isset($_GET['tx']) && $_GET['tx'] == 'disabled'){
		$response['error'] = true;
		$response['message'] = "Paypal payment method is not available currently";
		echo json_encode($response);
		return false;
	}else{
		$response['error'] = true;
		$response['message'] = "Payment either cancelled / failed to initialize. Try again or try some other payment method. Thank you";
		echo json_encode($response);
		return false;
	}
}
?>