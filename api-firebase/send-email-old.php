<?php
header('Access-Control-Allow-Origin: *');
	
function send_email($to,$subject,$message){

	include_once '../includes/crud.php';
	$db=new Database();
	$db->connect();
	
	include_once '../includes/functions.php';
    $fn = new functions();
    $system_configs = $fn->get_system_configs();
	
	$app_name = $system_configs['app_name'];
	$from_mail = $system_configs['from_mail'];
	$reply_to = $system_configs['reply_to'];
	
	//send email
	$headers = "From: ".$app_name."<".$from_mail.">\n";
	$headers .= "Reply-To: ".$reply_to."\n";
	$headers .= "MIME-Version: 1.0\n";
	$headers .= "Content-Type: text/html; charset=ISO-8859-1\n";
		if(!mail($to,$subject,$message,$headers))
			return false;
		else
			return true;
}

	
function send_email_with_template($to,$subject,$item_data1,$order_data){
	include_once '../includes/crud.php';
	$db=new Database();
	$db->connect();
	
	include_once '../includes/functions.php';
    $fn = new functions();
    $system_configs = $fn->get_system_configs();
	
	$app_name = $system_configs['app_name'];
	$from_mail = $system_configs['from_mail'];
	$reply_to = $system_configs['reply_to'];
	ob_start(); 
	include 'email-templates/order-receipt.php'; 
	$message = ob_get_contents(); 
	ob_end_clean();
	
	//send email
	$headers = "From: ".$app_name."<".$from_mail.">\n";
	$headers .= "Reply-To: ".$reply_to."\n";
	$headers .= "MIME-Version: 1.0\n";
	$headers .= "Content-Type: text/html; charset=ISO-8859-1\n";
		if(!mail($to,$subject,$message,$headers))
			return false;
		else
			return true;
}

				


