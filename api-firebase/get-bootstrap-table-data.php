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

//data of 'ORDERS' table goes here
if (isset($_GET['table']) && $_GET['table'] == 'orders') {
    $offset = 0;
    $limit = 10;
    $sort = 'o.id';
    $order = 'DESC';
    $where = ' ';
    if (!empty($_GET['start_date']) && !empty($_GET['end_date'])) {
        $start_date = $db->escapeString($fn->xss_clean($_GET['start_date']));
        $end_date = $db->escapeString($fn->xss_clean($_GET['end_date']));
        $where .= " where DATE(date_added)>=DATE('" . $start_date . "') AND DATE(date_added)<=DATE('" . $end_date . "')";
    }
    if (isset($_GET['sort']))
        $sort = $db->escapeString($fn->xss_clean($_GET['sort']));
    if (isset($_GET['offset']))
        $offset = $db->escapeString($fn->xss_clean($_GET['offset']));
    if (isset($_GET['limit']))
        $limit = $db->escapeString($fn->xss_clean($_GET['limit']));
    if (isset($_GET['order']))
        $order = $db->escapeString($fn->xss_clean($_GET['order']));
    if (isset($_GET['search']) && !empty($_GET['search'])) {
        $search = $db->escapeString($fn->xss_clean($_GET['search']));
        if (!empty($_GET['start_date']) && !empty($_GET['end_date'])) {
            $where .= " AND (name like '%" . $search . "%' OR o.id like '%" . $search . "%' OR o.mobile like '%" . $search . "%' OR address like '%" . $search . "%' OR `payment_method` like '%" . $search . "%' OR `delivery_charge` like '%" . $search . "%' OR `delivery_time` like '%" . $search . "%' OR o.`status` like '%" . $search . "%' OR `date_added` like '%" . $search . "%')";
        } else {
            $where .= " where (name like '%" . $search . "%' OR o.id like '%" . $search . "%' OR o.mobile like '%" . $search . "%' OR address like '%" . $search . "%' OR `payment_method` like '%" . $search . "%' OR `delivery_charge` like '%" . $search . "%' OR `delivery_time` like '%" . $search . "%' OR o.`status` like '%" . $search . "%' OR `date_added` like '%" . $search . "%')";
        }
    }
    if (isset($_GET['filter_order']) && $_GET['filter_order'] != '') {
        $filter_order = $db->escapeString($fn->xss_clean($_GET['filter_order']));
        if (isset($_GET['search']) && $_GET['search'] != '') {
            $where .= " and `active_status`='" . $filter_order . "'";
        } elseif (isset($_GET['start_date']) && $_GET['start_date'] != '') {
            $where .= " and `active_status`='" . $filter_order . "'";
        } else {
            $where .= " where `active_status`='" . $filter_order . "'";
        }
    }
    $sql = "SELECT COUNT(o.id) as total FROM `orders` o JOIN users u ON u.id=o.user_id" . $where;
    $db->sql($sql);
    $res = $db->getResult();
    foreach ($res as $row) {
        $total = $row['total'];
    }
    $sql = "select o.*,u.name FROM orders o JOIN users u ON u.id=o.user_id" . $where . " ORDER BY " . $sort . " " . $order . " LIMIT " . $offset . ", " . $limit;
    $db->sql($sql);
    $res = $db->getResult();
    for ($i = 0; $i < count($res); $i++) {
        $sql = "select oi.*,p.name as name, u.name as uname,v.measurement, (SELECT short_code FROM unit un where un.id=v.measurement_unit_id)as mesurement_unit_name,(SELECT status FROM orders o where o.id=oi.order_id)as order_status from `order_items` oi 
			    left join product_variant v on oi.product_variant_id=v.id 
			    left join products p on p.id=v.product_id 
			    left join users u ON u.id=oi.user_id
			    where oi.order_id=" . $res[$i]['id'];
        $db->sql($sql);
        $res[$i]['items'] = $db->getResult();
    }
    $bulkData = array();
    $bulkData['total'] = $total;
    $rows = array();
    $tempRow = array();
    foreach ($res as $row) {
        $items = $row['items'];
        $items1 = '';
        $temp = '';
        $total_amt = 0;
        foreach ($items as $item) {
            $temp .= "<b>Item ID :</b>" . $item['id'] . "<b> Product Variant Id :</b> " . $item['product_variant_id'] . "<b> Name : </b>" . $item['name'] . " <b>Unit : </b>" . $item['measurement'] . $item['mesurement_unit_name'] . " <b>Price : </b>" . $item['price'] . " <b>QTY : </b>" . $item['quantity'] . " <b>Subtotal : </b>" . $item['quantity'] * $item['price'] . "<br>------<br>";
            $total_amt += $item['sub_total'];
        }
        $items1 = $temp;
        $temp = '';
        $status = json_decode($row['items'][0]['order_status']);
        if (!empty($status)) {
            foreach ($status as $st) {
                $temp .= $st[0] . " : " . $st[1] . "<br>------<br>";
            }
        }
        if ($row['active_status'] == 'received') {
            $active_status = '<label class="label label-primary">' . $row['active_status'] . '</label>';
        }
        if ($row['active_status'] == 'awaiting_payment') {
            $active_status = '<label class="label label-secondary">Awaiting Payment</label>';
        }
        if ($row['active_status'] == 'processed') {
            $active_status = '<label class="label label-info">' . $row['active_status'] . '</label>';
        }
        if ($row['active_status'] == 'shipped') {
            $active_status = '<label class="label label-warning">' . $row['active_status'] . '</label>';
        }
        if ($row['active_status'] == 'delivered') {
            $active_status = '<label class="label label-success">' . $row['active_status'] . '</label>';
        }
        if ($row['active_status'] == 'returned' || $row['active_status'] == 'cancelled') {
            $active_status = '<label class="label label-danger">' . $row['active_status'] . '</label>';
        }
        $sql = "select name from delivery_boys where id=" . $row['delivery_boy_id'];
        $db->sql($sql);
        $res_dboy = $db->getResult();
        $status = $temp;
        $operate = "<a class='btn btn-sm btn-primary edit-fees' data-id='" . $row['id'] . "' data-toggle='modal' data-target='#editFeesModal'>Edit</a>";

        $operate .= "<a onclick='return conf(\"delete\");' class='btn btn-sm btn-danger' href='../public/db_operations.php?id=" . $row['id'] . "&delete_order=1' target='_blank'>Delete</a>";
        $discounted_amount = $row['total'] * $row['items'][0]['discount'] / 100; /*  */
        $final_total = $row['total'] - $discounted_amount;
        $discount_in_rupees = $row['total'] - $final_total;
        $discount_in_rupees = floor($discount_in_rupees);
        $tempRow['id'] = $row['id'];
        $tempRow['user_id'] = $row['user_id'];
        $tempRow['name'] = $row['items'][0]['uname'];
        if (ALLOW_MODIFICATION == 0 && !defined(ALLOW_MODIFICATION)) {
            $tempRow['mobile'] = str_repeat("*", strlen($row['mobile']) - 3) . substr($row['mobile'], -3);
        } else {
            $tempRow['mobile'] = $row['mobile'];
        }
        $tempRow['order_note'] = $row['order_note'];
        $tempRow['delivery_charge'] = $row['delivery_charge'];
        $tempRow['items'] = $items1;
        $tempRow['total'] = $row['total'];
        $tempRow['tax'] = $row['tax_amount'] . '(' . $row['tax_percentage'] . '%)';
        $tempRow['promo_discount'] = $row['promo_discount'];
        $tempRow['wallet_balance'] = $row['wallet_balance'];
        $tempRow['discount'] = $discount_in_rupees . '(' . $row['items'][0]['discount'] . '%)';
        $tempRow['qty'] = $row['items'][0]['quantity'];
        $tempRow['final_total'] = $row['final_total'];
        $tempRow['promo_code'] = $row['promo_code'];
        $tempRow['deliver_by'] = !empty($res_dboy[0]['name']) ? $res_dboy[0]['name'] : 'Not Assigned';
        $tempRow['payment_method'] = $row['payment_method'];
        $tempRow['address'] = $row['address'];
        $tempRow['delivery_time'] = $row['delivery_time'];
        $tempRow['status'] = $status;
        $tempRow['active_status'] = $active_status;
        $tempRow['wallet_balance'] = $row['wallet_balance'];
        $tempRow['date_added'] = date('d-m-Y', strtotime($row['date_added']));
        $tempRow['operate'] = '<a href="order-detail.php?id=' . $row['id'] . '"><i class="fa fa-eye"></i> View</a>
				<br><a href="delete-order.php?id=' . $row['id'] . '"><i class="fa fa-trash"></i> Delete</a>';
        $rows[] = $tempRow;
    }
    $bulkData['rows'] = $rows;
    print_r(json_encode($bulkData));
}

// data of 'CATEGORY' table goes here
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

        $operate = '<a href="view-subcategory.php?id=' . $row['id'] . '"><i class="fa fa-folder-open-o"></i>View Subcategories</a>';
        $operate .= ' <a href="edit-category.php?id=' . $row['id'] . '"><i class="fa fa-edit"></i>Edit</a>';
        $operate .= ' <a class="btn-xs btn-danger" href="delete-category.php?id=' . $row['id'] . '"><i class="fa fa-trash-o"></i>Delete</a>';

        $tempRow['id'] = $row['id'];
        $tempRow['name'] = $row['name'];
        $tempRow['subtitle'] = $row['subtitle'];
        $tempRow['image'] = "<a data-lightbox='category' href='" . $row['image'] . "' data-caption='" . $row['name'] . "'><img src='" . $row['image'] . "' title='" . $row['name'] . "' height='50' /></a>";
        $tempRow['operate'] = $operate;
        $rows[] = $tempRow;
    }
    $bulkData['rows'] = $rows;
    print_r(json_encode($bulkData));
}

// data of 'SUBCATEGORY' table goes here
if (isset($_GET['table']) && $_GET['table'] == 'subcategory') {

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

    if (isset($_GET['search'])) {
        $search = $db->escapeString($fn->xss_clean($_GET['search']));
        $where = " Where s.`id` like '%" . $search . "%' OR `name` like '%" . $search . "%' OR `subtitle` like '%" . $search . "%' OR `image` like '%" . $search . "%'";
    }

    $sql = "SELECT COUNT(*) as total FROM `subcategory` s" . $where;
    $db->sql($sql);
    $res = $db->getResult();
    foreach ($res as $row)
        $total = $row['total'];

    $sql = "SELECT s.*,(SELECT name FROM category c WHERE c.id=s.category_id) as category_name FROM `subcategory` s" . $where . " ORDER BY " . $sort . " " . $order . " LIMIT " . $offset . ", " . $limit;
    $db->sql($sql);
    $res = $db->getResult();
    $bulkData = array();
    $bulkData['total'] = $total;
    $rows = array();
    $tempRow = array();

    foreach ($res as $row) {

        $operate = '<a href="view-subcategory-product.php?id=' . $row['id'] . '"><i class="fa fa-folder-open-o"></i>View Products</a>';
        $operate .= ' <a href="edit-subcategory.php?id=' . $row['id'] . '"><i class="fa fa-edit"></i>Edit</a>';
        $operate .= ' <a class="btn-xs btn-danger" href="delete-subcategory.php?id=' . $row['id'] . '"><i class="fa fa-trash-o"></i>Delete</a>';
        $tempRow['id'] = $row['id'];
        $tempRow['name'] = $row['name'];
        $tempRow['category_name'] = $row['category_name'];
        $tempRow['subtitle'] = $row['subtitle'];
        $tempRow['image'] = "<a data-lightbox='category' href='" . $row['image'] . "' data-caption='" . $row['name'] . "'><img src='" . $row['image'] . "' title='" . $row['name'] . "' height='50' /></a>";
        $tempRow['operate'] = $operate;
        $rows[] = $tempRow;
    }
    $bulkData['rows'] = $rows;
    print_r(json_encode($bulkData));
}

// data of 'PRODUCTS' table goes here

if (isset($_GET['table']) && $_GET['table'] == 'products') {
    $offset = 0;
    $limit = 10;
    $sort = 'id';
    $order = 'ASC';
    $where = '';

    if (isset($_GET['offset']))
        $offset = $db->escapeString($fn->xss_clean($_GET['offset']));
    if (isset($_GET['limit']))
        $limit = $db->escapeString($fn->xss_clean($_GET['limit']));

    if (isset($_GET['sort']))
        if ($_GET['sort'] == 'id') {
            $sort = "id";
        } else {
            $sort = $db->escapeString($fn->xss_clean($_GET['sort']));
        }
    if (isset($_GET['order']))
        $order = $db->escapeString($fn->xss_clean($_GET['order']));

    if (isset($_GET['search']) and $_GET['search'] != '') {
        $search = $db->escapeString($fn->xss_clean($_GET['search']));
        $where = " where (p.`id` like '%" . $search . "%' OR p.`name` like '%" . $search . "%' OR pv.`measurement` like '%" . $search . "%' OR u.`short_code` like '%" . $search . "%' )";
    }

    if (isset($_GET['category_id']) && $_GET['category_id'] != '') {
        $category_id = $db->escapeString($fn->xss_clean($_GET['category_id']));
        if (isset($_GET['search']) and $_GET['search'] != '')
            $where .= ' and p.`category_id`=' . $category_id;
        else
            $where = ' where p.`category_id`=' . $category_id;
    }
    if (isset($_GET['sold_out']) && $_GET['sold_out'] == 1) {
        $where .= empty($where) ? " WHERE pv.stock <=0 AND pv.serve_for = 'Sold Out'" : " AND stock <=0 AND serve_for = 'Sold Out'";
    }
    if (isset($_GET['low_stock']) && $_GET['low_stock'] == 1) {
        $where .= empty($where) ? " WHERE pv.stock < $low_stock_limit AND pv.serve_for = 'Available'" : " AND stock < $low_stock_limit AND serve_for = 'Available'";
    }


    $join = "LEFT JOIN `product_variant` pv ON pv.product_id = p.id
            LEFT JOIN `unit` u ON u.id = pv.measurement_unit_id";

    $sql = "SELECT COUNT(p.id) as `total` FROM `products` p $join " . $where . "";
    $db->sql($sql);
    $res = $db->getResult();
    foreach ($res as $row)
        $total = $row['total'];
    $sql = "SELECT p.id AS id, p.name,p.status,p.tax_id, p.image, p.indicator, p.manufacturer, p.made_in, p.return_status, p.cancelable_status, p.till_status,p.description, pv.id as product_variant_id, pv.price, pv.discounted_price, pv.measurement, pv.serve_for, pv.stock,pv.stock_unit_id, u.short_code 
            FROM `products` p
            $join 
            $where ORDER BY $sort $order LIMIT $offset, $limit";
    $db->sql($sql);
    $res = $db->getResult();
    $bulkData = array();
    $bulkData['total'] = $total;
    $rows = array();
    $tempRow = array();

    $currency = $fn->get_settings('currency', false);

    foreach ($res as $row) {
        if ($row['indicator'] == 0) {
            $indicator = "<span class='label label-info'>None</span>";
        }
        if ($row['indicator'] == 1) {
            $indicator = "<span class='label label-success'>Veg</span>";
        }
        if ($row['indicator'] == 2) {
            $indicator = "<span class='label label-danger'>Non-Veg</span>";
        }
        if ($row['till_status'] == 'received') {
            $till_status = '<label class="label label-primary">Received</label>';
        }
        if ($row['till_status'] == 'processed') {
            $till_status = '<label class="label label-info">Processed</label>';
        }
        if ($row['till_status'] == 'shipped') {
            $till_status = '<label class="label label-warning">Shipped</label>';
        }
        if ($row['till_status'] == 'delivered') {
            $till_status = '<label class="label label-success">Delivered</label>';
        }

        if (!empty($row['stock_unit_id'])) {
            $sql = "select short_code as stock_unit from unit where id = " . $row['stock_unit_id'];
            $db->sql($sql);
            $stock_unit = $db->getResult();
            $tempRow['stock'] = $row['stock'] . ' ' . $stock_unit[0]['stock_unit'];
        }

        $operate = '<a href="view-product-variants.php?id=' . $row['id'] . '" title="View"><i class="fa fa-folder-open"></i></a>';
        $operate .= ' <a href="edit-product.php?id=' . $row['id'] . '" title="Edit"><i class="fa fa-edit"></i></a>';
        $operate .= ' <a class="btn btn-xs btn-danger" href="delete-product.php?id=' . $row['product_variant_id'] . '" title="Delete"><i class="fa fa-trash-o"></i></a>&nbsp;';
        if ($row['status'] == 1) {
            $operate .= "<a class='btn btn-xs btn-warning set-product-deactive' data-id='" . $row['id'] . "' title='Hide'>  <i class='fa fa-eye'></i> </a>";
        } elseif ($row['status'] == 0) {
            $operate .= "<a class='btn btn-xs btn-success set-product-active' data-id='" . $row['id'] . "' title='Show'>  <i class='fa fa-eye-slash'></i> </a>";
        }

        $tempRow['id'] = $row['product_variant_id'];
        $tempRow['product_id'] = $row['id'];
        $tempRow['tax_id'] = $row['tax_id'];
        $tempRow['name'] = $row['name'];
        $tempRow['measurement'] = $row['measurement'] . " " . $row['short_code'];
        $tempRow['price'] = $currency . " " . $row['price'];
        $tempRow['indicator'] = $indicator;
        $tempRow['manufacturer'] = $row['manufacturer'];
        $tempRow['made_in'] = $row['made_in'];
        $tempRow['description'] = $row['description'];
        $tempRow['return_status'] = $row['return_status'] == 1 ? "<span class='label label-success'>Allowed</span>" : "<span class='label label-danger'>Not Allowed</span>";
        $tempRow['cancelable_status'] = $row['cancelable_status'] == 1 ? "<span class='label label-success'>Allowed</span>" : "<span class='label label-danger'>Not Allowed</span>";
        $tempRow['till_status'] = $row['cancelable_status'] == 1 ? $till_status : "<label class='label label-info'>Not Applicable</label>";
        $tempRow['discounted_price'] = $currency . " " . $row['discounted_price'];
        $tempRow['serve_for'] = $row['serve_for'] == 'Sold Out' ? "<span class='label label-danger'>Sold Out</label>" : "<span class='label label-success'>Available</label>";
        $tempRow['image'] = "<a data-lightbox='product' href='" . $row['image'] . "' data-caption='" . $row['name'] . "'><img src='" . $row['image'] . "' title='" . $row['name'] . "' height='50' /></a>";
        $tempRow['operate'] = $operate;
        $rows[] = $tempRow;
    }
    $bulkData['rows'] = $rows;
    print_r(json_encode($bulkData));
}

// data of 'USERS' table goes here
if (isset($_GET['table']) && $_GET['table'] == 'users') {

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

    if (isset($_GET['filter_user']) && $_GET['filter_user'] != '') {
        $filter_user = $db->escapeString($fn->xss_clean($_GET['filter_user']));
        $where .= ' where u.city=' . $filter_user;
    }
    if (isset($_GET['search']) && !empty($_GET['search'])) {
        $search = $db->escapeString($fn->xss_clean($_GET['search']));
        if (isset($_GET['filter_user']) && $_GET['filter_user'] != '') {
            $where .= " and `id` like '%" . $search . "%' OR `name` like '%" . $search . "%' OR `email` like '%" . $search . "%' OR `mobile` like '%" . $search . "%' ";
        } else {
            $where .= " Where `id` like '%" . $search . "%' OR `name` like '%" . $search . "%' OR `email` like '%" . $search . "%' OR `mobile` like '%" . $search . "%'";
        }
    }
    if (isset($_GET['filter_order_status']) && $_GET['filter_order_status'] != '') {
        $filter_order = $db->escapeString($fn->xss_clean($_GET['filter_order']));
        if (isset($_GET['search']) and $_GET['search'] != '')
            $where .= ' and active_status=' . $filter_order;
        else
            $where = ' where active_status=' . $filter_order;
    }


    $sql = "SELECT COUNT(id) as total FROM `users` u " . $where;
    $db->sql($sql);
    $res = $db->getResult();
    foreach ($res as $row)
        $total = $row['total'];

    $sql = "SELECT *,(SELECT name FROM area a WHERE a.id=u.area) as area_name,(SELECT name FROM city c WHERE c.id=u.city) as city_name FROM `users` u " . $where . " ORDER BY `" . $sort . "` " . $order . " LIMIT " . $offset . ", " . $limit;
    $db->sql($sql);
    $res = $db->getResult();
    $bulkData = array();
    $bulkData['total'] = $total;
    $rows = array();
    $tempRow = array();

    foreach ($res as $row) {
        $tempRow['id'] = $row['id'];
        $tempRow['name'] = $row['name'];
        $path = DOMAIN_URL . 'upload/profile/';
        if (!empty($row['profile'])) {
            $tempRow['profile'] = "<a data-lightbox='product' href='" . $path . $row['profile'] . "' data-caption='" . $row['name'] . "'><img src='" . $path . $row['profile'] . "' title='" . $row['name'] . "' height='50' /></a>";
        } else {
            $tempRow['profile'] = "<a data-lightbox='product' href='" . $path . "default_user_profile.png' data-caption='" . $row['name'] . "'><img src='" . $path . "default_user_profile.png' title='" . $row['name'] . "' height='50' /></a>";
        }
        if (ALLOW_MODIFICATION == 0 && !defined(ALLOW_MODIFICATION)) {
            $tempRow['email'] = str_repeat("*", strlen($row['email']) - 13) . substr($row['email'], -13);
            $tempRow['mobile'] = str_repeat("*", strlen($row['mobile']) - 3) . substr($row['mobile'], -3);
        } else {
            $tempRow['mobile'] = $row['mobile'];
            $tempRow['email'] = $row['email'];
        }
        $tempRow['balance'] = $row['balance'];
        $tempRow['referral_code'] = $row['referral_code'];
        $tempRow['friends_code'] = !empty($row['friends_code']) ? $row['friends_code'] : '-';
        $tempRow['city_id'] = $row['city'];
        $tempRow['city'] = $row['city_name'];
        $tempRow['area_id'] = $row['area'];
        $tempRow['area'] = $row['area_name'];
        $tempRow['street'] = $row['street'];
        $tempRow['apikey'] = $row['apikey'];

        $tempRow['status'] = $row['status'] == 1 ? "<label class='label label-success'>Active</label>" : "<label class='label label-danger'>De-Active</label>";
        $tempRow['created_at'] = $row['created_at'];
        $rows[] = $tempRow;
    }
    $bulkData['rows'] = $rows;
    print_r(json_encode($bulkData));
}

// data of 'USERS' table goes here
if (isset($_GET['table']) && $_GET['table'] == 'area') {
    $where = '';
    $offset = (isset($_GET['offset']) && !empty($_GET['offset']) && is_numeric($_GET['offset'])) ? $db->escapeString($fn->xss_clean($_GET['offset'])) : 0;
    $limit = (isset($_GET['limit']) && !empty($_GET['limit']) && is_numeric($_GET['limit'])) ? $db->escapeString($fn->xss_clean($_GET['limit'])) : 10;
    $sort = (isset($_GET['sort']) && !empty($_GET['sort'])) ? $db->escapeString($fn->xss_clean($_GET['sort'])) : 'id';
    $order = (isset($_GET['order']) && !empty($_GET['order'])) ? $db->escapeString($fn->xss_clean($_GET['order'])) : 'DESC';

    if (isset($_GET['filter_area']) && !empty($_GET['filter_area'])) {
        $filter_area = $db->escapeString($fn->xss_clean($_GET['filter_area']));
        $where .= ' where c.id=' . $filter_area;
    }
    if (isset($_GET['search']) && !empty($_GET['search'])) {
        $search = $db->escapeString($fn->xss_clean($_GET['search']));
        if (isset($_GET['filter_area']) && !empty($_GET['filter_area'])) {
            $where .= " and a.`id` like '%" . $search . "%' OR a.`name` like '%" . $search . "%' OR `city_id` like '%" . $search . "%' OR c.`name` like '%" . $search . "%'";
        } else {
            $where .= " Where a.`id` like '%" . $search . "%' OR a.`name` like '%" . $search . "%' OR `city_id` like '%" . $search . "%' OR c.`name` like '%" . $search . "%'";
        }
    }

    $sql = "SELECT COUNT(a.id) as total FROM `area` a JOIN city c ON a.city_id=c.id " . $where;
    $db->sql($sql);
    $res = $db->getResult();
    foreach ($res as $row)
        $total = $row['total'];

    $sql = "SELECT a.*,c.name as city_name FROM `area` a join city c ON a.city_id=c.id $where ORDER BY $sort $order LIMIT $offset,$limit";
    $db->sql($sql);
    $res = $db->getResult();

    $bulkData = array();
    $bulkData['total'] = $total;
    $rows = array();
    $tempRow = array();

    foreach ($res as $row) {
        $operate = ' <a href="edit-area.php?id=' . $row['id'] . '" title="Edit"><i class="fa fa-edit"></i>Edit</a>&nbsp;';
        $operate .= ' <a class="btn btn-xs btn-danger" href="delete-area.php?id=' . $row['id'] . '" title="Delete"><i class="fa fa-trash-o"></i> Delete</a>';

        $tempRow['id'] = $row['id'];
        $tempRow['name'] = $row['name'];
        $tempRow['delivery_charges'] = $row['delivery_charges'];
        $tempRow['minimum_free_delivery_order_amount'] = $row['minimum_free_delivery_order_amount'];
        $tempRow['city_id'] = $row['city_id'];
        $tempRow['city_name'] = $row['city_name'];
        $tempRow['operate'] = $operate;
        $rows[] = $tempRow;
    }
    $bulkData['rows'] = $rows;
    print_r(json_encode($bulkData));
}

// data of 'notification' table goes here
if (isset($_GET['table']) && $_GET['table'] == 'notifications') {

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

    if (isset($_GET['search'])) {
        $search = $db->escapeString($fn->xss_clean($_GET['search']));
        $where = " Where `id` like '%" . $search . "%' OR `title` like '%" . $search . "%' OR `message` like '%" . $search . "%' OR `image` like '%" . $search . "%' OR `date_sent` like '%" . $search . "%' ";
    }

    $sql = "SELECT COUNT(*) as total FROM `notifications` " . $where;
    $db->sql($sql);
    $res = $db->getResult();
    foreach ($res as $row)
        $total = $row['total'];

    $sql = "SELECT * FROM `notifications` " . $where . " ORDER BY " . $sort . " " . $order . " LIMIT " . $offset . ", " . $limit;
    $db->sql($sql);
    $res = $db->getResult();

    $bulkData = array();
    $bulkData['total'] = $total;
    $rows = array();
    $tempRow = array();

    foreach ($res as $row) {


        $operate = " <a class='btn btn-xs btn-danger delete-notification' data-id='" . $row['id'] . "' data-image='" . $row['image'] . "' title='Delete'><i class='fa fa-trash-o'></i>Delete</a>";

        $tempRow['id'] = $row['id'];
        $tempRow['title'] = $row['title'];
        $tempRow['message'] = $row['message'];
        $tempRow['type'] = $row['type'];
        $tempRow['type_id'] = $row['type_id'];
        $tempRow['image'] = (!empty($row['image'])) ? "<a data-lightbox='slider' href='" . $row['image'] . "' data-caption='" . $row['title'] . "'><img src='" . $row['image'] . "' title='" . $row['title'] . "' width='50' /></a>" : "No Image";
        $tempRow['operate'] = $operate;
        $rows[] = $tempRow;
    }
    $bulkData['rows'] = $rows;
    print_r(json_encode($bulkData));
}

if (isset($_GET['table']) && $_GET['table'] == 'slider') {

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

    if (isset($_GET['search'])) {
        $search = $db->escapeString($fn->xss_clean($_GET['search']));
        $where = " Where `id` like '%" . $search . "%' OR `image` like '%" . $search . "%' OR `date_added` like '%" . $search . "%' ";
    }

    $sql = "SELECT COUNT(*) as total FROM `slider` " . $where;
    $db->sql($sql);
    $res = $db->getResult();
    foreach ($res as $row)
        $total = $row['total'];

    $sql = "SELECT * FROM `slider` " . $where . " ORDER BY " . $sort . " " . $order . " LIMIT " . $offset . ", " . $limit;
    $db->sql($sql);
    $res = $db->getResult();

    $bulkData = array();
    $bulkData['total'] = $total;
    $rows = array();
    $tempRow = array();

    foreach ($res as $row) {
        $operate = " <a class='btn btn-xs btn-danger delete-slider' data-id='" . $row['id'] . "' data-image='" . $row['image'] . "' title='Delete'><i class='fa fa-trash-o'></i>Delete</a>";


        $tempRow['id'] = $row['id'];
        $tempRow['type'] = $row['type'];
        $tempRow['type_id'] = $row['type_id'];
        $tempRow['image'] = (!empty($row['image'])) ? "<a data-lightbox='slider' href='" . $row['image'] . "'><img src='" . $row['image'] . "' width='40'/></a>" : "No Image";
        $tempRow['operate'] = $operate;
        $rows[] = $tempRow;
    }
    $bulkData['rows'] = $rows;
    print_r(json_encode($bulkData));
}
if (isset($_GET['table']) && $_GET['table'] == 'offers') {

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

    if (isset($_GET['search'])) {
        $search = $db->escapeString($fn->xss_clean($_GET['search']));
        $where = " Where `id` like '%" . $search . "%' OR `date_added` like '%" . $search . "%' ";
    }

    $sql = "SELECT COUNT(id) as total FROM `offers` " . $where;
    $db->sql($sql);
    $res = $db->getResult();
    foreach ($res as $row)
        $total = $row['total'];

    $sql = "SELECT * FROM `offers` " . $where . " ORDER BY " . $sort . " " . $order . " LIMIT " . $offset . ", " . $limit;
    $db->sql($sql);
    $res = $db->getResult();

    $bulkData = array();
    $bulkData['total'] = $total;
    $rows = array();
    $tempRow = array();
    foreach ($res as $row) {
        $operate = " <a class='btn btn-xs btn-danger delete-offer' data-id='" . $row['id'] . "' data-image='" . $row['image'] . "' title='Delete'><i class='fa fa-trash-o'></i>Delete</a>";

        $tempRow['id'] = $row['id'];
        $tempRow['image'] = (!empty($row['image'])) ? "<a data-lightbox='offer' href='" . $row['image'] . "'><img src='" . $row['image'] . "' width='40'/></a>" : "No Image";
        $tempRow['date_created'] = date('d-m-Y h:i:sa', strtotime($row['date_added']));
        $tempRow['operate'] = $operate;

        $rows[] = $tempRow;
    }
    $bulkData['rows'] = $rows;
    print_r(json_encode($bulkData));
}

function formatBytes($size, $precision = 2)
{
    $base = log($size, 1024);
    $suffixes = array('', 'KB', 'MB', 'GB', 'TB');

    return round(pow(1024, $base - floor($base)), $precision) . ' ' . $suffixes[floor($base)];
}
if (isset($_GET['table']) && $_GET['table'] == 'media') {
    $where = '';

    $offset = (isset($_GET['offset']) && !empty($_GET['offset']) && is_numeric($_GET['offset'])) ? $db->escapeString($fn->xss_clean($_GET['offset'])) : 0;
    $limit = (isset($_GET['limit']) && !empty($_GET['limit']) && is_numeric($_GET['limit'])) ? $db->escapeString($fn->xss_clean($_GET['limit'])) : 10;
    $sort = (isset($_GET['sort']) && !empty($_GET['sort'])) ? $db->escapeString($fn->xss_clean($_GET['sort'])) : 'id';
    $order = (isset($_GET['order']) && !empty($_GET['order'])) ? $db->escapeString($fn->xss_clean($_GET['order'])) : 'DESC';
    if (isset($_GET['search']) && !empty($_GET['search'])) {
        $search = $db->escapeString($fn->xss_clean($_GET['search']));
        $where = " Where `id` like '%" . $search . "%' OR `extension` like '%" . $search . "%' OR `type` like '%" . $search . "%' OR `name` like '%" . $search . "%' OR `date_created` like '%" . $search . "%' ";
    }

    $sql = "SELECT COUNT(id) as total FROM `media` " . $where;
    $db->sql($sql);
    $res = $db->getResult();
    foreach ($res as $row)
        $total = $row['total'];

    $sql = "SELECT * FROM `media` " . $where . " ORDER BY " . $sort . " " . $order . " LIMIT " . $offset . ", " . $limit;
    $db->sql($sql);
    $res = $db->getResult();

    $bulkData = array();
    $bulkData['total'] = $total;
    $rows = array();
    $tempRow = array();
    foreach ($res as $row) {
        $operate = " <a class='btn btn-xs btn-danger delete_media' data-id='" . $row['id'] . "' data-image='" . $row['sub_directory'] . '/' . $row['name'] . "'title='Delete'><i class='fa fa-trash-o'></i>Delete</a>";
        $operate .= " <a class='btn btn-xs btn-primary copy_to_clipboard' title='Copy'><i class='fa fa-copy'></i>Copy</a> ";

        $tempRow['id'] = $row['id'];
        if (!empty($row['sub_directory'] . '/' . $row['name']) && $row['type'] == 'image') {
            $tempRow['image'] = "<img src='" . $row['sub_directory'] . '/' . $row['name'] . "' width='60' height: 60px; />";
        } else if (!empty($row['sub_directory'] . '/' . $row['name']) && $row['type'] == 'video') {
            $tempRow['image'] = "<img src='./images/video-file.png' width='60' height: 60px; />";
        } else if (!empty($row['sub_directory'] . '/' . $row['name']) && $row['type'] == 'document') {
            $tempRow['image'] = "<img src='./images/doc-file.png' width='60' height: 60px; />";
        } else if (!empty($row['sub_directory'] . '/' . $row['name']) && $row['type'] == 'spreadsheet') {
            $tempRow['image'] = "<img src='./images/xls-file.png' width='60' height: 60px; />";
        } else if (!empty($row['sub_directory'] . '/' . $row['name']) && $row['type'] == 'archive') {
            $tempRow['image'] = "<img src='./images/zip-file.png' width='60' height: 60px; />";
        } else {
            $tempRow['image'] = "<img src='" . $row['sub_directory'] . '/' . $row['name'] . "' width='60' height: 60px; />";

            // $tempRow['image'] = "<img src='./images/audio-file.png' width='60' height: 60px; />";
        }
        $full_path = DOMAIN_URL . $row['sub_directory']  . $row['name'];
        $tempRow['image'] .= "<span class='copy-path hide'>$full_path</span>";
        $tempRow['name'] = $row['name'];
        $tempRow['extension'] = $row['extension'];
        $tempRow['type'] = $row['type'];
        $tempRow['sub_directory'] = $row['sub_directory'];
        $tempRow['size'] = ($row['size'] > 1) ? formatBytes($row['size']) : $row['size'];
        $tempRow['date_created'] = date('d-m-Y h:i:sa', strtotime($row['date_created']));
        $tempRow['operate'] = $operate;

        $rows[] = $tempRow;
    }
    $bulkData['rows'] = $rows;
    print_r(json_encode($bulkData));
}

if (isset($_GET['table']) && $_GET['table'] == 'sections') {

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

    if (isset($_GET['search'])) {
        $search = $db->escapeString($fn->xss_clean($_GET['search']));
        $where = " Where `id` like '%" . $search . "%' OR `title` like '%" . $search . "%' OR `date_added` like '%" . $search . "%' ";
    }

    $sql = "SELECT COUNT(*) as total FROM `sections` " . $where;
    $db->sql($sql);
    $res = $db->getResult();
    foreach ($res as $row)
        $total = $row['total'];

    $sql = "SELECT * FROM `sections` " . $where . " ORDER BY " . $sort . " " . $order . " LIMIT " . $offset . ", " . $limit;
    $db->sql($sql);
    $res = $db->getResult();

    $bulkData = array();
    $bulkData['total'] = $total;
    $rows = array();
    $tempRow = array();

    foreach ($res as $row) {
        $operate = "<a class='btn btn-xs btn-primary edit-section' data-id='" . $row['id'] . "' title='Edit'><i class='fa fa-pencil-square-o'></i></a>";

        $operate .= " <a class='btn btn-xs btn-danger delete-section' data-id='" . $row['id'] . "' title='Delete'><i class='fa fa-trash-o'></i></a>";

        $tempRow['id'] = $row['id'];
        $tempRow['title'] = $row['title'];
        $tempRow['short_description'] = $row['short_description'];
        $tempRow['style'] = $row['style'];
        $tempRow['product_ids'] = $row['product_ids'];
        $tempRow['operate'] = $operate;
        $rows[] = $tempRow;
    }
    $bulkData['rows'] = $rows;
    print_r(json_encode($bulkData));
}


if (isset($_GET['table']) && $_GET['table'] == 'seller_request') {
    $offset = 0;
    $limit = 10;
    $sort = 'id';
    $order = 'DESC';
    $status = $db->escapeString($fn->xss_clean($_GET['status']));
    $where = ' where status=' . $status;
    if (isset($_GET['offset']))
        $offset = $db->escapeString($fn->xss_clean($_GET['offset']));
    if (isset($_GET['limit']))
        $limit = $db->escapeString($fn->xss_clean($_GET['limit']));

    if (isset($_GET['sort']))
        $sort = $db->escapeString($fn->xss_clean($_GET['sort']));
    if (isset($_GET['order']))
        $order = $db->escapeString($fn->xss_clean($_GET['order']));

    if (isset($_GET['search'])) {
        $search = $db->escapeString($fn->xss_clean($_GET['search']));
        $where .= "  and (`id` like '%" . $search . "%' OR `name` like '%" . $search . "%')";
    }

    $sql = "SELECT COUNT(*) as total FROM `seller` " . $where;
    $db->sql($sql);
    $res = $db->getResult();
    foreach ($res as $row)
        $total = $row['total'];

    $sql = "SELECT * FROM `seller` " . $where . " ORDER BY " . $sort . " " . $order . " LIMIT " . $offset . ", " . $limit;
    $db->sql($sql);
    $res = $db->getResult();
    $bulkData = array();
    $bulkData['total'] = $total;
    $rows = array();
    $tempRow = array();

    foreach ($res as $row) {
        $operate = ' <a href="edit-request.php?id=' . $row['id'] . '"><i class="fa fa-edit"></i>Edit</a>';
        $tempRow['id'] = $row['id'];
        $tempRow['name'] = $row['name'];
        $tempRow['mobile'] = $row['mobile'];
        $tempRow['email'] = $row['email'];
        $tempRow['company'] = $row['company_name'];
        $tempRow['address'] = $row['company_address'];
        $tempRow['gst_no'] = $row['gst_no'];
        $tempRow['pan_no'] = $row['pan_no'];
        if ($row['status'] == 0) {
            $tempRow['status'] = "<span class='label label-warning'>Pending</span>";
        } elseif ($row['status'] == 1) {
            $tempRow['status'] =  "<span class='label label-success'>Accepted</span>";
        } else {
            $tempRow['status'] =  "<span class='label label-danger'>Denied</span>";
        }
        $tempRow['date_created'] = $row['date_created'];
        $tempRow['operate'] = $operate;
        $rows[] = $tempRow;
    }
    $bulkData['rows'] = $rows;
    print_r(json_encode($bulkData));
}
// data of 'Delivery Boy' table goes here
if (isset($_GET['table']) && $_GET['table'] == 'delivery-boys') {

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

    if (isset($_GET['search']) && $_GET['search'] != '') {
        $search = $db->escapeString($fn->xss_clean($_GET['search']));
        $where = " Where `id` like '%" . $search . "%' OR `name` like '%" . $search . "%' OR `mobile` like '%" . $search . "%' OR `address` like '%" . $search . "%'";
    }

    $sql = "SELECT COUNT(*) as total FROM `delivery_boys` " . $where;
    $db->sql($sql);
    $res = $db->getResult();
    foreach ($res as $row)
        $total = $row['total'];

    $sql = "SELECT * FROM `delivery_boys` " . $where . " ORDER BY " . $sort . " " . $order . " LIMIT " . $offset . ", " . $limit;

    $db->sql($sql);
    $res = $db->getResult();

    $bulkData = array();
    $bulkData['total'] = $total;
    $rows = array();
    $tempRow = array();
    $path = 'upload/delivery-boy/';
    foreach ($res as $row) {

        $operate = "<a class='btn btn-xs btn-primary edit-delivery-boy' data-id='" . $row['id'] . "' data-driving_license='" . $row['driving_license'] . "' data-national_identity_card='" . $row['national_identity_card'] . "' data-toggle='modal' data-target='#editDeliveryBoyModal' title='Edit'><i class='fa fa-pencil-square-o'></i></a>";

        $operate .= " <a class='btn btn-xs btn-danger delete-delivery-boy' data-id='" . $row['id'] . "' data-driving_license='" . $row['driving_license'] . "' data-national_identity_card='" . $row['national_identity_card'] . "' title='Delete'><i class='fa fa-trash-o'></i></a>";

        $operate .= " <a class='btn btn-xs btn-primary transfer-fund' data-id='" . $row['id'] . "' data-name='" . $row['name'] . "' data-mobile='" . $row['mobile'] . "' data-address='" . $row['address'] . "' data-balance='" . $row['balance'] . "' data-toggle='modal' data-target='#fundTransferModal' title='Fund Transfer'><i class='fa fa-chevron-circle-right'></i></a>";

        $tempRow['id'] = $row['id'];
        $tempRow['name'] = $row['name'];
        if (ALLOW_MODIFICATION == 0 && !defined(ALLOW_MODIFICATION)) {
            $tempRow['mobile'] = str_repeat("*", strlen($row['mobile']) - 3) . substr($row['mobile'], -3);
        } else {
            $tempRow['mobile'] = $row['mobile'];
        }
        $tempRow['address'] = $row['address'];
        $tempRow['bonus'] = $row['bonus'];
        $tempRow['balance'] = ceil($row['balance']);
        if (!empty($row['driving_license'])) {
            $tempRow['driving_license'] = "<a data-lightbox='product' href='" . DOMAIN_URL . $path . $row['driving_license'] . "'><img src='" . DOMAIN_URL . $path . $row['driving_license'] . "' height='50' /></a>";
            $tempRow['national_identity_card'] = "<a data-lightbox='product' href='" . $path . $row['national_identity_card'] . "'><img src='" . $path . $row['national_identity_card'] . "' height='50' /></a>";
        } else {
            $tempRow['national_identity_card'] = "<p>No National Identity Card</p>";
            $tempRow['driving_license'] = "<p>No Driving License</p>";
        }
        $tempRow['dob'] = $row['dob'];
        $tempRow['bank_account_number'] = $row['bank_account_number'];
        $tempRow['bank_name'] = $row['bank_name'];
        $tempRow['account_name'] = $row['account_name'];
        $tempRow['other_payment_information'] = (!empty($row['other_payment_information'])) ? $row['other_payment_information'] : "";
        $tempRow['ifsc_code'] = $row['ifsc_code'];
        if ($row['status'] == 0)
            $tempRow['status'] = "<label class='label label-danger'>Deactive</label>";
        else
            $tempRow['status'] = "<label class='label label-success'>Active</label>";
        $tempRow['operate'] = $operate;
        $rows[] = $tempRow;
    }
    $bulkData['rows'] = $rows;
    print_r(json_encode($bulkData));
}

// data of 'SOCIAL MEDIA' table goes here
if (isset($_GET['table']) && $_GET['table'] == 'social_media') {

    $offset = 0;
    $limit = 10;
    $sort = 'id';
    $order = 'ASC';
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
        $where .= " Where `id` like '%" . $search . "%' OR `icon` like '%" . $search . "%' OR `link` like '%" . $search . "%' ";
    }

    $sql = "SELECT COUNT(`id`) as total FROM `social_media` " . $where;
    $db->sql($sql);
    $res = $db->getResult();
    foreach ($res as $row)
        $total = $row['total'];

    $sql = "SELECT * FROM `social_media` " . $where . " ORDER BY " . $sort . " " . $order . " LIMIT " . $offset . ", " . $limit;
    $db->sql($sql);
    $res = $db->getResult();

    $bulkData = array();
    $bulkData['total'] = $total;
    $rows = array();
    $tempRow = array();

    foreach ($res as $row) {


        $operate = "<a class='btn btn-xs btn-primary edit-social-media' data-id='" . $row['id'] . "'  data-toggle='modal' data-target='#editSocialMediaModal' title='Edit'><i class='fa fa-pencil-square-o'></i></a>";

        $operate .= " <a class='btn btn-xs btn-danger delete-social-media' data-id='" . $row['id'] . "'   title='Delete'><i class='fa fa-trash-o'></i></a>";

        $tempRow['id'] = $row['id'];
        //$tempRow['name'] = $row['name'];

        $tempRow['id'] = $row['id'];
        $icon = "<i class='fa " . $row['icon'] . "'></i>";
        $tempRow['social_icon'] = $icon;
        $tempRow['icon'] = $row['icon'];
        $tempRow['link'] = $row['link'];

        $tempRow['operate'] = $operate;
        $rows[] = $tempRow;
    }
    $bulkData['rows'] = $rows;
    print_r(json_encode($bulkData));
}
// data of 'Payment Request' table goes here
if (isset($_GET['table']) && $_GET['table'] == 'payment-requests') {

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

    if (isset($_GET['search']) && $_GET['search'] != '') {
        $search = $db->escapeString($fn->xss_clean($_GET['search']));
        $where = " Where p.`id` like '%" . $search . "%' OR `user_id` like '%" . $search . "%' OR `payment_type` like '%" . $search . "%' OR `amount_requested` like '%" . $search . "%' OR `remarks` like '%" . $search . "%' OR `name` like '%" . $search . "%' OR `email` like '%" . $search . "%' OR `date_created` like '%" . $search . "%' OR `payment_address` like '%" . $search . "%'";
    }

    $sql = "SELECT COUNT(*) as total FROM `payment_requests` p JOIN users u ON p.user_id=u.id" . $where;
    $db->sql($sql);
    $res = $db->getResult();
    foreach ($res as $row)
        $total = $row['total'];

    $sql = "SELECT p.*,u.name,u.email FROM payment_requests p JOIN users u ON u.id=p.user_id" . $where . " ORDER BY " . $sort . " " . $order . " LIMIT " . $offset . ", " . $limit;

    $db->sql($sql);
    $res = $db->getResult();

    $bulkData = array();
    $bulkData['total'] = $total;
    $rows = array();
    $tempRow = array();

    foreach ($res as $row) {
        $operate = "<a class='btn btn-xs btn-primary edit-payment-request' data-id='" . $row['id'] . "' data-toggle='modal' data-target='#editPaymentRequestModal' title='Edit'><i class='fa fa-pencil-square-o'></i></a>";

        $tempRow['id'] = $row['id'];
        $tempRow['user_id'] = $row['user_id'];
        $tempRow['payment_type'] = $row['payment_type'];
        if ($row['payment_type'] == 'bank') {
            $payment_address = json_decode($row['payment_address'], true);
            $tempRow['payment_address'] = '<b>A/C Holder</b><br>' . $payment_address[0][1] . '<br>' . '<b>A/C Number</b><br>' . $payment_address[1][1] . '<br>' . '<b>IFSC Code</b><br>' . $payment_address[2][1] . '<br>' . '<b>Bank Name</b><br>' . $payment_address[3][1];
        } else {
            $tempRow['payment_address'] = $row['payment_address'];
        }
        $tempRow['amount_requested'] = $row['amount_requested'];
        $tempRow['remarks'] = $row['remarks'];
        $tempRow['name'] = $row['name'];
        $tempRow['email'] = $row['email'];
        if ($row['status'] == 0)
            $tempRow['status'] = "<label class='label label-warning'>Pending</label>";
        if ($row['status'] == 1)
            $tempRow['status'] = "<label class='label label-primary'>Success</label>";
        if ($row['status'] == 2)
            $tempRow['status'] = "<label class='label label-danger'>Cancelled</label>";
        $tempRow['operate'] = $operate;
        $tempRow['date_created'] = $row['date_created'];
        $rows[] = $tempRow;
    }
    $bulkData['rows'] = $rows;
    print_r(json_encode($bulkData));
}

// data of 'Fund Transfer' table goes here
if (isset($_GET['table']) && $_GET['table'] == 'fund-transfers') {

    $offset = 0;
    $limit = 10;
    $sort = 'f.id';
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

    if (isset($_GET['search']) && $_GET['search'] != '') {
        $search = $db->escapeString($fn->xss_clean($_GET['search']));
        $where = " Where f.`id` like '%" . $search . "%' OR `name` like '%" . $search . "%' OR `mobile` like '%" . $search . "%' OR `address` like '%" . $search . "%' OR `message` like '%" . $search . "%' OR f.`date_created` like '%" . $search . "%'";
    }

    $sql = "SELECT COUNT(f.`id`) as total FROM `fund_transfers` f LEFT JOIN `delivery_boys` d ON f.delivery_boy_id=d.id" . $where;
    $db->sql($sql);
    $res = $db->getResult();
    foreach ($res as $row)
        $total = $row['total'];

    $sql = "SELECT f.*,d.name,d.mobile,d.address FROM `fund_transfers` f LEFT JOIN `delivery_boys` d ON f.delivery_boy_id=d.id " . $where . " ORDER BY " . $sort . " " . $order . " LIMIT " . $offset . ", " . $limit;

    $db->sql($sql);
    $res = $db->getResult();

    $bulkData = array();
    $bulkData['total'] = $total;
    $rows = array();
    $tempRow = array();

    foreach ($res as $row) {
        $tempRow['id'] = $row['id'];
        $tempRow['name'] = $row['name'];
        if (ALLOW_MODIFICATION == 0 && !defined(ALLOW_MODIFICATION)) {
            $tempRow['mobile'] = str_repeat("*", strlen($row['mobile']) - 3) . substr($row['mobile'], -3);
        } else {
            $tempRow['mobile'] = $row['mobile'];
        }
        $tempRow['address'] = $row['address'];
        $tempRow['delivery_boy_id'] = $row['delivery_boy_id'];
        $tempRow['opening_balance'] = $row['opening_balance'];
        $tempRow['closing_balance'] = $row['closing_balance'];
        $tempRow['amount'] = $row['amount'];
        $tempRow['type'] = $row['type'] == 'credit' ? '<span class="label label-success">Credit</span>' : '<span class="label label-danger">Debit</span>';
        $tempRow['status'] = $row['status'] == 'SUCCESS' ? '<span class="label label-success">Success</span>' : '<span class="label label-danger">Failed</span>';
        $tempRow['message'] = $row['message'];
        $tempRow['date_created'] = $row['date_created'];


        $rows[] = $tempRow;
    }
    $bulkData['rows'] = $rows;
    print_r(json_encode($bulkData));
}

// data of 'Fund Transfer' table goes here
if (isset($_GET['table']) && $_GET['table'] == 'unit') {

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

    if (isset($_GET['search']) && $_GET['search'] != '') {
        $search = $db->escapeString($fn->xss_clean($_GET['search']));
        $where = " Where `id` like '%" . $search . "%' OR `name` like '%" . $search . "%' OR `short_code` like '%" . $search . "%' OR `conversion` like '%" . $search . "%' ";
    }

    $sql = "SELECT COUNT(`id`) as total FROM `unit` $where";
    $db->sql($sql);
    $res = $db->getResult();
    foreach ($res as $row)
        $total = $row['total'];

    $sql = "SELECT * FROM `unit` $where ORDER BY $sort $order LIMIT $offset,$limit";
    $db->sql($sql);
    $res = $db->getResult();

    $bulkData = array();
    $bulkData['total'] = $total;
    $rows = array();
    $tempRow = array();

    foreach ($res as $row) {
        $tempRow['id'] = $row['id'];
        $tempRow['name'] = $row['name'];
        $tempRow['short_code'] = $row['short_code'];
        $tempRow['parent_id'] = $row['parent_id'];
        $tempRow['conversion'] = $row['conversion'];
        $rows[] = $tempRow;
    }
    $bulkData['rows'] = $rows;
    print_r(json_encode($bulkData));
}

// data of 'Promo Codes' table goes here
if (isset($_GET['table']) && $_GET['table'] == 'promo-codes') {

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

    if (isset($_GET['search']) && $_GET['search'] != '') {
        $search = $db->escapeString($fn->xss_clean($_GET['search']));
        $where = " Where `id` like '%" . $search . "%' OR `promo_code` like '%" . $search . "%' OR `message` like '%" . $search . "%' OR `start_date` like '%" . $search . "%' OR `end_date` like '%" . $search . "%'";
    }

    $sql = "SELECT COUNT(id) as total FROM `promo_codes`" . $where;
    $db->sql($sql);
    $res = $db->getResult();
    foreach ($res as $row)
        $total = $row['total'];

    $sql = "SELECT * FROM `promo_codes`" . $where . " ORDER BY " . $sort . " " . $order . " LIMIT " . $offset . ", " . $limit;
    $db->sql($sql);
    $res = $db->getResult();

    $bulkData = array();
    $bulkData['total'] = $total;
    $rows = array();
    $tempRow = array();

    foreach ($res as $row) {

        $operate = "<a class='btn btn-xs btn-primary edit-promo-code' data-id='" . $row['id'] . "' data-toggle='modal' data-target='#editPromoCodeModal' title='Edit'><i class='fa fa-pencil-square-o'></i></a>";
        $operate .= " <a class='btn btn-xs btn-danger delete-promo-code' data-id='" . $row['id'] . "' title='Delete'><i class='fa fa-trash-o'></i></a>";


        $tempRow['id'] = $row['id'];
        $tempRow['promo_code'] = $row['promo_code'];
        $tempRow['message'] = $row['message'];
        $tempRow['start_date'] = $row['start_date'];
        $tempRow['end_date'] = $row['end_date'];
        $tempRow['no_of_users'] = $row['no_of_users'];
        $tempRow['minimum_order_amount'] = $row['minimum_order_amount'];
        $tempRow['discount'] = $row['discount'];
        $tempRow['discount_type'] = $row['discount_type'];
        $tempRow['max_discount_amount'] = $row['max_discount_amount'];
        $tempRow['repeat_usage'] = $row['repeat_usage'] == 1 ? 'Allowed' : 'Not Allowed';
        $tempRow['no_of_repeat_usage'] = $row['no_of_repeat_usage'];
        if ($row['status'] == 0)
            $tempRow['status'] = "<label class='label label-danger'>Deactive</label>";
        else
            $tempRow['status'] = "<label class='label label-success'>Active</label>";
        $tempRow['date_created'] = date('d-m-Y h:i:sa', strtotime($row['date_created']));
        $tempRow['operate'] = $operate;

        $rows[] = $tempRow;
    }
    $bulkData['rows'] = $rows;
    print_r(json_encode($bulkData));
}
if (isset($_GET['table']) && $_GET['table'] == 'time-slots') {

    $offset = 0;
    $limit = 10;
    $sort = 'last_order_time';
    $order = 'ASC';
    $where = '';
    if (isset($_GET['offset']))
        $offset = $db->escapeString($fn->xss_clean($_GET['offset']));
    if (isset($_GET['limit']))
        $limit = $db->escapeString($fn->xss_clean($_GET['limit']));

    if (isset($_GET['sort']))
        $sort = $db->escapeString($fn->xss_clean($_GET['sort']));
    if (isset($_GET['order']))
        $order = $db->escapeString($fn->xss_clean($_GET['order']));

    if (isset($_GET['search']) && $_GET['search'] != '') {
        $search = $db->escapeString($fn->xss_clean($_GET['search']));
        $where = " Where `id` like '%" . $search . "%' OR `title` like '%" . $search . "%' OR `from_time` like '%" . $search . "%' OR `to_time` like '%" . $search . "%' OR `last_order_time` like '%" . $search . "%'";
    }

    $sql = "SELECT COUNT(*) as total FROM `time_slots` " . $where;
    $db->sql($sql);
    $res = $db->getResult();
    foreach ($res as $row)
        $total = $row['total'];

    $sql = "SELECT * FROM `time_slots` " . $where . " ORDER BY " . $sort . " " . $order . " LIMIT " . $offset . ", " . $limit;
    $db->sql($sql);
    $res = $db->getResult();

    $bulkData = array();
    $bulkData['total'] = $total;
    $rows = array();
    $tempRow = array();

    foreach ($res as $row) {

        $operate = "<a class='btn btn-xs btn-primary edit-time-slot' data-id='" . $row['id'] . "' data-toggle='modal' data-target='#editTimeSlotModal' title='Edit'><i class='fa fa-pencil-square-o'></i></a>";
        $operate .= " <a class='btn btn-xs btn-danger delete-time-slot' data-id='" . $row['id'] . "' title='Delete'><i class='fa fa-trash-o'></i></a>";
        $tempRow['id'] = $row['id'];
        $tempRow['title'] = $row['title'];
        $tempRow['from_time'] = $row['from_time'];
        $tempRow['to_time'] = $row['to_time'];
        $tempRow['last_order_time'] = $row['last_order_time'];
        if ($row['status'] == 0)
            $tempRow['status'] = "<label class='label label-danger'>Deactive</label>";
        else
            $tempRow['status'] = "<label class='label label-success'>Active</label>";
        $tempRow['operate'] = $operate;
        $rows[] = $tempRow;
    }
    $bulkData['rows'] = $rows;
    print_r(json_encode($bulkData));
}
// data of 'Return Request' table goes here
if (isset($_GET['table']) && $_GET['table'] == 'return-requests') {

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

    if (isset($_GET['search']) && $_GET['search'] != '') {
        $search = $db->escapeString($fn->xss_clean($_GET['search']));
        $where = " Where r.`id` like '%" . $search . "%' OR r.`user_id` like '%" . $search . "%' OR r.`order_id` like '%" . $search . "%' OR p.`name` like '%" . $search . "%' OR u.`name` like '%" . $search . "%' OR r.`status` like '%" . $search . "%' OR r.`date_created` like '%" . $search . "%'";
    }

    $sql = "SELECT COUNT(*) as total FROM `return_requests` r LEFT JOIN users u ON r.user_id=u.id" . $where;
    $db->sql($sql);
    $res = $db->getResult();
    foreach ($res as $row)
        $total = $row['total'];

    $sql = "SELECT r.*,u.name,oi.product_variant_id,oi.quantity,p.id as product_id,p.name as product_name,pv.price,pv.discounted_price FROM return_requests r LEFT JOIN users u ON u.id=r.user_id LEFT JOIN order_items oi ON oi.id=r.order_item_id LEFT JOIN products p ON p.id = r.product_id LEFT JOIN product_variant pv ON pv.id=r.product_variant_id" . $where . " ORDER BY " . $sort . " " . $order . " LIMIT " . $offset . ", " . $limit;
    $db->sql($sql);
    $res = $db->getResult();

    $bulkData = array();
    $bulkData['total'] = $total;
    $rows = array();
    $tempRow = array();

    foreach ($res as $row) {
        $operate = "<a class='btn btn-xs btn-primary edit-return-request' data-id='" . $row['id'] . "' data-toggle='modal' data-target='#editReturnRequestModal' title='Edit'><i class='fa fa-pencil-square-o'></i></a>";
        $operate .= " <a class='btn btn-xs btn-danger delete-return-request' data-id='" . $row['id'] . "' title='Delete'><i class='fa fa-trash'></i></a>";

        $tempRow['id'] = $row['id'];
        $tempRow['user_id'] = $row['user_id'];
        $tempRow['order_id'] = $row['order_id'];
        $tempRow['order_item_id'] = $row['order_item_id'];
        $tempRow['product_id'] = $row['product_id'];
        $tempRow['price'] = $row['price'];
        $tempRow['discounted_price'] = $row['discounted_price'];
        $tempRow['remarks'] = $row['remarks'];
        $tempRow['name'] = $row['name'];
        $tempRow['product_name'] = $row['product_name'];
        $tempRow['product_variant_id'] = $row['product_variant_id'];
        $tempRow['quantity'] = $row['quantity'];
        $tempRow['total'] = $row['discounted_price'] == 0 ? $row['price'] * $row['quantity'] : $row['discounted_price'] * $row['quantity'];
        if ($row['status'] == 0)
            $tempRow['status'] = "<label class='label label-warning'>Pending</label>";
        if ($row['status'] == 1)
            $tempRow['status'] = "<label class='label label-primary'>Approved</label>";
        if ($row['status'] == 2)
            $tempRow['status'] = "<label class='label label-danger'>Cancelled</label>";
        $tempRow['operate'] = $operate;
        $tempRow['date_created'] = $row['date_created'];
        $rows[] = $tempRow;
    }
    $bulkData['rows'] = $rows;
    print_r(json_encode($bulkData));
}
// data of 'Promo Codes' table goes here
if (isset($_GET['table']) && $_GET['table'] == 'system-users') {

    $offset = 0;
    $limit = 10;
    $sort = 'id';
    $order = 'ASC';
    $where = '';
    $condition = '';
    if (isset($_GET['offset']))
        $offset = $db->escapeString($fn->xss_clean($_GET['offset']));
    if (isset($_GET['limit']))
        $limit = $db->escapeString($fn->xss_clean($_GET['limit']));

    if (isset($_GET['sort']))
        $sort = $db->escapeString($fn->xss_clean($_GET['sort']));
    if (isset($_GET['order']))
        $order = $db->escapeString($fn->xss_clean($_GET['order']));

    if (isset($_GET['search']) && $_GET['search'] != '') {
        $search = $db->escapeString($fn->xss_clean($_GET['search']));
        $where = " Where `id` like '%" . $search . "%' OR `username` like '%" . $search . "%' OR `email` like '%" . $search . "%' OR `role` like '%" . $search . "%' OR `date_created` like '%" . $search . "%'";
    }
    if ($_SESSION['role'] != 'super admin') {
        if (empty($where)) {
            $condition .= ' where created_by=' . $_SESSION['id'];
        } else {
            $condition .= ' and created_by=' . $_SESSION['id'];
        }
    }

    $sql = "SELECT COUNT(id) as total FROM `admin`" . $where . "" . $condition;
    $db->sql($sql);
    $res = $db->getResult();
    foreach ($res as $row)
        $total = $row['total'];

    $sql = "SELECT * FROM `admin`" . $where . "" . $condition . " ORDER BY " . $sort . " " . $order . " LIMIT " . $offset . ", " . $limit;
    $db->sql($sql);
    $res = $db->getResult();

    $bulkData = array();
    $bulkData['total'] = $total;
    $rows = array();
    $tempRow = array();

    foreach ($res as $row) {
        if ($row['created_by'] != 0) {
            $sql = "SELECT username FROM admin WHERE id=" . $row['created_by'];
            $db->sql($sql);
            $created_by = $db->getResult();
        }

        if ($row['role'] != 'super admin') {
            $operate = "<a class='btn btn-xs btn-primary edit-system-user' data-id='" . $row['id'] . "' data-toggle='modal' data-target='#editSystemUserModal' title='Edit'><i class='fa fa-pencil-square-o'></i></a>";
            $operate .= " <a class='btn btn-xs btn-danger delete-system-user' data-id='" . $row['id'] . "' title='Delete'><i class='fa fa-trash-o'></i></a>";
        } else {
            $operate = '';
        }
        if ($row['role'] == 'super admin') {
            $role = '<span class="label label-success">Super Admin</span>';
        }
        if ($row['role'] == 'admin') {
            $role = '<span class="label label-primary">Admin</span>';
        }
        if ($row['role'] == 'editor') {
            $role = '<span class="label label-warning">Editor</span>';
        }
        $tempRow['id'] = $row['id'];
        $tempRow['username'] = $row['username'];
        $tempRow['email'] = $row['email'];
        $tempRow['permissions'] = $row['permissions'];
        $tempRow['role'] = $role;
        $tempRow['created_by_id'] = $row['created_by'] != 0 ? $row['created_by'] : '-';
        $tempRow['created_by'] = $row['created_by'] != 0 ? $created_by[0]['username'] : '-';
        $tempRow['date_created'] = date('d-m-Y h:i:sa', strtotime($row['date_created']));
        $tempRow['operate'] = $operate;

        $rows[] = $tempRow;
    }
    $bulkData['rows'] = $rows;
    print_r(json_encode($bulkData));
}

// data of 'Wallet Transactions' table goes here
if (isset($_GET['table']) && $_GET['table'] == 'wallet-transactions') {

    $offset = 0;
    $limit = 10;
    $sort = 'w.id';
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

    if (isset($_GET['search']) && $_GET['search'] != '') {
        $search = $db->escapeString($fn->xss_clean($_GET['search']));
        $where = " Where w.`id` like '%" . $search . "%' OR `user_id` like '%" . $search . "%' OR `message` like '%" . $search . "%' OR `name` like '%" . $search . "%' OR `date_created` like '%" . $search . "%'";
    }

    $sql = "SELECT COUNT(*) as total FROM `wallet_transactions` w JOIN `users` u ON u.id=w.user_id " . $where;
    $db->sql($sql);
    $res = $db->getResult();
    foreach ($res as $row)
        $total = $row['total'];

    $sql = "SELECT w.*,u.name FROM `wallet_transactions` w JOIN `users` u ON u.id=w.user_id " . $where . " ORDER BY " . $sort . " " . $order . " LIMIT " . $offset . ", " . $limit;
    $db->sql($sql);
    $res = $db->getResult();

    $bulkData = array();
    $bulkData['total'] = $total;
    $rows = array();
    $tempRow = array();

    foreach ($res as $row) {
        $tempRow['id'] = $row['id'];
        $tempRow['user_id'] = $row['user_id'];
        $tempRow['name'] = $row['name'];
        $tempRow['type'] = $row['type'];
        $tempRow['amount'] = $row['amount'];
        $tempRow['message'] = $row['message'];
        $tempRow['date_created'] = $row['date_created'];
        $tempRow['las_updated'] = $row['last_updated'];
        if ($row['status'] == 0)
            $tempRow['status'] = "<label class='label label-danger'>Deactive</label>";
        else
            $tempRow['status'] = "<label class='label label-success'>Active</label>";
        $rows[] = $tempRow;
    }
    $bulkData['rows'] = $rows;
    print_r(json_encode($bulkData));
}

// data of 'Withdrawal Request' table goes here
if (isset($_GET['table']) && $_GET['table'] == 'withdrawal-requests') {

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

    if (isset($_GET['type']) && $_GET['type'] != '') {
        $type = $db->escapeString($fn->xss_clean($_GET['type']));
        $where .= empty($where) ? " WHERE type = '" . $type . "'" : " and type = '" . $type . "'";
    }

    if (isset($_GET['type']) && $_GET['type'] != '' && $_GET['type'] == 'user') {
        $sql = "SELECT COUNT(w.id) as total FROM `withdrawal_requests` w LEFT JOIN users u ON w.type_id=u.id" . $where;
    } elseif (isset($_GET['type']) && $_GET['type'] != '' && $_GET['type'] == 'delivery_boy') {
        $sql = "SELECT COUNT(w.id) as total FROM `withdrawal_requests` w LEFT JOIN delivery_boys d ON w.type_id=d.id" . $where;
    } else {
        $sql = "SELECT COUNT(id) as total FROM `withdrawal_requests`" . $where;
    }
    $db->sql($sql);
    $res = $db->getResult();
    foreach ($res as $row)
        $total = $row['total'];
    if (isset($_GET['type']) && $_GET['type'] != '' && $_GET['type'] == 'user') {
        $sql = "SELECT * FROM withdrawal_requests" . $where . " ORDER BY " . $sort . " " . $order . " LIMIT " . $offset . ", " . $limit;
    } elseif (isset($_GET['type']) && $_GET['type'] != '' && $_GET['type'] == 'delivery_boy') {
        $sql = "SELECT * FROM withdrawal_requests" . $where . " ORDER BY " . $sort . " " . $order . " LIMIT " . $offset . ", " . $limit;
    } else {
        $sql = "SELECT * FROM `withdrawal_requests`" . $where . " ORDER BY " . $sort . " " . $order . " LIMIT " . $offset . ", " . $limit;
    }
    $db->sql($sql);
    $res = $db->getResult();

    $bulkData = array();
    $bulkData['total'] = $total;
    $rows = array();
    $tempRow = array();

    foreach ($res as $row) {

        if ($row['type'] == 'user') {
            $sql = "select name,balance from users where id=" . $row['type_id'];
        } else {
            $sql = "select name,balance from delivery_boys where id=" . $row['type_id'];
        }
        $db->sql($sql);
        $res1 = $db->getResult();
        $operate = "<a class='btn btn-xs btn-primary edit-withdrawal-request' data-id='" . $row['id'] . "' data-toggle='modal' data-target='#editWithdrawalRequestModal' title='Edit'><i class='fa fa-pencil-square-o'></i></a>";
        $operate .= " <a class='btn btn-xs btn-danger delete-withdrawal-request' data-id='" . $row['id'] . "' title='Delete'><i class='fa fa-trash'></i></a>";

        $tempRow['id'] = $row['id'];
        $tempRow['type'] = $row['type'] == 'delivery_boy' ? 'Delivery Boy' : 'User';
        $tempRow['type_id'] = $row['type_id'];
        $tempRow['amount'] = $row['amount'];
        $tempRow['balance'] = $res1[0]['balance'];
        $tempRow['message'] = empty($row['message']) ? '-' : $row['message'];
        $tempRow['name'] = !empty($res1[0]['name']) ? $res1[0]['name'] : "";

        if ($row['status'] == 0)
            $tempRow['status'] = "<label class='label label-warning'>Pending</label>";
        if ($row['status'] == 1)
            $tempRow['status'] = "<label class='label label-primary'>Approved</label>";
        if ($row['status'] == 2)
            $tempRow['status'] = "<label class='label label-danger'>Cancelled</label>";
        $tempRow['operate'] = $operate;
        $tempRow['date_created'] = $row['date_created'];
        $tempRow['last_updated'] = $row['last_updated'];
        $rows[] = $tempRow;
    }
    $bulkData['rows'] = $rows;
    print_r(json_encode($bulkData));
}

if (isset($_GET['table']) && $_GET['table'] == 'sales_reports') {
    $offset = 0;
    $limit = 10;
    $sort = 'id';
    $order = 'DESC';
    $where = ' ';
    if (!empty($_GET['start_date']) && !empty($_GET['end_date'])) {
        $start_date = $db->escapeString($fn->xss_clean($_GET['start_date']));
        $end_date = $db->escapeString($fn->xss_clean($_GET['end_date']));
        $where .= " where DATE(date_added)>=DATE('" . $start_date . "') AND DATE(date_added)<=DATE('" . $end_date . "')";
    } else {
        $where .= " WHERE date_added > DATE_SUB(NOW(), INTERVAL 1 MONTH)";
    }

    if (isset($_GET['sort']))
        $sort = $db->escapeString($fn->xss_clean($_GET['sort']));
    if (isset($_GET['offset']))
        $offset = $db->escapeString($fn->xss_clean($_GET['offset']));
    if (isset($_GET['limit']))
        $limit = $db->escapeString($fn->xss_clean($_GET['limit']));
    if (isset($_GET['order']))
        $order = $db->escapeString($fn->xss_clean($_GET['order']));
    if (isset($_GET['search']) && !empty($_GET['search'])) {
        $search = $db->escapeString($fn->xss_clean($_GET['search']));
        $where .= " AND (o.id like '%" . $search . "%' OR o.mobile like '%" . $search . "%' OR u.name like '%" . $search . "%' OR address like '%" . $search . "%' OR date_added like '%" . $search . "%' OR `final_total` like '%" . $search . "%')";
    }
    $sql = "SELECT COUNT(o.id) as total FROM `orders` o LEFT JOIN users u ON u.id=o.user_id" . $where;
    $db->sql($sql);
    $res = $db->getResult();
    foreach ($res as $row) {
        $total = $row['total'];
    }
    $sql = "select o.id,o.user_id,o.mobile,o.address,o.date_added,o.final_total,u.name FROM orders o left join users u on u.id=o.user_id" . $where . " ORDER BY " . $sort . " " . $order . " LIMIT " . $offset . ", " . $limit;
    $db->sql($sql);
    $res = $db->getResult();
    $bulkData = array();
    $bulkData['total'] = $total;
    $rows = array();
    $tempRow = array();
    foreach ($res as $row) {

        $tempRow['id'] = $row['id'];
        $tempRow['user_id'] = $row['user_id'];
        $tempRow['name'] = $row['name'];
        if (ALLOW_MODIFICATION == 0 && !defined(ALLOW_MODIFICATION)) {
            $tempRow['mobile'] = str_repeat("*", strlen($row['mobile']) - 3) . substr($row['mobile'], -3);
        } else {
            $tempRow['mobile'] = $row['mobile'];
        }
        $tempRow['address'] = $row['address'];
        $tempRow['final_total'] = $row['final_total'];
        $tempRow['date_added'] = date('d-m-Y', strtotime($row['date_added']));
        $rows[] = $tempRow;
    }
    $bulkData['rows'] = $rows;
    $bulkData['rows'] = $rows;
    print_r(json_encode($bulkData));
}

if (isset($_GET['table']) && $_GET['table'] == 'invoice_reports') {
    $offset = 0;
    $limit = 10;
    $sort = 'id';
    $order = 'DESC';
    $where = ' ';
    if (!empty($_GET['start_date']) && !empty($_GET['end_date'])) {
        $start_date = $db->escapeString($fn->xss_clean($_GET['start_date']));
        $end_date = $db->escapeString($fn->xss_clean($_GET['end_date']));
        $where .= " where DATE(invoice_date) >= DATE('" . $start_date . "') AND DATE(invoice_date) <= DATE('" . $end_date . "')";
    } else {
        $where .= " WHERE invoice_date > DATE_SUB(NOW(), INTERVAL 1 MONTH)";
    }

    if (isset($_GET['sort']))
        $sort = $db->escapeString($fn->xss_clean($_GET['sort']));
    if (isset($_GET['offset']))
        $offset = $db->escapeString($fn->xss_clean($_GET['offset']));
    if (isset($_GET['limit']))
        $limit = $db->escapeString($fn->xss_clean($_GET['limit']));
    if (isset($_GET['order']))
        $order = $db->escapeString($fn->xss_clean($_GET['order']));
    if (isset($_GET['search']) && !empty($_GET['search'])) {
        $search = $db->escapeString($fn->xss_clean($_GET['search']));
        $where .= " AND (name like '%" . $search . "%' OR i.id like '%" . $search . "%' OR invoice_date like '%" . $search . "%' OR order_id like '%" . $search . "%' OR i.address like '%" . $search . "%' OR `order_date` like '%" . $search . "%'  OR `phone_number` like '%" . $search . "%' OR `order_list` like '%" . $search . "%' OR `email` like '%" . $search . "%' OR i.`discount` like '%" . $search . "%' OR `total_sale` like '%" . $search . "%' OR shipping_charge LIKE '%" . $search . "%' OR payment LIKE '%" . $search . "%')";
    }
    $sql = "SELECT COUNT(i.id) as total FROM invoice i" . $where;
    $db->sql($sql);
    $res = $db->getResult();
    foreach ($res as $row) {
        $total = $row['total'];
    }
    $sql = "SELECT i.*,o.tax_amount,o.tax_percentage,o.wallet_balance,o.promo_code,o.promo_discount,o.total FROM invoice i LEFT JOIN orders o ON o.id=i.order_id" . $where . " ORDER BY " . $sort . " " . $order . " LIMIT " . $offset . ", " . $limit;
    $db->sql($sql);
    $res = $db->getResult();
    for ($i = 0; $i < count($res); $i++) {
        $sql = "select oi.*,p.name as name, u.name as uname,v.measurement, (SELECT short_code FROM unit un where un.id=v.measurement_unit_id)as mesurement_unit_name,(SELECT status FROM orders o where o.id=oi.order_id)as order_status from `order_items` oi 
			    join product_variant v on oi.product_variant_id=v.id 
			    join products p on p.id=v.product_id 
			    JOIN users u ON u.id=oi.user_id 
			    where oi.order_id=" . $res[$i]['order_id'];
        $db->sql($sql);
        $res[$i]['items'] = $db->getResult();
    }
    $bulkData = array();
    $bulkData['total'] = $total;
    $rows = array();
    $tempRow = array();
    $temp = '';
    $total_amt = 0;
    foreach ($res as $row) {
        $items = $row['items'];
        foreach ($items as $item) {
            $temp .= "<b>Item ID :</b>" . $item['id'] . "<b> Product Variant Id :</b> " . $item['product_variant_id'] . "<b> Name : </b>" . $item['name'] . " <b>Unit : </b>" . $item['measurement'] . $item['mesurement_unit_name'] . " <b>Price : </b>" . $item['price'] . " <b>QTY : </b>" . $item['quantity'] . " <b>Subtotal : </b>" . $item['quantity'] * $item['price'] . "<br>------<br>";
            $total_amt += $item['sub_total'];
        }
        if (is_numeric($row['discount'])) {
            $discounted_amount = $row['total'] * $row['discount'] / 100; /*  */
            $final_total = $row['total'] - $discounted_amount;
            $discount_in_rupees = $row['total'] - $final_total;
            $discount_in_rupees = floor($discount_in_rupees);
            $tempRow['discount'] = $discount_in_rupees . '(' . $row['discount'] . '%)';
        } else {
            $tempRow['discount'] = 0;
        }
        $tempRow['id'] = $row['id'];
        $tempRow['invoice_date'] = date('d-m-Y', strtotime($row['invoice_date']));
        $tempRow['order_id'] = $row['order_id'];
        $tempRow['name'] = $row['name'];
        $tempRow['address'] = $row['address'];
        $tempRow['order_date'] = date('d-m-Y h:i:s', strtotime($row['order_date']));
        if (ALLOW_MODIFICATION == 0 && !defined(ALLOW_MODIFICATION)) {
            $tempRow['phone_number'] = str_repeat("*", strlen($row['phone_number']) - 3) . substr($row['phone_number'], -3);
            $tempRow['email'] = str_repeat("*", strlen($row['email']) - 13) . substr($row['email'], -13);
        } else {
            $tempRow['email'] = $row['email'];
            $tempRow['phone_number'] = $row['phone_number'];
        }

        $tempRow['items'] = $temp;
        $tempRow['tax'] = $row['tax_amount'] . '(' . $row['tax_percentage'] . '%)';
        $tempRow['promo_discount'] = $row['promo_discount'];
        $tempRow['wallet_balance'] = $row['wallet_balance'];
        // $tempRow['discount'] = $discount_in_rupees . '(' . $row['discount'] . '%)';
        $tempRow['promo_code'] = $row['promo_code'];
        $tempRow['total_sale'] = $row['total'];
        $tempRow['shipping_charge'] = $row['shipping_charge'];
        $tempRow['payment'] = ceil($row['payment']);
        $tempRow['action'] = '<a href="order-detail.php?id=' . $row['order_id'] . '" title="View Order"><i class="fa fa-folder-open"></i>&nbsp;Order&nbsp;</a> <a href="invoice.php?id=' . $row['order_id'] . '"><i class="fa fa-eye" title="View Invoice"></i>&nbsp;Invoice</a>';

        $rows[] = $tempRow;
    }
    $bulkData['rows'] = $rows;
    print_r(json_encode($bulkData));
}

if (isset($_GET['table']) && $_GET['table'] == 'taxes') {

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

    if (isset($_GET['search'])) {
        $search = $db->escapeString($fn->xss_clean($_GET['search']));
        $where = " Where `id` like '%" . $search . "%' OR `title` like '%" . $search . "%'";
    }

    $sql = "SELECT COUNT(`id`) as total FROM `taxes` " . $where;
    $db->sql($sql);
    $res = $db->getResult();
    foreach ($res as $row)
        $total = $row['total'];

    $sql = "SELECT * FROM `taxes` " . $where . " ORDER BY " . $sort . " " . $order . " LIMIT " . $offset . ", " . $limit;
    $db->sql($sql);
    $res = $db->getResult();

    $bulkData = array();
    $bulkData['total'] = $total;
    $rows = $tempRow = array();

    foreach ($res as $row) {

        $operate = ' <a href="edit-tax.php?id=' . $row['id'] . '"><i class="fa fa-edit"></i>Edit</a>';
        $operate .= ' <a class="btn-xs btn-danger" href="delete-tax.php?id=' . $row['id'] . '"><i class="fa fa-trash-o"></i>Delete</a>';

        $tempRow['id'] = $row['id'];
        $tempRow['title'] = $row['title'];
        $tempRow['percentage'] = $row['percentage'];
        if ($row['status'] == 0)
            $tempRow['status'] = "<label class='label label-danger'>Deactive</label>";
        else
            $tempRow['status'] = "<label class='label label-success'>Active</label>";;
        $tempRow['operate'] = $operate;
        $rows[] = $tempRow;
    }
    $bulkData['rows'] = $rows;
    print_r(json_encode($bulkData));
}

if (isset($_GET['table']) && $_GET['table'] == 'product_sales_report') {
    $where = ' ';
    $offset = (isset($_GET['offset']) && !empty($_GET['offset']) && is_numeric($_GET['offset'])) ? $db->escapeString($_GET['offset']) : 0;
    $limit = (isset($_GET['limit']) && !empty($_GET['limit']) && is_numeric($_GET['limit'])) ? $db->escapeString($_GET['limit']) : 10;
    $sort = (isset($_GET['sort']) && !empty($_GET['sort'])) ? $db->escapeString($_GET['sort']) : 'id';
    $order = (isset($_GET['order']) && !empty($_GET['order'])) ? $db->escapeString($_GET['order']) : 'DESC';
    $currency = $fn->get_settings('currency', false);
    if (!empty($_GET['start_date']) && !empty($_GET['end_date'])) {
        $start_date = $db->escapeString($fn->xss_clean($_GET['start_date']));
        $end_date = $db->escapeString($fn->xss_clean($_GET['end_date']));
        $where .= " where DATE(oi.date_added)>=DATE('" . $start_date . "') AND DATE(oi.date_added)<=DATE('" . $end_date . "')";
    } else {
        $where .= " WHERE oi.date_added > DATE_SUB(NOW(), INTERVAL 1 MONTH)";
    }
    if (isset($_GET['search']) && !empty($_GET['search'])) {
        $search = $db->escapeString($fn->xss_clean($_GET['search']));
        //  if (!empty($_GET['start_date']) && !empty($_GET['end_date'])) {
        $where .= " AND (oi.id like '%" . $search . "%' OR p.name like '%" . $search . "%' OR u.name like '%" . $search . "%' )";
        //  }
    }
    // $sql = "SELECT count(oi.id) as total FROM `order_items` oi join `product_variant` pv ON oi.product_variant_id=pv.id join products p ON pv.product_id=p.id join unit u on pv.measurement_unit_id=u.id $where GROUP by (pv.id)";
    $sql = "SELECT COUNT(DISTINCT(product_variant_id)) as total from order_items";
    $db->sql($sql);
    $res = $db->getResult();
    // print_r($res);
    $total = $res[0]['total'];
    $sql = "SELECT pv.product_id,p.name as p_name, pv.measurement,u.short_code as u_name,oi.*, 
        (SELECT count(oi.product_variant_id) FROM `order_items` oi where pv.id = oi.product_variant_id) as total_sales, 
        (SELECT SUM(oi.sub_total) FROM `order_items` oi where pv.id = oi.product_variant_id) as total_price
        FROM `order_items` oi join `product_variant` pv ON oi.product_variant_id=pv.id join products p ON pv.product_id=p.id join unit u on pv.measurement_unit_id=u.id $where GROUP by (pv.id) ORDER BY $sort $order LIMIT $offset, $limit";
    $db->sql($sql);
    $res = $db->getResult();
    $tempRow = $bulkData = $rows = array();
    $bulkData['total'] = $total;
    foreach ($res as $row) {
        $tempRow['product_name'] = $row['p_name'];
        $tempRow['product_varient_id'] = $row['product_variant_id'];
        $tempRow['unit_name'] = $row['measurement'] . ' ' . $row['u_name'];
        $tempRow['total_sales'] = $row['total_sales'];
        $tempRow['total_price'] = $currency . ' ' . number_format($row['total_price']);
        // sub total = subtotal - (tax_amount * qty)
        // $total_price = $row['sub_total'] - ($row['tax_amount'] * $row['quantity']);
        // $tempRow['total_price'] = $currency.' '.number_format($total_price);
        $rows[] = $tempRow;
    }
    $bulkData['rows'] = $rows;
    print_r(json_encode($bulkData));
}

$db->disconnect();
