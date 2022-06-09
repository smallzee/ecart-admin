<?php
session_start();
ini_set('display_errors', 1);
//importing required files
require_once 'includes/crud.php';
$db_con = new Database();
$db_con->connect();
require_once 'includes/functions.php';
require_once('includes/firebase.php');
require_once('includes/push.php');


$fnc = new functions;

include_once('includes/custom-functions.php');

$fn = new custom_functions;
$permissions = $fn->get_permissions($_SESSION['id']);

$response = array();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    //hecking the required params 
    if (isset($_POST['title']) and isset($_POST['message'])) {
        if (ALLOW_MODIFICATION == 0 && !defined(ALLOW_MODIFICATION)) {
            echo '<label class="alert alert-danger">This operation is not allowed in demo panel!.</label>';
            return false;
        }
        if ($permissions['notifications']['create'] == 0) {
            $response['error'] = true;
            $response['message'] = '<p class="alert alert-danger">You have no permission to send notifications</p>';
            echo (json_encode($response));
            return false;
        }
        //creating a new push
        $title = $db_con->escapeString($fn->xss_clean($_POST['title']));
        $message = $db_con->escapeString($fn->xss_clean($_POST['message']));
        $type = $db_con->escapeString($fn->xss_clean($_POST['type']));
        $id = ($type != 'default') ? $db_con->escapeString($fn->xss_clean($_POST[$type])) : "0";
        /*dynamically getting the domain of the app*/
        $url  = isset($_SERVER['HTTPS']) ? 'https://' : 'http://';
        $url .= $_SERVER['SERVER_NAME'];
        $url .= $_SERVER['REQUEST_URI'];
        $server_url = dirname($url) . '/';

        $push = null;
        $include_image = (isset($_POST['include_image']) && $_POST['include_image'] == 'on') ? TRUE : FALSE;
        if ($include_image) {
            $extension = explode(".", $_FILES["image"]["name"]);
            $extension = end($extension);
            $result = $fn->validate_image($_FILES["image"]);
            if ($result) {
                $response['error'] = true;
                $response['message'] = 'Image type must jpg, jpeg, gif, or png!';
                echo json_encode($response);
                return false;
            }
            // $mimetype = mime_content_type($_FILES["image"]["tmp_name"]);
            // if (!in_array($mimetype, array('image/jpg', 'image/jpeg', 'image/gif', 'image/png'))) {
            //     $response['error'] = true;
            //     $response['message'] = 'Image type must jpg, jpeg, gif, or png!';
            //     echo json_encode($response);
            //     return false;
            // }
            $target_path = 'upload/notifications/';
            $filename = microtime(true) . '.' . strtolower($extension);
            $full_path = $target_path . "" . $filename;
            if (!move_uploaded_file($_FILES["image"]["tmp_name"], $full_path)) {
                $response['error'] = true;
                $response['message'] = 'Image is not uploaded';
                echo json_encode($response);
                return false;
            }
            $sql = "INSERT INTO `notifications`(`title`, `message`,  `type`, `type_id`, `image`) VALUES 
			('" . $title . "','" . $message . "','" . $type . "','" . $id . "','" . $full_path . "')";
        } else {
            $sql = "INSERT INTO `notifications`(`title`, `message`, `type`, `type_id`) VALUES 
			('" . $title . "','" . $message . "','" . $type . "','" . $id . "')";
        }
        $db_con->sql($sql);
        $db_con->getResult();
        //first check if the push has an image with it
        if ($include_image) {
            $push = new Push(
                $db_con->escapeString($fn->xss_clean($_POST['title'])),
                $db_con->escapeString($fn->xss_clean($_POST['message'])),
                $server_url . '' . $full_path,
                $type,
                $id
            );
        } else {
            //if the push don't have an image give null in place of image
            $push = new Push(
                $db_con->escapeString($fn->xss_clean($_POST['title'])),
                $db_con->escapeString($fn->xss_clean($_POST['message'])),
                null,
                $type,
                $id
            );
        }

        //getting the push from push object
        $mPushNotification = $push->getPush();

        //getting the token from database object 
        $devicetoken = $fnc->getAllTokens();
        $devicetoken1 = $fnc->getAllTokens("devices");
        $final_tokens = array_merge($devicetoken, $devicetoken1);
        $f_tokens = array_unique($final_tokens);
        $devicetoken_chunks = array_chunk($f_tokens, 1000);
        foreach ($devicetoken_chunks as $devicetokens) {
            //creating firebase class object 
            $firebase = new Firebase();

            //sending push notification and displaying result 
            $firebase->send($devicetokens, $mPushNotification);
        }
        // array_unique();
        //getting the token from devices table 
        // $devicetoken = $fnc->getAllTokens("devices");

        // $devicetoken_chunks = array_chunk($devicetoken,1000);
        // foreach($devicetoken_chunks as $devicetokens){
        // 	//creating firebase class object 
        // 	$firebase = new Firebase(); 

        // 	//sending push notification and displaying result 
        // 	$firebase->send($devicetokens, $mPushNotification);
        // }
        $response['error'] = false;
        // 		$response['message'] = $firebase->send($devicetoken, $mPushNotification);
        $response["message"] = "<span class='label label-success'>Notification Sent Successfully!</span>";
    } else {
        $response['error'] = true;
        $response['message'] = 'Parameters missing';
    }
} else {
    $response['error'] = true;
    $response['message'] = 'Invalid request';
}
// echo str_replace("\\/","/",json_encode($response['message']));
echo (json_encode($response));
