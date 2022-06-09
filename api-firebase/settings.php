<?php
header('Access-Control-Allow-Origin: *');
header("Content-Type: application/json");
header("Expires: 0");
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
header("Cache-Control: no-store, no-cache, must-revalidate");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

include('../includes/crud.php');
include('../includes/variables.php');
include_once('verify-token.php');
include_once('../includes/custom-functions.php');
$fn = new custom_functions;
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

$accesskey = $db->escapeString($fn->xss_clean($_POST['accesskey']));
if (!isset($_POST['accesskey']) || $access_key != $accesskey) {
    $response['error'] = true;
    $response['message'] = "invalid accesskey";
    print_r(json_encode($response));
    return false;
}
if (!verify_token()) {
    return false;
}
$settings = $setting = array();

if (isset($_POST['settings'])) {
    if (isset($_POST['get_payment_methods'])) {
        $sql = "select value from `settings` where `variable`='payment_methods'";
        $db->sql($sql);
        $res = $db->getResult();

        if (!empty($res)) {
            $payment_methods = json_decode($res[0]['value']);
            if (!isset($payment_methods->paytm_payment_method)) {
                $payment_methods->paytm_payment_method = 0;
                $payment_methods->paytm_mode = "sandbox";
                $payment_methods->paytm_merchant_key = "";
                $payment_methods->paytm_merchant_id = "";
            }
            $settings['error'] = false;
            $settings['payment_methods'] = $payment_methods;
            print_r(json_encode($settings));
        } else {
            $settings['error'] = true;
            $settings['settings'] = "No settings found!";
            $settings['message'] = "Something went wrong!";
            print_r(json_encode($settings));
        }
    }
    if (isset($_POST['get_privacy'])) {
        $sql = "select value from `settings` where variable='privacy_policy'";
        $db->sql($sql);
        $res = $db->getResult();
        if (!empty($res)) {
            $settings['error'] = false;
            $settings['privacy'] = $res[0]['value'];
            print_r(json_encode($settings));
        } else {
            $settings['error'] = true;
            $settings['settings'] = "No settings found!";
            $settings['message'] = "Something went wrong!";
            print_r(json_encode($settings));
        }
    }
    if (isset($_POST['get_terms'])) {
        $sql = "select value from `settings` where variable='terms_conditions'";
        $db->sql($sql);
        $res = $db->getResult();
        if (!empty($res)) {
            $settings['error'] = false;
            $settings['terms'] = $res[0]['value'];
            print_r(json_encode($settings));
        } else {
            $settings['error'] = true;
            $settings['settings'] = "No settings found!";
            $settings['message'] = "Something went wrong!";
            print_r(json_encode($settings));
        }
    }
    if (isset($_POST['get_logo'])) {
        $sql = "select value from `settings` where variable='Logo' OR variable='logo'";
        $db->sql($sql);
        $res = $db->getResult();
        if (!empty($res)) {
            $settings['error'] = false;
            $settings['logo'] = DOMAIN_URL . $res[0]['value'];
            print_r(json_encode($settings));
        } else {
            $settings['error'] = true;
            $settings['settings'] = "No settings found!";
            $settings['message'] = "Something went wrong!";
            print_r(json_encode($settings));
        }
    }
    if (isset($_POST['get_contact'])) {
        $sql = "select value from `settings` where variable='contact_us'";
        $db->sql($sql);
        $res = $db->getResult();
        if (!empty($res)) {
            $settings['error'] = false;
            $settings['contact'] = $res[0]['value'];
            print_r(json_encode($settings));
        } else {
            $settings['error'] = true;
            $settings['settings'] = "No settings found!";
            $settings['message'] = "Something went wrong!";
            print_r(json_encode($settings));
        }
    }
    if (isset($_POST['get_about_us'])) {
        $sql = "select value from `settings` where variable='about_us'";
        $db->sql($sql);
        $res = $db->getResult();
        if (!empty($res)) {
            $settings['error'] = false;
            $settings['about'] = $res[0]['value'];
            print_r(json_encode($settings));
        } else {
            $settings['error'] = true;
            $settings['settings'] = "No settings found!";
            $settings['message'] = "Something went wrong!";
            print_r(json_encode($settings));
        }
    }
    if (isset($_POST['get_timezone'])) {
        $sql = "select value from `settings` where variable='system_timezone'";
        $db->sql($sql);
        $res = $db->getResult();
        $array = json_decode($res[0]['value'], true);
        $currency = $fn->get_settings('currency');
        function replaceArrayKeys($array)
        {
            $replacedKeys = str_replace('-', '_', array_keys($array));
            return array_combine($replacedKeys, $array);
        }
        if (!empty($res)) {
            $settings['error'] = false;
            $settings['settings'] = replaceArrayKeys($array);
            $settings['settings']['currency'] = $currency;
            print_r(json_encode($settings));
        } else {
            $settings['error'] = true;
            $settings['settings'] = "No settings found!";
            $settings['message'] = "Something went wrong!";
            print_r(json_encode($settings));
        }
    }
    if (isset($_POST['get_fcm_key'])) {
        $sql = "select value from `settings` where variable='fcm_server_key'";
        $db->sql($sql);
        $res = $db->getResult();
        if (!empty($res)) {
            $settings['error'] = false;
            $settings['fcm'] = $res[0]['value'];
            print_r(json_encode($settings));
        } else {
            $settings['error'] = true;
            $settings['settings'] = "No settings found!";
            $settings['message'] = "Something went wrong!";
            print_r(json_encode($settings));
        }
    }
    if (isset($_POST['get_time_slot_config'])) {
        $sql = "select value from `settings` where variable='time_slot_config'";
        $db->sql($sql);
        $res = $db->getResult();
        if (!empty($res)) {
            $settings['error'] = false;
            $settings['time_slot_config'] = json_decode($res[0]['value']);
            print_r(json_encode($settings));
        } else {
            $settings['error'] = true;
            $settings['settings'] = "No settings found!";
            $settings['message'] = "Something went wrong!";
            print_r(json_encode($settings));
        }
    }
    if (isset($_POST['get_front_end_settings'])) {
        $sql = "select * from `settings` where variable='front_end_settings'";
        $db->sql($sql);
        $res = $db->getResult();
        if (!empty($res)) {
            $res[0]['value'] = json_decode($res[0]['value'], true);
            $res[0]['value']['favicon'] = DOMAIN_URL . 'dist/img/' . $res[0]['value']['favicon'];
            $res[0]['value']['screenshots'] = DOMAIN_URL . 'dist/img/' . $res[0]['value']['screenshots'];
            $res[0]['value']['google_play'] = DOMAIN_URL . 'dist/img/' . $res[0]['value']['google_play'];
            $settings['error'] = false;
            $settings['front_end_settings'] = $res;
            print_r(json_encode($settings));
        } else {
            $settings['error'] = true;
            $settings['front_end_settings'] = null;
            $settings['message'] = "No active time slots found!";
            print_r(json_encode($settings));
        }
    }
} else if (isset($_POST['get_time_slots'])) {
    $sql = "select * from `time_slots` where status=1 ORDER BY `last_order_time` ASC";
    $db->sql($sql);
    $res = $db->getResult();
    if (!empty($res)) {
        $settings['error'] = false;
        $settings['time_slots'] = $res;
        print_r(json_encode($settings));
    } else {
        $settings['error'] = true;
        $settings['time_slots'] = null;
        $settings['message'] = "No active time slots found!";
        print_r(json_encode($settings));
    }
} elseif (isset($_POST['all'])) {
    $sql = "select variable, value from `settings` where 1";
    $db->sql($sql);
    $res = $db->getResult();
    if (!empty($res)) {
        $settings['error'] = false;
        $settings['data'] = array();
        foreach ($res as $k => $v) {
            if ($v['variable'] == "system_timezone") {
                $system_timezone = (array)json_decode($v['value']);
                foreach ($system_timezone as $k => $v) {
                    $settings['data'][$k] = $v;
                }
            } else {
                $settings['data'][$v['variable']] = $v['value'];
            }
        }
        print_r(json_encode($settings));
    } else {
        $settings['error'] = true;
        $settings['settings'] = "No settings found!";
        $settings['message'] = "Something went wrong!";
        print_r(json_encode($settings));
    }
} else {
    die('Something Wrong!!.');
}
$db->disconnect();
