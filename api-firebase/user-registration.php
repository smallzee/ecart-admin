<?php
header('Access-Control-Allow-Origin: *');
session_start();
include '../includes/crud.php';
include '../includes/custom-functions.php';
$fn = new custom_functions;

include '../includes/variables.php';
include_once('verify-token.php');
$db = new Database();
$db->connect();
$fn = new custom_functions();
$settings = $fn->get_settings('system_timezone', true);
$app_name = $settings['app_name'];
include 'send-email.php';
include 'send-sms.php';

$config = $fn->get_configurations();
$time_slot_config = $fn->time_slot_config();
if (isset($config['system_timezone']) && isset($config['system_timezone_gmt'])) {
    date_default_timezone_set($config['system_timezone']);
    $db->sql("SET `time_zone` = '" . $config['system_timezone_gmt'] . "'");
} else {
    date_default_timezone_set('Asia/Kolkata');
    $db->sql("SET `time_zone` = '+05:30'");
}

$response = array();
$accesskey = $db->escapeString($fn->xss_clean($_POST['accesskey']));

if ($access_key != $accesskey) {
    $response['error'] = true;
    $response['message'] = "invalid accesskey";
    print_r(json_encode($response));
    return false;
}

if ((isset($_POST['type'])) && ($_POST['type'] == 'verify-user')) {
    if (!verify_token()) {
        return false;
    }
    $mobile = $db->escapeString($fn->xss_clean($_POST['mobile']));
    if (!empty($mobile) && $mobile != "") {
        $sql = 'select id from users where mobile =' . $mobile;
        $db->sql($sql);
        $res = $db->getResult();
        $num_rows = $db->numRows($res);
        if ($num_rows > 0) {
            $response["error"]   = true;
            $response["id"]   = $res[0]['id'];
            $response["message"] = "This mobile is already registered. Please login!";
            echo json_encode($response);
        } else if ($num_rows == 0) {
            $response["error"]   = false;
            $response["message"] = "Ready to sent firebase OTP request!";
            echo json_encode($response);
        }
    } else {
        $response['error'] = true;
        $response['message'] = "mobile is required.";
        echo json_encode($response);
    }
}

if (isset($_POST['type']) && $_POST['type'] != '' && $_POST['type'] == 'verify-user-email') {
    if (!verify_token()) {
        return false;
    }
    $email  = $db->escapeString($fn->xss_clean($_POST['email']));
    $otp    = rand(100000, 999999);

    $sql = "select `id`,`name` from `users` where `email`='" . $email . "'";
    $db->sql($sql);
    $result = $db->getResult();
    $num_rows = $db->numRows($result);
    if ($num_rows == 0) {
        //send email
        $to = $email;
        $subject = "$app_name Registration Verification";

        $message = "<#> Your OTP for $app_name Registration verification is : " . $otp . ". Please enter this OTP to activate your profile. ";

        if (!send_email($to, $subject, $message)) {
            $response["error"]   = true;
            $response["message"] = "Activation mail could not be sent!Try Again";
            echo json_encode($response);
            return false;
        }
        $response["error"]   = false;
        $response["message"] = "OTP for account activation is sent to your email. Please verify it to complete the registration process."/*.$smsResult*/;
        $response["OTP"] = $otp;
    } else {
        $response["error"]   = true;
        $response["message"] = "Email is already registered. Please login!";
    }
    echo json_encode($response);
}

if ((isset($_POST['type'])) && ($_POST['type'] == 'register')) {
    if (!verify_token()) {
        return false;
    }
    $name          = (isset($_POST['name'])) ? $db->escapeString($fn->xss_clean($_POST['name'])) : "";
    $mobile      = (isset($_POST['mobile'])) ? $db->escapeString($fn->xss_clean($_POST['mobile'])) : "";
    $country_code      = (isset($_POST['country_code'])) ? $db->escapeString($fn->xss_clean($_POST['country_code'])) : "";
    $fcm_id      = (isset($_POST['fcm_id'])) ? $db->escapeString($fn->xss_clean($_POST['fcm_id'])) : "";
    $dob      = (isset($_POST['dob'])) ? $db->escapeString($fn->xss_clean($_POST['dob'])) : "";
    $email      = (isset($_POST['email']) && !empty($_POST['email'])) ? $db->escapeString($fn->xss_clean($_POST['email'])) : "";
    $password     = md5($db->escapeString($fn->xss_clean($_POST['password'])));
    $city         = (isset($_POST['city_id'])) ? $db->escapeString($fn->xss_clean($_POST['city_id'])) : "";
    $area         = (isset($_POST['area_id'])) ? $db->escapeString($fn->xss_clean($_POST['area_id'])) : "";
    $street     = (isset($_POST['street'])) ? $db->escapeString($fn->xss_clean($_POST['street'])) : "";
    $pincode     = (isset($_POST['pincode'])) ? $db->escapeString($fn->xss_clean($_POST['pincode'])) : "";
    $api_key     = (isset($_POST['api_key'])) ? $db->escapeString($fn->xss_clean($_POST['api_key'])) : "";
    $latitude     = (isset($_POST['latitude'])) ? $db->escapeString($fn->xss_clean($_POST['latitude'])) : "0";
    $longitude     = (isset($_POST['longitude'])) ? $db->escapeString($fn->xss_clean($_POST['longitude'])) : "0";
    $status     = 1;
    $chars = "0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ";
    $referral_code  = "";
    for ($i = 0; $i < 10; $i++) {
        $referral_code .= $chars[mt_rand(0, strlen($chars) - 1)];
    }
    if (isset($_POST['friends_code']) && $_POST['friends_code'] != '') {
        $friend_code = $db->escapeString($fn->xss_clean($_POST['friends_code']));
        $sql = "SELECT id FROM users WHERE referral_code='" . $friend_code . "'";
        $db->sql($sql);
        $result = $db->getResult();
        $num_rows = $db->numRows($result);
        if ($num_rows > 0) {
            $friends_code = $db->escapeString($fn->xss_clean($_POST['friends_code']));
        } else {
            $response["error"]   = true;
            $response["message"] = "Invalid friends code!";
            echo json_encode($response);
            return false;
        }
    } else {
        $friends_code = '';
    }

    if (!empty($mobile)) {
        $sql = "select mobile from users where mobile='" . $mobile . "'";
        $db->sql($sql);
        $res = $db->getResult();
        $num_rows = $db->numRows($res);
        if ($num_rows > 0) {

            $response["error"]   = true;
            $response["message"] = "This mobile $mobile is already registered. Please login!";

            echo json_encode($response);
        } else if ($num_rows == 0) {
            if (isset($_FILES['profile']) && !empty($_FILES['profile']) && $_FILES['profile']['error'] == 0 && $_FILES['profile']['size'] > 0) {
                $profile = $db->escapeString($fn->xss_clean($_FILES['profile']['name']));
                if (!is_dir('../upload/profile/')) {
                    mkdir('../upload/profile/', 0777, true);
                }
                $extension = pathinfo($_FILES["profile"]["name"])['extension'];
                $result = $fn->validate_image($_FILES["profile"]);
                if ($result) {
                    $response["error"]   = true;
                    $response["message"] = "Image type must jpg, jpeg, gif, or png!";
                    echo json_encode($response);
                    return false;
                }
                // $mimetype = mime_content_type($_FILES["profile"]["tmp_name"]);
                // if (!in_array($mimetype, array('image/jpg', 'image/jpeg', 'image/gif', 'image/png'))) {
                //     $response["error"]   = true;
                //     $response["message"] = "Image type must jpg, jpeg, gif, or png!";
                //     echo json_encode($response);
                //     return false;
                // }

                $filename = microtime(true) . '.' . strtolower($extension);
                $full_path = '../upload/profile/' . "" . $filename;
                if (!move_uploaded_file($_FILES["profile"]["tmp_name"], $full_path)) {
                    $response["error"]   = true;
                    $response["message"] = "Invalid directory to load profile!";
                    echo json_encode($response);
                    return false;
                }
            } else {
                $filename = 'default_user_profile.png';
                $full_path = '../upload/profile/' . "" . $filename;
            }
            //user is not registered, insert the data to the database  
            $sql = "INSERT INTO `users`(`name`, `email`,`profile`, `mobile`,`dob`, `city`,`area`, `street` , `pincode`, `apikey`, `password`,`referral_code`,`friends_code`,`fcm_id`,`latitude`,`longitude`,`status`,`country_code`) VALUES 
			('$name','$email','$filename','$mobile','$dob','$city','$area','$street','$pincode','$api_key','$password','$referral_code','$friends_code','$fcm_id','$latitude','$longitude',$status,'$country_code')";
            $data = array(
                'name' => $name,
                'email' => $email,
                'profile' => DOMAIN_URL . 'upload/profile/' . "" . $filename,
                'mobile' => $mobile,
                'country_code' => $country_code,
                'fcm_id' => $fcm_id,
                'dob' => $dob,
                'city' => $city,
                'area' => $area,
                'street' => $street,
                'pincode' => $pincode,
                'apikey' => $api_key,
                'password' => $password,
                'referral_code' => $referral_code,
                'friends_code' => $friends_code,
                'latitude' => $latitude,
                'longitude' => $longitude,
                'status' => $status
            );
            $db->sql($sql);
            $res = $db->getResult();
            $usr_id = $fn->get_data($columns = ['id'], 'mobile = "' . $mobile . '"', 'users');

            $sql = "DELETE FROM devices where fcm_id = '$fcm_id' ";
            $db->sql($sql);
            $res = $db->getResult();

            $sql_query = "SELECT *,(SELECT name FROM area a WHERE a.id=u.area) as area_name,(SELECT name FROM city c WHERE c.id=u.city) as city_name FROM `users` u WHERE `mobile` = '" . $mobile . "' AND `password` ='" . $password . "'";
            $db->sql($sql_query);
            $result = $db->getResult();
            if ($db->numRows($result) > 0) {
                $response["error"]   = false;
                $response["message"] = "User registered successfully";
                // $response["user_id"] = $usr_id[0]['id'];
                // $response['name'] = $data['name'];
                // $response['email'] = $data['email'];
                // $response['profile'] = $data['profile'];
                // $response['mobile']  = $data['mobile'];
                $response['password']  = $data['password'];
                // $response['referral_code']     = $data['referral_code'];
                foreach ($result as $row) {
                    $response['error']     = false;
                    $response['user_id'] = $row['id'];
                    $response['name'] = $row['name'];
                    $response['email'] = $row['email'];
                    $response['profile'] = DOMAIN_URL . 'upload/profile/' . "" . $row['profile'];
                    $response['mobile'] = $row['mobile'];
                    $response['country_code'] = $row['country_code'];
                    $response['dob'] = $row['dob'];
                    $response['balance'] = $row['balance'];
                    $response['city_id'] = !empty($row['city']) ? $row['city'] : '';
                    $response['city_name'] = !empty($row['city_name']) ? $row['city_name'] : '';
                    $response['area_id'] = !empty($row['area']) ? $row['area'] : '';
                    $response['area_name'] = !empty($row['area_name']) ? $row['area_name'] : '';
                    $response['street'] = $row['street'];
                    $response['pincode'] = $row['pincode'];
                    $response['referral_code'] = $row['referral_code'];
                    $response['friends_code'] = $row['friends_code'];
                    $response['latitude'] = (!empty($row['latitude'])) ? $row['latitude'] : '0';
                    $response['longitude'] = (!empty($row['longitude'])) ? $row['longitude'] : '0';
                    $response['apikey'] = $row['apikey'];
                    $response['status'] = $row['status'];
                    $response['created_at'] = $row['created_at'];
                }
                echo json_encode($response);
            }
        }
    } else {
        echo "Email is required.";
    }
}

if ((isset($_POST['type'])) && ($_POST['type'] == 'upload_profile')) {
    if (!verify_token()) {
        return false;
    }
    if (!isset($_POST['user_id']) && empty($_POST['user_id'])) {
        $response["error"]   = true;
        $response["message"] = "User id is missing.";
        print_r(json_encode($response));
        return false;
        exit();
    }
    $id = $db->escapeString($fn->xss_clean($_POST['user_id']));
    $sql = 'select * from users where id =' . $id;
    $db->sql($sql);
    $res = $db->getResult();

    if (!empty($res)) {
        if (isset($_FILES['profile']) && !empty($_FILES['profile']) && $_FILES['profile']['error'] == 0 && $_FILES['profile']['size'] > 0) {

            if (!is_dir('../upload/profile/')) {
                mkdir('../upload/profile/', 0777, true);
            }
            if (!empty($res[0]['profile'])) {
                $old_image = $res[0]['profile'];
                if ($old_image != 'default_user_profile.png' && !empty($old_image)) {
                    unlink('../upload/profile/' . $old_image);
                }
            }
            $profile = $db->escapeString($fn->xss_clean($_FILES['profile']['name']));
            $extension = pathinfo($_FILES["profile"]["name"])['extension'];
            $result = $fn->validate_image($_FILES["profile"]);
            if ($result) {
                $response["error"]   = true;
                $response["message"] = "Image type must jpg, jpeg, gif, or png!";
                echo json_encode($response);
                return false;
            }
            // $mimetype = mime_content_type($_FILES["profile"]["tmp_name"]);
            // if (!in_array($mimetype, array('image/jpg', 'image/jpeg', 'image/gif', 'image/png'))) {
            //     $response["error"]   = true;
            //     $response["message"] = "Image type must jpg, jpeg, gif, or png!";
            //     echo json_encode($response);
            //     return false;
            // }
            $filename = microtime(true) . '.' . strtolower($extension);
            $full_path = '../upload/profile/' . "" . $filename;
            if (!move_uploaded_file($_FILES["profile"]["tmp_name"], $full_path)) {
                $response["error"]   = true;
                $response["message"] = "Invalid directory to load profile!";
                echo json_encode($response);
                return false;
            }
            $sql = "UPDATE users SET `profile`='" . $filename . "' WHERE `id`=" . $id;
            if ($db->sql($sql)) {
                $profile = $fn->get_data($columns = ['profile'], 'id = "' . $id . '"', 'users');
                $profile_url = DOMAIN_URL . 'upload/profile/' . "" . $profile[0]['profile'];
                $response["error"]   = false;
                $response["profile"]   = $profile_url;
                $response["message"] = "Profile has been updated successfully.";
            } else {
                $response["error"]   = true;
                $response["message"] = "Profile is not updated.";
            }
        } else {
            $response["error"]   = true;
            $response["message"] = "Profile parameter is missing.";
        }
    } else {
        $response["error"]   = true;
        $response["message"] = "User does not exist.";
    }
    echo json_encode($response);
}


if (isset($_POST['type']) && $_POST['type'] != '' && $_POST['type'] == 'edit-profile') {
    if (!verify_token()) {
        return false;
    }
    $id       = $db->escapeString($fn->xss_clean($_POST['id']));
    $name   = $db->escapeString($fn->xss_clean($_POST['name']));
    $email  = $db->escapeString($fn->xss_clean($_POST['email']));
    $city   = $db->escapeString($fn->xss_clean($_POST['city_id']));
    $area   = $db->escapeString($fn->xss_clean($_POST['area_id']));
    $street = $db->escapeString($fn->xss_clean($_POST['street']));
    $pincode = $db->escapeString($fn->xss_clean($_POST['pincode']));
    $dob = $db->escapeString($fn->xss_clean($_POST['dob']));
    $latitude     = (isset($_POST['latitude']) && !empty($_POST['latitude'])) ? $db->escapeString($fn->xss_clean($_POST['latitude'])) : "0";
    $longitude     = (isset($_POST['longitude']) && !empty($_POST['longitude'])) ? $db->escapeString($fn->xss_clean($_POST['longitude'])) : "0";

    $sql = 'select * from users where id =' . $id;
    $db->sql($sql);
    $res = $db->getResult();

    if (!empty($res)) {

        if (isset($_FILES['profile']) && !empty($_FILES['profile']) && $_FILES['profile']['error'] == 0 && $_FILES['profile']['size'] > 0) {
            if (!empty($res[0]['profile'])) {
                $old_image = $res[0]['profile'];
                if ($old_image != 'default_user_profile.png' && !empty($old_image)) {
                    unlink('../upload/profile/' . $old_image);
                }
            }

            $profile = $db->escapeString($fn->xss_clean($_FILES['profile']['name']));
            $extension = pathinfo($_FILES["profile"]["name"])['extension'];
            $result = $fn->validate_image($_FILES["profile"]);
            if ($result) {
                $response["error"]   = true;
                $response["message"] = "Image type must jpg, jpeg, gif, or png!";
                echo json_encode($response);
                return false;
            }
            // $mimetype = mime_content_type($_FILES["profile"]["tmp_name"]);
            // if (!in_array($mimetype, array('image/jpg', 'image/jpeg', 'image/gif', 'image/png'))) {
            //     $response["error"]   = true;
            //     $response["message"] = "Image type must jpg, jpeg, gif, or png!";
            //     echo json_encode($response);
            //     return false;
            // }
            $filename = microtime(true) . '.' . strtolower($extension);
            $full_path = '../upload/profile/' . "" . $filename;
            if (!move_uploaded_file($_FILES["profile"]["tmp_name"], $full_path)) {
                $response["error"]   = true;
                $response["message"] = "Invalid directory to load profile!";
                echo json_encode($response);
                return false;
            }
            $sql = "UPDATE users SET `profile`='" . $filename . "' WHERE `id`=" . $id;
            $db->sql($sql);
        }

        $sql = 'UPDATE `users` SET `name`="' . $name . '",`email`="' . $email . '",`dob`="' . $dob . '",`city`="' . $city . '",`area`="' . $area . '",`street`="' . $street . '",`pincode`="' . $pincode . '",`latitude`="' . $latitude . '",`longitude`="' . $longitude . '" WHERE `id`=' . $id;
        $db->sql($sql);

        $response["error"]   = false;
        $response["message"] = "Profile has been updated successfully.";
    } else {
        $response["error"]   = true;
        $response["message"] = "valid id is required!!";
    }
    echo json_encode($response);
}

if (isset($_POST['type']) && $_POST['type'] != '' && $_POST['type'] == 'change-password') {
    if (!verify_token()) {
        return false;
    }

    $id       = $db->escapeString($fn->xss_clean($_POST['id']));
    $password = $db->escapeString($fn->xss_clean($_POST['password']));
    $password = md5($password);

    $sql = 'UPDATE `users` SET `password`="' . $password . '" WHERE `id`=' . $id;
    if ($db->sql($sql)) {
        $response["error"]   = false;
        $response["message"] = "Profile updated successfully";
    } else {
        $response["error"]   = true;
        $response["message"] = "Something went wrong! Try Again!";
    }
    echo json_encode($response);
}

if (isset($_POST['type']) && $_POST['type'] != '' && $_POST['type'] == 'forgot-password-email') {
    if (!verify_token()) {
        return false;
    }
    $email  = $db->escapeString($fn->xss_clean($_POST['email']));
    $password = rand(10000, 99999);
    $encrypted_password = md5($password);

    $sql = "select `id`,`name` from `users` where `email`='" . $email . "'";
    $db->sql($sql);
    $result = $db->getResult();
    if ($db->numRows($result)) {
        //send email
        $to = $email;
        $subject = "Password Recovery Mail - Password is reset ( $email )";
        $message = "Hi, <b>" . $result[0]['name'] . " - " . $email . "</b>, \t\r\n Your Password has been reset. Please Login with the new password. \r\nYour new Password is : " . $password . "\r\n Thank you";

        if (!send_email($to, $subject, $message)) {
            $response["error"]   = true;
            $response["message"] = "Password could not be reset!Try Again";
            echo json_encode($response);
            return false;
        }
        $sql = 'UPDATE `users` SET `password`="' . $encrypted_password . '" WHERE `email`="' . $email . '"';
        if ($db->sql($sql)) {
            $response["error"]   = false;
            $response["message"] = "Password updated successfully! Please check the mail!";
        }
    } else {
        $response["error"]   = true;
        $response["message"] = "Email ID does not exist!";
    }
    echo json_encode($response);
}


if (isset($_POST['type']) && $_POST['type'] != '' && $_POST['type'] == 'forgot-password-mobile') {
    if (!verify_token()) {
        return false;
    }
    $mobile  = $db->escapeString($fn->xss_clean($_POST['mobile']));
    $password = 'test1234';
    $encrypted_password = md5($password);
    $sql = "select `id`,`name`,`country_code` from `users` where `mobile`='" . $mobile . "'";
    $db->sql($sql);
    $result = $db->getResult();

    if ($db->numRows($result) > 0) {
        $country_code = $result[0]['country_code'];
        //send sms
        $message = 'Your Password for ' . $app_name . ' is Reset. Please login using new Password : ' . $password . '.';
        $sql = 'UPDATE `users` SET `password`="' . $encrypted_password . '" WHERE `mobile`="' . $mobile . '"';
        if ($db->sql($sql)) {
            // sendSms($mobile,$message,$country_code);    
            $response["error"]   = false;
            $response["message"] = "Password is sent successfully! Please login via the OTP sent to your mobile number!";
        }
    } else {
        $response["error"]   = true;
        $response["message"] = "Mobile number does not exist! Please Register";
    }
    echo json_encode($response);
}


if (isset($_POST['type']) && $_POST['type'] != '' && $_POST['type'] == 'delete-notification') {
    $permissions = $fn->get_permissions($_SESSION['id']);
    if ($permissions['notifications']['delete'] == 0) {
        echo 2;
        return false;
    }
    $id        = $db->escapeString($fn->xss_clean($_POST['id']));
    $image     = $db->escapeString($fn->xss_clean($_POST['image']));

    if (!empty($image))
        unlink('../' . $image);

    $sql = 'DELETE FROM `notifications` WHERE `id`=' . $id;
    if ($db->sql($sql)) {
        echo 1;
    } else {
        echo 0;
    }
}

if (isset($_POST['type']) && $_POST['type'] != '' && $_POST['type'] == 'register-device') {
    if (!verify_token()) {
        return false;
    }
    $user_id  = $db->escapeString($fn->xss_clean($_POST['user_id']));
    $token  = $db->escapeString($fn->xss_clean($_POST['token']));

    $sql = "select `id` from `users` where `id`='" . $user_id . "'";
    $db->sql($sql);
    $result = $db->getResult();
    if ($db->numRows($result) > 0) {
        // Update the Device ID
        $sql = 'UPDATE `users` SET `fcm_id`="' . $token . '" WHERE `id`="' . $user_id . '"';
        if ($db->sql($sql)) {
            $response["error"]   = false;
            $response["message"] = "Device updated successfully";
        }
    } else {
        $response["error"]   = true;
        $response["message"] = "User does't exists.";
    }
    echo json_encode($response);
}

if (isset($_POST['type']) && $_POST['type'] != '' && $_POST['type'] == 'send-invitation') {
    if (!verify_token()) {
        return false;
    }
    $referral_code = $db->escapeString($fn->xss_clean($_POST['referral_code']));
    $friend_id  = $db->escapeString($fn->xss_clean($_POST['friend_id']));
    $sql = "select * from `users` where `referral_code`='" . $referral_code . "'";
    $db->sql($sql);
    $result = $db->getResult();
    if ($db->numRows($result) > 0) {
        $sql = 'UPDATE `users` SET `friends_code`="' . $referral_code . '" WHERE `id`="' . $friend_id . '"';
        if ($db->sql($sql)) {
            $response["error"]   = false;
            $response["message"] = "Invitation sent successfully";
            $response['data'] = $result;
        }
    } else {
        $response["error"]   = true;
        $response["message"] = "Invalid referral code.";
    }
    echo json_encode($response);
}
