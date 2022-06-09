<?php
session_start();
include '../includes/crud.php';
include_once('../includes/variables.php');
include_once('../includes/custom-functions.php');


header("Content-Type: application/json");
header("Expires: 0");
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
header("Cache-Control: no-store, no-cache, must-revalidate");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header('Access-Control-Allow-Origin: *');
// date_default_timezone_set('Asia/Kolkata');

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
if (!isset($_POST['accesskey'])) {
    $response['error'] = true;
    $response['message'] = "Access key is invalid or not passed!";
    print_r(json_encode($response));
    return false;
}
$accesskey = $db->escapeString($fn->xss_clean($_POST['accesskey']));
if ($access_key != $accesskey) {
    $response['error'] = true;
    $response['message'] = "invalid accesskey!";
    print_r(json_encode($response));
    return false;
}

if (!verify_token()) {
    return false;
}
/*
1.add_address
    accesskey:90336
    add_address:1
    user_id:3
    type:Home/Office
    name:John Smith
    country_code:+91  {optional}
    mobile:1234567890
    alternate_mobile:9876543210 {optional}
    address:Time Square Empire
    landmark:Bhuj-Mirzapar Highway
    area_id:1
    city_id:2
    pincode:0123456
    state:Gujarat
    country:India
    latitude:value {optional}
    longitude:value {optional}
    is_default:0/1 {optional}
*/
if ((isset($_POST['add_address'])) && ($_POST['add_address'] == 1)) {
    $user_id = (isset($_POST['user_id']) && !empty($_POST['user_id'])) ? trim($db->escapeString($fn->xss_clean($_POST['user_id']))) : "";
    $type = (isset($_POST['type']) && !empty($_POST['type'])) ? trim($db->escapeString($fn->xss_clean($_POST['type']))) : "";
    $name  = (isset($_POST['name']) && !empty($_POST['name'])) ? trim($db->escapeString($fn->xss_clean($_POST['name']))) : "";
    $country_code  = (isset($_POST['country_code']) && !empty($_POST['country_code'])) ? trim($db->escapeString($fn->xss_clean($_POST['country_code']))) : "";
    $mobile = (isset($_POST['mobile']) && !empty($_POST['mobile'])) ? trim($db->escapeString($fn->xss_clean($_POST['mobile']))) : "";
    $alternate_mobile = (isset($_POST['alternate_mobile']) && !empty($_POST['alternate_mobile'])) ? trim($db->escapeString($fn->xss_clean($_POST['alternate_mobile']))) : "";
    $address = (isset($_POST['address']) && !empty($_POST['address'])) ? trim($db->escapeString($fn->xss_clean($_POST['address']))) : "";
    $landmark = (isset($_POST['landmark']) && !empty($_POST['landmark'])) ? trim($db->escapeString($fn->xss_clean($_POST['landmark']))) : "";
    $area_id = (isset($_POST['area_id']) && !empty($_POST['area_id'])) ? trim($db->escapeString($fn->xss_clean($_POST['area_id']))) : "";
    $city_id = (isset($_POST['city_id']) && !empty($_POST['city_id'])) ? trim($db->escapeString($fn->xss_clean($_POST['city_id']))) : "";
    $pincode = (isset($_POST['pincode']) && !empty($_POST['pincode'])) ? trim($db->escapeString($fn->xss_clean($_POST['pincode']))) : "";
    $state = (isset($_POST['state']) && !empty($_POST['state'])) ? trim($db->escapeString($fn->xss_clean($_POST['state']))) : "";
    $country = (isset($_POST['country']) && !empty($_POST['country'])) ? trim($db->escapeString($fn->xss_clean($_POST['country']))) : "";
    $latitude = (isset($_POST['latitude']) && !empty($_POST['latitude'])) ? trim($db->escapeString($fn->xss_clean($_POST['latitude']))) : "0";
    $longitude = (isset($_POST['longitude']) && !empty($_POST['longitude'])) ? trim($db->escapeString($fn->xss_clean($_POST['longitude']))) : "0";
    $is_default = (isset($_POST['is_default']) && !empty($_POST['is_default'])) ? trim($db->escapeString($fn->xss_clean($_POST['is_default']))) : "0";

    if (!empty($user_id) && !empty($type) && !empty($name) && !empty($mobile) && !empty($address) && !empty($landmark) && !empty($area_id) && !empty($city_id) && !empty($pincode) && !empty($state) && !empty($country)) {
        if ($is_default == 1) {
            $fn->remove_other_addresses_from_default($user_id);
        }
        $data = array(
            'user_id' => $user_id,
            'type' => $type,
            'name' => $name,
            'country_code' => $country_code,
            'mobile' => $mobile,
            'alternate_mobile' => $alternate_mobile,
            'address' => $address,
            'landmark' => $landmark,
            'area_id' => $area_id,
            'city_id' => $city_id,
            'pincode' => $pincode,
            'state' => $state,
            'country' => $country,
            'latitude' => $latitude == "" ? "0" : $latitude,
            'longitude' => $longitude == "" ? "0" : $longitude,
            'is_default' => $is_default
        );
        if ($db->insert('user_addresses', $data)) {
            $d_charges = $fn->get_data($columns = ['minimum_free_delivery_order_amount', 'delivery_charges'], 'id=' . $area_id, 'area');
            $res = $db->getResult();
            $response['error'] = false;
            $response['message'] = 'Address added successfully';
            $response["id"] = "$res[0]";
            $response['user_id'] = $user_id;
            $response['type'] = $type;
            $response['name'] = $name;
            $response['country_code'] = $country_code;
            $response['mobile'] = $mobile;
            $response['alternate_mobile'] = $alternate_mobile;
            $response['address'] = $address;
            $response['landmark'] = $landmark;
            $response['area_id'] = $area_id;
            $response['city_id'] = $city_id;
            $response['pincode'] = $pincode;
            $response['state'] = $state;
            $response['country'] = $country;
            $response['latitude'] = $latitude == "" ? "0" : $latitude;
            $response['longitude'] = $longitude == "" ? "0" : $longitude;
            $response['is_default'] = $is_default == "" ? "0" : $is_default;
            $response['minimum_free_delivery_order_amount'] = (!empty($d_charges[0]['minimum_free_delivery_order_amount'])) ? $d_charges[0]['minimum_free_delivery_order_amount'] : "0";
            $response['delivery_charges'] = (!empty($d_charges[0]['delivery_charges'])) ? $d_charges[0]['delivery_charges'] : "0";
        } else {
            $response['error'] = true;
            $response['message'] = 'Something went wrong please try again!';
        }
    } else {
        $response['error'] = true;
        $response['message'] = 'Please pass all the fields!';
    }
    print_r(json_encode($response));
    return false;
}
/*
2.update_address
    accesskey:90336
    update_address:1
    id:1
    user_id:1
    type:Home/Office {optional}
    name:John Smith {optional}
    country_code:+91 {optional}
    mobile:1234567890 {optional}
    alternate_mobile:9876543210 {optional}
    address:Time Square Empire {optional}
    landmark:Bhuj-Mirzapar Highway {optional}
    area_id:1 {optional}
    city_id:2 {optional}
    pincode:0123456 {optional}
    state:Gujarat {optional}
    country:India {optional}
    latitude:value {optional}
    longitude:value {optional}
    is_default:0/1
*/
if ((isset($_POST['update_address'])) && ($_POST['update_address'] == 1)) {
    $id = (isset($_POST['id']) && !empty($_POST['id'])) ? trim($db->escapeString($fn->xss_clean($_POST['id']))) : "";
    $user_id = (isset($_POST['user_id']) && !empty($_POST['user_id'])) ? trim($db->escapeString($fn->xss_clean($_POST['user_id']))) : "";
    $type = (isset($_POST['type']) && !empty($_POST['type'])) ? trim($db->escapeString($fn->xss_clean($_POST['type']))) : "";
    $name  = (isset($_POST['name']) && !empty($_POST['name'])) ? trim($db->escapeString($fn->xss_clean($_POST['name']))) : "";
    $country_code  = (isset($_POST['country_code']) && !empty($_POST['country_code'])) ? trim($db->escapeString($fn->xss_clean($_POST['country_code']))) : "";
    $mobile = (isset($_POST['mobile']) && !empty($_POST['mobile'])) ? trim($db->escapeString($fn->xss_clean($_POST['mobile']))) : "";
    $alternate_mobile = (isset($_POST['alternate_mobile']) && !empty($_POST['alternate_mobile'])) ? trim($db->escapeString($fn->xss_clean($_POST['alternate_mobile']))) : "";
    $address = (isset($_POST['address']) && !empty($_POST['address'])) ? trim($db->escapeString($fn->xss_clean($_POST['address']))) : "";
    $landmark = (isset($_POST['landmark']) && !empty($_POST['landmark'])) ? trim($db->escapeString($fn->xss_clean($_POST['landmark']))) : "";
    $area_id = (isset($_POST['area_id']) && !empty($_POST['area_id'])) ? trim($db->escapeString($fn->xss_clean($_POST['area_id']))) : "";
    $city_id = (isset($_POST['city_id']) && !empty($_POST['city_id'])) ? trim($db->escapeString($fn->xss_clean($_POST['city_id']))) : "";
    $pincode = (isset($_POST['pincode']) && !empty($_POST['pincode'])) ? trim($db->escapeString($fn->xss_clean($_POST['pincode']))) : "";
    $state = (isset($_POST['state']) && !empty($_POST['state'])) ? trim($db->escapeString($fn->xss_clean($_POST['state']))) : "";
    $country = (isset($_POST['country']) && !empty($_POST['country'])) ? trim($db->escapeString($fn->xss_clean($_POST['country']))) : "";
    $latitude = (isset($_POST['latitude']) && !empty($_POST['latitude'])) ? trim($db->escapeString($fn->xss_clean($_POST['latitude']))) : "0";
    $longitude = (isset($_POST['longitude']) && !empty($_POST['longitude'])) ? trim($db->escapeString($fn->xss_clean($_POST['longitude']))) : "0";
    $is_default = (isset($_POST['is_default']) && !empty($_POST['is_default'])) ? trim($db->escapeString($fn->xss_clean($_POST['is_default']))) : "";

    if (!empty($id) && !empty($user_id)) {
        //checking for default address
        if ($is_default == 1) {
            //remove all other addresses from default
            $fn->remove_other_addresses_from_default($user_id);
        }
        if ($fn->is_address_exists($id)) {
            /* If address exists update it */
            $data = array(
                'type' => $type,
                'name' => $name,
                'country_code' => $country_code,
                'mobile' => $mobile,
                'alternate_mobile' => $alternate_mobile,
                'address' => $address,
                'landmark' => $landmark,
                'area_id' => $area_id,
                'city_id' => $city_id,
                'pincode' => $pincode,
                'state' => $state,
                'country' => $country,
                'latitude' => $latitude == "" ? "0" : $latitude,
                'longitude' => $longitude == "" ? "0" : $longitude,
                'is_default' => $is_default
            );

            if ($db->update('user_addresses', $data, 'id=' . $id)) {
                $d_charges = $fn->get_data($columns = ['minimum_free_delivery_order_amount', 'delivery_charges'], 'id=' . $area_id, 'area');
                $response['error'] = false;
                $response['message'] = 'Address updated successfully';
                $response["id"] = "$id";
                $response['user_id'] = $user_id;
                $response['type'] = $type;
                $response['name'] = $name;
                $response['country_code'] = $country_code;
                $response['mobile'] = $mobile;
                $response['alternate_mobile'] = $alternate_mobile;
                $response['address'] = $address;
                $response['landmark'] = $landmark;
                $response['area_id'] = $area_id;
                $response['city_id'] = $city_id;
                $response['pincode'] = $pincode;
                $response['state'] = $state;
                $response['country'] = $country;
                $response['latitude'] = $latitude == "" ? "0" : $latitude;
                $response['longitude'] = $longitude == "" ? "0" : $longitude;
                $response['is_default'] = $is_default == "" ? "0" : $is_default;
                $response['minimum_free_delivery_order_amount'] = (!empty($d_charges[0]['minimum_free_delivery_order_amount'])) ? $d_charges[0]['minimum_free_delivery_order_amount'] : "0";
                $response['delivery_charges'] = (!empty($d_charges[0]['delivery_charges'])) ? $d_charges[0]['delivery_charges'] : "0";
            } else {
                $response['error'] = true;
                $response['message'] = 'Something went wrong please try again!';
            }
        } else {
            $response['error'] = true;
            $response['message'] = 'No such address exists';
        }
    } else {
        $response['error'] = true;
        $response['message'] = 'Please pass all the fields!';
    }

    print_r(json_encode($response));
    return false;
}

/*
3.delete_address
    accesskey:90336
    delete_address:1
    id:3
*/
if ((isset($_POST['delete_address'])) && ($_POST['delete_address'] == 1)) {
    $id  = (isset($_POST['id']) && !empty($_POST['id'])) ? trim($db->escapeString($fn->xss_clean($_POST['id']))) : "";
    if (!empty($id)) {
        if ($fn->is_address_exists($id)) {
            /* If address exists delete it */
            if ($db->delete('user_addresses', 'id=' . $id)) {
                $response['error'] = false;
                $response['message'] = 'Address deleted successfully';
            } else {
                $response['error'] = true;
                $response['message'] = 'Something went wrong please try again!';
            }
        } else {
            $response['error'] = true;
            $response['message'] = 'No such address exists';
        }
    } else {
        $response['error'] = true;
        $response['message'] = 'Please pass all the fields!';
    }

    print_r(json_encode($response));
    return false;
}

/*
4.get_addresses
    accesskey:90336
    get_addresses:1
    user_id:3
    offset:0 {optional}
    limit:5 {optional}
*/
if ((isset($_POST['get_addresses'])) && ($_POST['get_addresses'] == 1)) {
    $user_id  = (isset($_POST['user_id']) && !empty($_POST['user_id'])) ? trim($db->escapeString($fn->xss_clean($_POST['user_id']))) : "";
    if (!empty($user_id)) {
        if ($fn->is_address_exists($id = "", $user_id)) {
            /* if user's addresse(s) exists return data */
            $sql = "SELECT count(id) as total from user_addresses where user_id=" . $user_id;
            $db->sql($sql);
            $total = $db->getResult();
            $sql = "select u.*,c.name as city_name,a.name as area_name,a.minimum_free_delivery_order_amount as minimum_free_delivery_order_amount,a.delivery_charges as delivery_charges from user_addresses u LEFT JOIN city c ON c.id=u.city_id LEFT JOIN area a ON a.id=u.area_id where u.user_id=" . $user_id . " ORDER BY is_default DESC";
            $db->sql($sql);
            $res = $db->getResult();
            if (!empty($res)) {
                $address['error'] = false;
                $address['total'] = $total[0]['total'];
                for ($i = 0; $i < count($res); $i++) {
                    $res[$i]['latitude'] = (!empty($res[$i]['latitude'])) ? $res[$i]['latitude'] : "0";
                    $res[$i]['longitude'] = (!empty($res[$i]['longitude'])) ? $res[$i]['longitude'] : "0";
                    $res[$i]['minimum_free_delivery_order_amount'] = (!empty($res[$i]['minimum_free_delivery_order_amount'])) ? $res[$i]['minimum_free_delivery_order_amount'] : "0";
                    $res[$i]['delivery_charges'] = (!empty($res[$i]['delivery_charges'])) ? $res[$i]['delivery_charges'] : "0";
                }
                $address['data'] = array_values($res);
                print_r(json_encode($address));
                die();
            }
        } else {
            $response['error'] = true;
            $response['message'] = 'User addresse(s) doesn\'t exists';
        }
    } else {
        $response['error'] = true;
        $response['message'] = 'Please pass all the fields!';
    }

    print_r(json_encode($response));
    return false;
}
