<?php
header('Access-Control-Allow-Origin: *');
session_start();
include '../includes/crud.php';
include '../includes/variables.php';
include_once('verify-token.php');
include_once('../includes/custom-functions.php');

$fn = new custom_functions;
$db = new Database();
$db->connect();
// date_default_timezone_set('Asia/Kolkata');

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
/* accesskey:90336 */

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

if ((isset($_POST['add-section'])) && ($_POST['add-section'] == 1)) {
    if (ALLOW_MODIFICATION == 0 && !defined(ALLOW_MODIFICATION)) {
        $response["message"] =  '<label class="alert alert-danger">This operation is not allowed in demo panel!.</label>';
        echo json_encode($response);
        return false;
    }
    $permissions = $fn->get_permissions($_SESSION['id']);
    if ($permissions['featured']['create'] == 0) {
        $response["message"] = "<p class='alert alert-danger'>You have no permission to create featured section.</p>";
        echo json_encode($response);
        return false;
    }

    $title = $db->escapeString($fn->xss_clean($_POST['title']));
    $short_description = $db->escapeString($fn->xss_clean($_POST['short_description']));
    $style = $db->escapeString($fn->xss_clean($_POST['style']));
    $product_ids = $fn->xss_clean_array($_POST['product_ids']);
    $product_ids = implode(',', $product_ids);
    $sql = "INSERT INTO `sections` (`title`,`style`,`short_description`,`product_ids`) VALUES ('$title','$style','$short_description','$product_ids')";
    $db->sql($sql);
    $res = $db->getResult();
    $response["message"] = "<p class = 'alert alert-success'>Section created Successfully</p>";
    $sql = "SELECT id FROM sections ORDER BY id DESC";
    $db->sql($sql);
    $res = $db->getResult();
    $response["id"] = $res[0]['id'];
    echo json_encode($response);
}
if ((isset($_POST['edit-section'])) && ($_POST['edit-section'] == 1)) {
    if (ALLOW_MODIFICATION == 0 && !defined(ALLOW_MODIFICATION)) {
        $response["message"] =  '<label class="alert alert-danger">This operation is not allowed in demo panel!.</label>';
        echo json_encode($response);
        return false;
    }
    $permissions = $fn->get_permissions($_SESSION['id']);
    if ($permissions['featured']['update'] == 0) {
        $response["message"] = "<p class='alert alert-danger'>You have no permission to update featured section.</p>";
        echo json_encode($response);
        return false;
    }

    $id = $db->escapeString($fn->xss_clean($_POST['section-id']));
    $style = $db->escapeString($fn->xss_clean($_POST['style']));
    $short_description = $db->escapeString($fn->xss_clean($_POST['short_description']));
    $title = $db->escapeString($fn->xss_clean($_POST['title']));
    $product_ids = $fn->xss_clean_array($_POST['product_ids']);
    $product_ids = implode(',', $product_ids);

    $sql = "UPDATE `sections` SET `title`='$title', `short_description`='$short_description', `style`='$style', `product_ids` = '$product_ids' WHERE `sections`.`id` = " . $id;
    $db->sql($sql);
    $res = $db->getResult();
    $response["message"] = "<p class='alert alert-success'>Section updated Successfully</p>";
    $response["id"] = $id;
    echo json_encode($response);
}
if (isset($_GET['type']) && $_GET['type'] != '' && $_GET['type'] == 'delete-section') {
    if (ALLOW_MODIFICATION == 0 && !defined(ALLOW_MODIFICATION)) {
        return 2;
    }
    $permissions = $fn->get_permissions($_SESSION['id']);
    if ($permissions['featured']['delete'] == 0) {
        echo 2;
        return false;
    }
    $id = $db->escapeString($fn->xss_clean($_GET['id']));

    $sql = 'DELETE FROM `sections` WHERE `id`=' . $id;
    if ($db->sql($sql)) {
        echo 1;
    } else {
        echo 0;
    }
}

if (isset($_POST['get-all-sections'])) {
    if (!verify_token()) {
        return false;
    }
    $section_id = (isset($_POST['section_id']) && is_numeric($_POST['section_id'])) ? $db->escapeString($fn->xss_clean($_POST['section_id'])) : "";
    $user_id = (isset($_POST['user_id']) && is_numeric($_POST['user_id'])) ? $db->escapeString($fn->xss_clean($_POST['user_id'])) : "";
    $sql = "select * from `sections` ";
    $sql .= (!empty($section_id)) ? " where `id` = $section_id " : "";
    $sql .= " order by `id` desc";
    $db->sql($sql);
    $result = $db->getResult();
    $response = $product_ids = $section = $variations = $temp = array();
    foreach ($result as $row) {
        $product_ids = explode(',', $row['product_ids']);

        $section['id'] = $row['id'];
        $section['title'] = $row['title'];
        $section['short_description'] = $row['short_description'];
        $section['style'] = $row['style'];
        $section['product_ids'] = array_map('trim', $product_ids);
        $product_ids = $section['product_ids'];
        $product_ids = implode(',', $product_ids);

        $sql = 'SELECT * FROM `products` WHERE `status` = 1 AND id IN (' . $product_ids . ')';
        $db->sql($sql);
        $result1 = $db->getResult();
        $product = array();
        $i = 0;
        foreach ($result1 as $row) {
            $sql = "SELECT *,(SELECT short_code FROM unit u WHERE u.id=pv.measurement_unit_id) as measurement_unit_name,(SELECT short_code FROM unit u WHERE u.id=pv.stock_unit_id) as stock_unit_name FROM product_variant pv WHERE pv.product_id=" . $row['id'] . " ORDER BY serve_for ASC";
            $db->sql($sql);
            $variants = $db->getResult();
            $row['other_images'] = json_decode($row['other_images'], 1);
            $row['other_images'] = (empty($row['other_images'])) ? array() : $row['other_images'];
            for ($j = 0; $j < count($row['other_images']); $j++) {
                $row['other_images'][$j] = DOMAIN_URL . $row['other_images'][$j];
            }
            if ($row['tax_id'] == 0) {
                $row['tax_title'] = "";
                $row['tax_percentage'] = "0";
            } else {
                $t_id = $row['tax_id'];
                $sql_tax = "SELECT * from taxes where id= $t_id";
                $db->sql($sql_tax);
                $res_tax = $db->getResult();
                foreach ($res_tax as $tax) {
                    $row['tax_title'] = $tax['title'];
                    $row['tax_percentage'] = $tax['percentage'];
                }
            }
            for ($k = 0; $k < count($variants); $k++) {
                // if ($variants[$k]['stock'] <= 0) {
                if ($variants[$k]['stock'] <= 0 || $variants[$k]['serve_for'] == 'Sold Out') {

                    $variants[$k]['serve_for'] = 'Sold Out';
                } else {
                    $variants[$k]['serve_for'] = 'Available';
                }
                if (!empty($user_id)) {
                    $sql = "SELECT qty as cart_count FROM cart where product_variant_id= " . $variants[$k]['id'] . " AND user_id=" . $user_id;
                    $db->sql($sql);
                    $res = $db->getResult();
                    if (!empty($res)) {
                        foreach ($res as $row1) {
                            $variants[$k]['cart_count'] = $row1['cart_count'];
                        }
                    } else {
                        $variants[$k]['cart_count'] = "0";
                    }
                } else {
                    $variants[$k]['cart_count'] = "0";
                }
            }
            if (!empty($user_id)) {
                $sql = "SELECT id from favorites where product_id = " . $row['id'] . " AND user_id = " . $user_id;
                $db->sql($sql);
                $favorite = $db->getResult();
                if (!empty($favorite)) {
                    $row['is_favorite'] = true;
                } else {
                    $row['is_favorite'] = false;
                }
            } else {
                $row['is_favorite'] = false;
            }

            $row['image'] = DOMAIN_URL . $row['image'];
            $product[$i] = $row;
            $product[$i]['variants'] = $variants;
            $i++;
        }
        $section['products'] = $product;
        $temp[] = $section;
        unset($section['products']);
    }
    if (!empty($result)) {
        $response['error'] = false;
        $response['sections'] = $temp;
    } else {
        $response['error'] = true;
        $response['message'] = "No section has been created yet";
    }
    print_r(json_encode($response));
}


if (isset($_POST['get-notifications'])) {
    if (!verify_token()) {
        return false;
    }

    $offset = 0;
    $limit = 10;
    $sort = 'id';
    $order = 'DESC';
    $where = '';
    if (isset($_POST['offset']))
        $offset = $db->escapeString($fn->xss_clean($_POST['offset']));
    if (isset($_POST['limit']))
        $limit = $db->escapeString($fn->xss_clean($_POST['limit']));

    if (isset($_POST['sort']))
        $sort = $db->escapeString($fn->xss_clean($_POST['sort']));
    if (isset($_POST['order']))
        $order = $db->escapeString($fn->xss_clean($_POST['order']));

    if (isset($_POST['search'])) {
        $search = $db->escapeString($fn->xss_clean($_GET['search']));
        $where = " Where `id` like '%" . $search . "%' OR `title` like '%" . $search . "%' OR `message` like '%" . $search . "%' OR `image` like '%" . $search . "%' OR `date_sent` like '%" . $search . "%' ";
    }

    $sql = "SELECT COUNT(`id`) as total FROM `notifications` " . $where;
    $db->sql($sql);
    $res = $db->getResult();
    foreach ($res as $row)
        $total = $row['total'];

    $sql = "SELECT * FROM `notifications` " . $where . " ORDER BY " . $sort . " " . $order . " LIMIT " . $offset . ", " . $limit;
    $db->sql($sql);
    $res = $db->getResult();
    if (empty($res)) {
        $response['error'] = true;
        $response['message'] = "Data not found!";
        print_r(json_encode($response));
        return false;
    }
    $bulkData = array();
    $bulkData['error'] = false;
    $bulkData['total'] = $total;
    $rows = array();
    $tempRow = array();

    foreach ($res as $row) {
        $tempRow['id'] = $row['id'];
        $tempRow['name'] = $row['title'];
        $tempRow['subtitle'] = $row['message'];
        $tempRow['type'] = $row['type'];
        $tempRow['type_id'] = $row['type_id'];
        $tempRow['image'] = (!empty($row['image'])) ? DOMAIN_URL . $row['image'] : "";
        $rows[] = $tempRow;
    }
    $bulkData['data'] = $rows;
    print_r(json_encode($bulkData));
}
if (isset($_POST['get-delivery-boy-notifications'])) {
    $offset = 0;
    $limit = 10;
    $sort = 'id';
    $order = 'DESC';
    $where = '';
    if (isset($_POST['offset']))
        $offset = $db->escapeString($fn->xss_clean($_POST['offset']));
    if (isset($_POST['limit']))
        $limit = $db->escapeString($fn->xss_clean($_POST['limit']));

    if (isset($_POST['sort']))
        $sort = $db->escapeString($fn->xss_clean($_POST['sort']));
    if (isset($_POST['order']))
        $order = $db->escapeString($fn->xss_clean($_POST['order']));

    if (isset($_POST['search'])) {
        $search = $db->escapeString($fn->xss_clean($_POST['search']));
        $where = " Where `id` like '%" . $search . "%' OR `title` like '%" . $search . "%' OR `message` like '%" . $search . "%' OR `date_created` like '%" . $search . "%' ";
    }
    if (isset($_POST['delivery_boy_id']) && !empty($_POST['delivery_boy_id'])) {
        $delivery_boy_id = $db->escapeString($fn->xss_clean($_POST['delivery_boy_id']));
        $where .= empty($where) ? ' where delivery_boy_id=' . $delivery_boy_id : 'and delivery_boy_id=' . $delivery_boy_id;
    }
    if (isset($_POST['type']) && !empty($_POST['type'])) {
        $type = $db->escapeString($fn->xss_clean($_POST['type']));
        $where .= empty($where) ? " where type='" . $type . "'" : " and type='" . $type . "'";
    }
    $sql = "SELECT COUNT(`id`) as total FROM `delivery_boy_notifications` " . $where;
    $db->sql($sql);
    $res = $db->getResult();
    foreach ($res as $row)
        $total = $row['total'];

    $sql = "SELECT * FROM `delivery_boy_notifications` " . $where . " ORDER BY " . $sort . " " . $order . " LIMIT " . $offset . ", " . $limit;
    $db->sql($sql);
    $res = $db->getResult();
    if (empty($res)) {
        $response['error'] = true;
        $response['message'] = "Data not found!";
        print_r(json_encode($response));
        return false;
    }
    $bulkData = $rows = $tempRow = array();
    $bulkData['error'] = false;
    $bulkData['total'] = $total;

    foreach ($res as $row) {
        $tempRow['id'] = $row['id'];
        $tempRow['delivery_boy_id'] = $row['delivery_boy_id'];
        $tempRow['title'] = $row['title'];
        $tempRow['message'] = $row['message'];
        $tempRow['type'] = $row['type'];
        $tempRow['date_sent'] = $row['date_created'];
        $rows[] = $tempRow;
    }
    $bulkData['data'] = $rows;
    print_r(json_encode($bulkData));
}

function isJSON($string)
{
    return is_string($string) && is_array(json_decode($string, true)) && (json_last_error() == JSON_ERROR_NONE) ? true : false;
}
