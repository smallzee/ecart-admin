<?php
header('Access-Control-Allow-Origin: *');
session_start();
include '../includes/crud.php';
include_once '../includes/variables.php';
include_once '../includes/custom-functions.php';
$fn = new custom_functions;
include_once('verify-token.php');
$db = new Database();
$db->connect();
$response = array();

$config = $fn->get_configurations();
$time_slot_config = $fn->time_slot_config();
if (isset($config['system_timezone']) && isset($config['system_timezone_gmt'])) {
    date_default_timezone_set($config['system_timezone']);
    $db->sql("SET `time_zone` = '" . $config['system_timezone_gmt'] . "'");
} else {
    date_default_timezone_set('Asia/Kolkata');
    $db->sql("SET `time_zone` = '+05:30'");
}

/*
offer-images.php
    accesskey:90336
*/

if (!isset($_POST['accesskey'])) {
    if (!isset($_GET['accesskey'])) {
        $response['error'] = true;
        $response['message'] = "Access key is invalid or not passed!";
        print_r(json_encode($response));
        return false;
    }
}

if (isset($_POST['accesskey'])) {
    $accesskey = $db->escapeString($fn->xss_clean($_POST['accesskey']));
} else {
    $accesskey = $db->escapeString($fn->xss_clean($_GET['accesskey']));
}

if ($access_key != $accesskey) {
    $response['error'] = true;
    $response['message'] = "invalid accesskey!";
    print_r(json_encode($response));
    return false;
}
if ((!isset($_REQUEST['ajax_call']))) {
    if (!verify_token()) {
        return false;
    }
}

if ((isset($_POST['add-image'])) && ($_POST['add-image'] == 1)) {
    $permissions = $fn->get_permissions($_SESSION['id']);

    if ($permissions['new_offers']['create'] == 0) {
        $response["message"] = "<p class='alert alert-danger'>You have no permission to create new offers.</p>";
        echo json_encode($response);
        return false;
    }
    $image = $db->escapeString($fn->xss_clean($_FILES['image']['name']));
    $image_error = $db->escapeString($fn->xss_clean($_FILES['image']['error']));
    $image_type = $db->escapeString($fn->xss_clean($_FILES['image']['type']));
    $target_path = '../upload/offers/';
    $error = array();
    $allowedExts = array("gif", "jpeg", "jpg", "png");

    // get image file extension
    error_reporting(E_ERROR | E_PARSE);
    $extension = end(explode(".", $_FILES["image"]["name"]));
    if ($image_error > 0) {
        $error['image'] = " <span class='label label-danger'>Not uploaded!</span>";
    } else {

        $result = $fn->validate_image($_FILES["image"]);
        if ($result) {
            $response["message"] = "<span class='label label-danger'>Image type must jpg, jpeg, gif, or png!</span>";
            echo json_encode($response);
            $error['image'] = " <span class='label label-danger'>Image type must jpg, jpeg, gif, or png!</span>";
            return false;
        }
        // $mimetype = mime_content_type($_FILES["image"]["tmp_name"]);
        // if (!in_array($mimetype, array('image/jpg', 'image/jpeg', 'image/gif', 'image/png'))) {
        //     $response["message"] = "<span class='label label-danger'>Image type must jpg, jpeg, gif, or png!</span>";
        //     echo json_encode($response);
        //     $error['image'] = " <span class='label label-danger'>Image type must jpg, jpeg, gif, or png!</span>";
        //     return false;
        // }
    }
    if (empty($error['image'])) {
        // create random image file name
        $mt = explode(' ', microtime());
        $microtime = ((int)$mt[1]) * 1000 + ((int)round($mt[0] * 1000));
        $file = preg_replace("/\s+/", "_", $_FILES['image']['name']);

        $image = $microtime . "." . $extension;
        // upload new image
        if (!is_dir($target_path)) {
            mkdir($target_path, 0777, true);
        }
        $upload = move_uploaded_file($_FILES['image']['tmp_name'], '../upload/offers/' . $image);

        // insert new data to menu table
        $upload_image = 'upload/offers/' . $image;
        $sql = "INSERT INTO `offers`(`image`) VALUES ('$upload_image')";
        $db->sql($sql);
        $res = $db->getResult();
        $sql = "SELECT id FROM `offers` ORDER BY id DESC";
        $db->sql($sql);
        $res = $db->getResult();
        $response["message"] = "<p class='alert alert-success'>Image Uploaded Successfully</p>";
        $response["id"] = $res[0]['id'];
    } else {
        $response["message"] = "<p class='alert alert-danger'>Image could not be Uploaded!Try Again</p>";
    }
    echo json_encode($response);
}
if (isset($_GET['type']) && $_GET['type'] != '' && $_GET['type'] == 'delete-offer') {
    $permissions = $fn->get_permissions($_SESSION['id']);
    if ($permissions['new_offers']['delete'] == 0) {
        echo 2;
        return false;
    }
    $id        = $db->escapeString($fn->xss_clean($_GET['id']));
    $image     = $db->escapeString($fn->xss_clean($_GET['image']));

    if (!empty($image))
        unlink('../' . $image);

    $sql = 'DELETE FROM `offers` WHERE `id`=' . $id;
    if ($db->sql($sql)) {
        echo 1;
    } else {
        echo 0;
    }
}
if (isset($_POST['get-offer-images']) && !empty($_POST['get-offer-images'] && $_POST['get-offer-images'] == 1)) {
    if (!verify_token()) {
        return false;
    }
    $sql = 'select * from offers order by id desc';
    $db->sql($sql);
    $result = $db->getResult();
    $response = $temp = $temp1 = array();
    if (!empty($result)) {
        $response['error'] = false;
        foreach ($result as $row) {
            $temp['image'] = DOMAIN_URL . $row['image'];
            $temp1[] = $temp;
        }
        $response['data'] = $temp1;
    } else {
        $response['error'] = true;
        $response['message'] = "No offer images uploaded yet!";
    }
    print_r(json_encode($response));
}
