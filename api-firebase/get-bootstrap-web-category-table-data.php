<?php
session_start();

// set time for session timeout
$currentTime = time() + 25200;
$expired = 3600;

// if session not set go to login page
if (!isset($_SESSION['user'])) {
    header("location:index.php");
}

// if current time is more than session timeout back to login page
if ($currentTime > $_SESSION['timeout']) {
    session_destroy();
    header("location:index.php");
}

// destroy previous session timeout and create new one
unset($_SESSION['timeout']);
$_SESSION['timeout'] = $currentTime + $expired;

header('Access-Control-Allow-Origin: *');
header("Content-Type: application/json");
header("Expires: 0");
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
header("Cache-Control: no-store, no-cache, must-revalidate");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");


include_once('../includes/custom-functions.php');
$fn = new custom_functions;
include_once('../includes/crud.php');
include_once('../includes/variables.php');
$db = new Database();
$db->connect();
$config = $fn->get_configurations();
$low_stock_limit = $config['low-stock-limit'];

if (isset($config['system_timezone']) && isset($config['system_timezone_gmt'])) {
    date_default_timezone_set($config['system_timezone']);
    $db->sql("SET `time_zone` = '" . $config['system_timezone_gmt'] . "'");
} else {
    date_default_timezone_set('Asia/Kolkata');
    $db->sql("SET `time_zone` = '+05:30'");
}


// data of 'CATEGORY BG IMAGE' table goes here
if (isset($_GET['table']) && $_GET['table'] == 'category') {

    $offset = 0;
    $limit = 10;
    $sort = 'id';
    $order = 'DESC';
    $where = '';
    if (isset($_GET['offset']))
        $offset = $db->escapeString($fn->xss_clean($_GET['offset']));
    if (isset($_GET['limit']))
        $limit = $db->escapeString($fn->xss_clean($_GET['limit']));

    if (isset($_GET['sort']))
        $sort = $db->escapeString($fn->xss_clean($_GET['sort']));
    if (isset($_GET['order']))
        $order = $db->escapeString($fn->xss_clean($_GET['order']));

    if (isset($_GET['search']) && !empty($_GET['search'])) {
        $search = $db->escapeString($fn->xss_clean($_GET['search']));
        $where .= " Where `id` like '%" . $search . "%' OR `name` like '%" . $search . "%' OR `subtitle` like '%" . $search . "%' OR `image` like '%" . $search . "%'";
    }

    $sql = "SELECT COUNT(`id`) as total FROM `category` " . $where;
    $db->sql($sql);
    $res = $db->getResult();
    foreach ($res as $row)
        $total = $row['total'];

    $sql = "SELECT * FROM `category` " . $where . " ORDER BY " . $sort . " " . $order . " LIMIT " . $offset . ", " . $limit;
    $db->sql($sql);
    $res = $db->getResult();

    $bulkData = array();
    $bulkData['total'] = $total;
    $rows = array();
    $tempRow = array();

    foreach ($res as $row) {


        $tempRow['id'] = $row['id'];
        $tempRow['name'] = $row['name'];
        $tempRow['subtitle'] = $row['subtitle'];
        $tempRow['web_image_db'] = $row['web_image'];
        $tempRow['web_image'] = '<a data-lightbox="category"  href="' . $row['web_image'] . '"><img id="c_img" src="' . $row['web_image'] . '" height="50" /></a><br>
                                <p id="no_c_img"></p>';
        $operate = "<a class='btn btn-xs btn-primary edit-web-category' data-id='" . $row['id'] . "'  data-toggle='modal' data-target='#editWebCategoryModal' title='Edit'><i class='fa fa-pencil-square-o'></i></a>";



        $tempRow['operate'] = $operate;
        $rows[] = $tempRow;
    }
    $bulkData['rows'] = $rows;
    print_r(json_encode($bulkData));
}


$db->disconnect();
