<?php 
include_once('../includes/crud.php');
$db = new Database();
include_once('../includes/custom-functions.php');
$fn = new custom_functions;
$db->connect();
date_default_timezone_set('Asia/Kolkata');
if(isset($_POST['i']) && isset($_POST['pid'])){
	if(ALLOW_MODIFICATION==0 && !defined (ALLOW_MODIFICATION)){
		echo '<label class="alert alert-danger">This operation is not allowed in demo panel!.</label>';
		return false;
	}
	$i = $db->escapeString($fn->xss_clean($_POST['i']));
	$pid = $db->escapeString($fn->xss_clean($_POST['pid']));
	$sql = "SELECT other_images FROM products WHERE id =".$pid;
    $db->sql($sql);
    $res = $db->getResult();
    foreach($res as $row)
    	$other_images = $row['other_images']; /*get other images json array*/
	$other_images = json_decode($other_images); /*decode from json to array*/
	unlink("../".$other_images[$i]); /*remove the image from the folder*/
	unset($other_images[$i]); /*remove image from the array*/
	$other_images= json_encode(array_values($other_images)); /*convert back to JSON */
	
	/*update the table*/
	$sql = "UPDATE `products` set `other_images`='".$other_images."' where id=".$pid;
	if($db->sql($sql))
		echo 1;
	else 
		echo 0;
}
?>