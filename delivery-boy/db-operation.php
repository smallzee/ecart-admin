<?php
session_start();
include('../includes/crud.php');
$db = new Database();
$db->connect();
$db->sql("SET NAMES 'utf8'");

include_once('../includes/custom-functions.php');
$fn = new custom_functions;
$config = $fn->get_configurations();
if(isset($config['system_timezone']) && isset($config['system_timezone_gmt'])){
    date_default_timezone_set($config['system_timezone']);
    $db->sql("SET `time_zone` = '".$config['system_timezone_gmt']."'");
}else{
    date_default_timezone_set('Asia/Kolkata');
    $db->sql("SET `time_zone` = '+05:30'");
}
if(ALLOW_MODIFICATION==0 && !defined (ALLOW_MODIFICATION)){
    echo '<label class="alert alert-danger">This operation is not allowed in demo panel!.</label>';
    return false;
}

if(isset($_POST['update_delivery_boy']) && isset($_POST['delivery_boy_id'])){
    $id = $db->escapeString($_POST['delivery_boy_id']);
    if(isset($_POST['old_password']) && $_POST['old_password'] != ''){
        $old_password = md5($_POST['old_password']);
        $sql = "SELECT `password` FROM delivery_boys WHERE id=".$id;
        $db->sql($sql);
        $res = $db->getResult();
        if($res[0]['password'] != $old_password){
            echo "<label class='alert alert-danger'>Old password does't match.</label>";
            return false;
        }
    }
    if($_POST['update_password'] !='' && $_POST['old_password'] == ''){
        echo "<label class='alert alert-danger'>Please enter old password.</label>";
        return false;
    }
    $name = $db->escapeString($_POST['update_name']);
    // $password = !empty($_POST['update_password'])?$db->escapeString($_POST['update_password']):'';
    $password = '12345678';
    $address = $db->escapeString($_POST['update_address']);
    $password = !empty($password)?md5($password):'';
    $update_dob = $db->escapeString($fn->xss_clean($_POST['update_dob']));
    $update_bank_name = $db->escapeString($fn->xss_clean($_POST['update_bank_name']));
    $update_account_number = $db->escapeString($fn->xss_clean($_POST['update_account_number']));
    $update_account_name = $db->escapeString($fn->xss_clean($_POST['update_account_name']));
    $update_ifsc_code = $db->escapeString($fn->xss_clean($_POST['update_ifsc_code']));
    $update_other_payment_info = !empty($_POST['update_other_payment_info']) ? $db->escapeString($fn->xss_clean($_POST['update_other_payment_info'])) : '';


    if ($_FILES['update_driving_license']['size'] != 0 && $_FILES['update_driving_license']['error'] == 0 && !empty($_FILES['update_driving_license']))
    {
        //image isn't empty and update the image
        $dr_image = $db->escapeString($fn->xss_clean($_POST['dr_image1']));
        // common image file extensions
        // $allowedExts = array("gif", "jpeg", "jpg", "png", "JPEG","JPG","PNG","GIF");
        // $extension = pathinfo($_FILES["update_driving_license"]["name"])['extension'];
        // if(!in_array($extension, $allowedExts)){
        //     echo '<p class="alert alert-danger">Image type is invalid!</p>';
        //     return false;
        //     exit();
        // }
        $result = $fn->validate_image($_FILES["update_driving_license"]);
        if ($result) {
            echo " <span class='label label-danger'>Driving License image type must jpg, jpeg, gif, or png!</span>";
            return false;
            exit();
        }
        $target_path = '../upload/delivery-boy/';
        $dr_filename = microtime(true).'.'. strtolower($extension);
        $dr_full_path = $target_path."".$dr_filename;
        if(!move_uploaded_file($_FILES["update_driving_license"]["tmp_name"], $dr_full_path)){
            echo '<p class="alert alert-danger">Can not upload image.</p>';
            return false;
            exit();
        }
        if(!empty($dr_image)){
            unlink($target_path.$dr_image);
        }
        $sql = "UPDATE delivery_boys SET `driving_license`='".$dr_filename."' WHERE `id`=".$id;
        $db->sql($sql);
    } 
    if ($_FILES['update_national_identity_card']['size'] != 0 && $_FILES['update_national_identity_card']['error'] == 0 && !empty($_FILES['update_national_identity_card']))
    {
        //image isn't empty and update the image
        $nic_image = $db->escapeString($_POST['nic_image']);
        // common image file extensions
        // $allowedExts = array("gif", "jpeg", "jpg", "png", "JPEG","JPG","PNG","GIF");
        // $extension = pathinfo($_FILES["update_national_identity_card"]["name"])['extension'];
        // if(!in_array($extension, $allowedExts)){
        //     echo '<p class="alert alert-danger">Image type is invalid!</p>';
        //     return false;
        //     exit();
        // }
        $result = $fn->validate_image($_FILES["update_national_identity_card"]);
        if ($result) {
            echo " <span class='label label-danger'>National Identity Card image type must jpg, jpeg, gif, or png!</span>";
            return false;
            exit();
        }
        $target_path = '../upload/delivery-boy/';
        $nic_filename = microtime(true).'.'. strtolower($extension);
        $nic_full_path = $target_path."".$nic_filename;
        if(!move_uploaded_file($_FILES["update_national_identity_card"]["tmp_name"], $nic_full_path)){
            echo '<p class="alert alert-danger">Can not upload image.</p>';
            return false;
            exit();
        }
        if(!empty($nic_image)){
            unlink($target_path.$nic_image);
        }
        $sql = "UPDATE delivery_boys SET `national_identity_card`='".$nic_filename."' WHERE `id`=".$id;
        $db->sql($sql);
    } 

    if(!empty($password)){
        $sql = "Update delivery_boys set `name`='".$name."',password='".$password."',`address`='".$address."' ,`dob`='$update_dob',`bank_account_number`='$update_account_number',`bank_name`='$update_bank_name',`account_name`='$update_account_name',`ifsc_code`='$update_ifsc_code',`other_payment_information`='$update_other_payment_info' where `id`=".$id;
    }else{
         $sql = "Update delivery_boys set `name`='".$name."',`address`='".$address."',`dob`='$update_dob',`bank_account_number`='$update_account_number',`bank_name`='$update_bank_name',`account_name`='$update_account_name',`ifsc_code`='$update_ifsc_code',`other_payment_information`='$update_other_payment_info' where `id`=".$id;
    }
    if($db->sql($sql)){
        echo "<label class='alert alert-success'>Information Updated Successfully.</label>";
    }else{
        echo "<label class='alert alert-danger'>Some Error Occurred! Please Try Again.</label>";

    }
}

?>