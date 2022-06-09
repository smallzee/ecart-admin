<?php
header('Access-Control-Allow-Origin: *');
header("Content-Type: application/json");
header("Expires: 0");
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
header("Cache-Control: no-store, no-cache, must-revalidate");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header('Access-Control-Allow-Origin: *');


include_once('../../includes/crud.php');
include_once('../../includes/custom-functions.php');
include_once('verify-token.php');
$fn = new custom_functions();
$db = new Database();
$db->connect();
include_once('../../includes/variables.php');

include_once('../../delivery-boy/api/send-email.php');
// $config = $fn->get_configurations();

$config = $fn->get_configurations();
$time_slot_config = $fn->time_slot_config();
if (isset($config['system_timezone']) && isset($config['system_timezone_gmt'])) {
    date_default_timezone_set($config['system_timezone']);
    $db->sql("SET `time_zone` = '" . $config['system_timezone_gmt'] . "'");
} else {
    date_default_timezone_set('Asia/Kolkata');
    $db->sql("SET `time_zone` = '+05:30'");
}
$low_stock_limit = $config['low-stock-limit'];

/* 
-------------------------------------------
APIs for Admin App
-------------------------------------------
1. get_orders
2. get_customers
3. get_products
4. get_delivery_boys
5. get_financial_statistics
6. login
7. update_admin_fcm_id
8. get_privacy_and_terms
9. update_order_status
10. get_permissions
11. update_order_item_status
12. add_delivery_boy
13. update_delivery_boy
14. delete_delivery_boy
15. delivery_boy_fund_transfers
16. delivery_boy_transfer_fund
17. get_all_data
18. get_categories
19. get_subcategories

-------------------------------------------

-------------------------------------------

*/

if (!verify_token()) {
    return false;
}

if (!isset($_POST['accesskey'])  || trim($_POST['accesskey']) != $access_key) {
    $response['error'] = true;
    $response['message'] = "No Accsess key found!";
    print_r(json_encode($response));
    return false;
    exit();
}

if (isset($_POST['get_orders']) && !empty($_POST['get_orders'])) {
    /* 
    1.get_orders
        accesskey:90336
        get_orders:1
        order_id:12      {optional}
        start_date:2020-10-29  {optional} {YYYY-mm-dd}
        end_date:2020-10-29  {optional} {YYYY-mm-dd}
        filter_order:received | processed | shipped | delivered | cancelled | returned | awaiting_payment {optional}
        limit:10  {optional}
        offset:0    {optional}
        sort:id      {optional}
        order:ASC/DESC {optional}
        search:value {optional}
    */
    $where = '';
    $offset = (isset($_POST['offset']) && !empty(trim($_POST['offset'])) && is_numeric($_POST['offset'])) ? $db->escapeString(trim($fn->xss_clean($_POST['offset']))) : 0;
    $limit = (isset($_POST['limit']) && !empty(trim($_POST['limit'])) && is_numeric($_POST['limit'])) ? $db->escapeString(trim($fn->xss_clean($_POST['limit']))) : 10;
    $sort = (isset($_POST['sort']) && !empty(trim($_POST['sort']))) ? $db->escapeString(trim($fn->xss_clean($_POST['sort']))) : 'id';
    $order = (isset($_POST['order']) && !empty(trim($_POST['order']))) ? $db->escapeString(trim($fn->xss_clean($_POST['order']))) : 'DESC';
    if (!empty($_POST['start_date']) && !empty($_POST['end_date'])) {
        $start_date = $db->escapeString($fn->xss_clean($_POST['start_date']));
        $end_date = $db->escapeString($fn->xss_clean($_POST['end_date']));
        $where .= " where DATE(date_added)>=DATE('" . $start_date . "') AND DATE(date_added)<=DATE('" . $end_date . "')";
    }
    if (isset($_POST['search']) && !empty($_POST['search'])) {
        $search = $db->escapeString($fn->xss_clean($_POST['search']));
        if (!empty($_POST['start_date']) && !empty($_POST['end_date'])) {
            $where .= " AND (name like '%" . $search . "%' OR o.id like '%" . $search . "%' OR o.mobile like '%" . $search . "%' OR address like '%" . $search . "%' OR `payment_method` like '%" . $search . "%' OR `delivery_charge` like '%" . $search . "%' OR `delivery_time` like '%" . $search . "%' OR o.`status` like '%" . $search . "%' OR `date_added` like '%" . $search . "%')";
        } else {
            $where .= " where (name like '%" . $search . "%' OR o.id like '%" . $search . "%' OR o.mobile like '%" . $search . "%' OR address like '%" . $search . "%' OR `payment_method` like '%" . $search . "%' OR `delivery_charge` like '%" . $search . "%' OR `delivery_time` like '%" . $search . "%' OR o.`status` like '%" . $search . "%' OR `date_added` like '%" . $search . "%')";
        }
    }
    if (isset($_POST['filter_order']) && $_POST['filter_order'] != '') {
        $filter_order = $db->escapeString($fn->xss_clean($_POST['filter_order']));
        if (isset($_POST['search']) && $_POST['search'] != '') {
            $where .= " and `active_status`='" . $filter_order . "'";
        } elseif (isset($_POST['start_date']) && $_POST['start_date'] != '') {
            $where .= " and `active_status`='" . $filter_order . "'";
        } else {
            $where .= " where `active_status`='" . $filter_order . "'";
        }
    }
    if (isset($_POST['order_id']) && !empty($_POST['order_id']) && is_numeric($_POST['order_id'])) {
        $order_id = $db->escapeString($fn->xss_clean($_POST['order_id']));
        if ($where != "") {
            $where .= " and o.`id`=$order_id";
        } else {
            $where .= " where o.`id`=$order_id";
        }
    }
    $item_discount = 0;
    $orders_join = " JOIN users u ON u.id=o.user_id ";
    $sql = "SELECT COUNT(o.id) as total FROM `orders` o " . $orders_join . " " . $where;
    $db->sql($sql);
    $res = $db->getResult();
    if (!empty($res)) {
        foreach ($res as $row) {
            $total = $row['total'];
        }
        $sql = "select o.*,u.name as name,u.country_code as country_code FROM orders o " . $orders_join . " " . $where . " ORDER BY " . $sort . " " . $order . " LIMIT " . $offset . ", " . $limit;
        $db->sql($sql);
        $res = $db->getResult();
        if (!empty($res)) {
            for ($i = 0; $i < count($res); $i++) {
                $sql = "select oi.*,p.name as name, v.measurement,p.image, (SELECT short_code FROM unit un where un.id=v.measurement_unit_id)as mesurement_unit_name from `order_items` oi 
                join product_variant v on oi.product_variant_id=v.id 
                join products p on p.id=v.product_id 
                where oi.order_id=" . $res[$i]['id'];
                $db->sql($sql);
                $res[$i]['items'] = $db->getResult();
            }
            $rows = array();
            $tempRow = array();
            foreach ($res as $row) {
                $items = $row['items'];
                $items1 = array();
                $total_amt = 0;
                foreach ($items as $item) {
                    $price = $item['discounted_price'] == 0 ? $item['price'] : $item['discounted_price'];
                    $temp = array(
                        'id' => $item['id'],
                        'product_variant_id' => $item['product_variant_id'],
                        'name' => $item['name'],
                        'unit' => $item['measurement'] . " " . $item['mesurement_unit_name'],
                        'product_image' => DOMAIN_URL . $item['image'],
                        'price' => $price,
                        'quantity' => $item['quantity'],
                        'subtotal' => $item['quantity'] * $price,
                        'active_status' => $item['active_status']
                    );
                    $total_amt += $item['sub_total'];
                    $items1[] = $temp;
                }
                if (!empty($row['items'][0]['discount'])) {
                    $item_discount = $row['items'][0]['discount'];
                    $discounted_amount = $row['total'] * $row['items'][0]['discount'] / 100;
                } else {
                    $discounted_amount = 0;
                }
                $final_total = $row['total'] - $discounted_amount;
                $discount_in_rupees = $row['total'] - $final_total;


                $discount_in_rupees = floor($discount_in_rupees);
                $tempRow['id'] = $row['id'];
                $tempRow['user_id'] = $row['user_id'];
                $tempRow['otp'] = (!empty($row['otp']) && $row['otp'] != null) ? $row['otp'] : 0;
                $tempRow['name'] = $row['name'];
                $tempRow['mobile'] = $row['mobile'];
                $tempRow['delivery_charge'] = $row['delivery_charge'];
                $tempRow['items'] = $items1;
                $tempRow['total'] = $total_amt;
                $tempRow['tax'] = $row['tax_amount'] . '(' . $row['tax_percentage'] . '%)';
                $tempRow['promo_discount'] = $row['promo_discount'];
                $tempRow['wallet_balance'] = $row['wallet_balance'];
                $tempRow['discount'] = $discount_in_rupees . '(' . $item_discount . '%)';
                $tempRow['qty'] = (isset($row['items'][0]['quantity']) && !empty($row['items'][0]['quantity'])) ? $row['items'][0]['quantity'] : "0";
                $tempRow['final_total'] = ceil($row['final_total']);
                $tempRow['promo_code'] = $row['promo_code'];
                $tempRow['deliver_by'] = $row['delivery_boy_id'];
                if ($row['delivery_boy_id'] != 0 && $row['delivery_boy_id'] != "") {
                    $d_name = $fn->get_data($columns = ['name'], 'id=' . $row['delivery_boy_id'], 'delivery_boys');
                    $tempRow['deliver_boy_name'] = $d_name[0]['name'];
                } else {
                    $tempRow['deliver_boy_name'] = "";
                }
                $tempRow['payment_method'] = $row['payment_method'];
                $tempRow['address'] = $row['address'];
                $tempRow['latitude'] = $row['latitude'];
                $tempRow['longitude'] = $row['longitude'];
                $tempRow['delivery_time'] = $row['delivery_time'];
                $tempRow['active_status'] = $row['active_status'];
                $tempRow['wallet_balance'] = $row['wallet_balance'];
                $tempRow['date_added'] = date('d-m-Y', strtotime($row['date_added']));
                $tempRow['country_code'] = $row['country_code'];
                $rows1[] = $tempRow;
            }
            $response['error'] = false;
            $response['message'] = "Orders fatched successfully.";
            $response['total'] = $total;
            $response['data'] = $rows1;
        } else {
            $response['error'] = true;
            $response['message'] = "Order not found.";
        }
    } else {
        $response['error'] = true;
        $response['message'] = "Something went wrong, please try again leter.";
    }
    print_r(json_encode($response));
}


/* 
---------------------------------------------------------------------------------------------------------
*/

if (isset($_POST['get_customers']) && !empty($_POST['get_customers'])) {
    /* 
   2.get_customers
	   accesskey:90336
	   get_customers:1
	   city_id:119  {optional}
	   limit:10  {optional}
	   offset:0    {optional}
	   sort:id      {optional}
	   order:ASC/DESC {optional}
	   search:value {optional}
   */
    $where = '';
    $offset = (isset($_POST['offset']) && !empty(trim($_POST['offset'])) && is_numeric($_POST['offset'])) ? $db->escapeString(trim($fn->xss_clean($_POST['offset']))) : 0;
    $limit = (isset($_POST['limit']) && !empty(trim($_POST['limit'])) && is_numeric($_POST['limit'])) ? $db->escapeString(trim($fn->xss_clean($_POST['limit']))) : 10;

    $sort = (isset($_POST['sort']) && !empty(trim($_POST['sort']))) ? $db->escapeString(trim($fn->xss_clean($_POST['sort']))) : 'id';
    $order = (isset($_POST['order']) && !empty(trim($_POST['order']))) ? $db->escapeString(trim($fn->xss_clean($_POST['order']))) : 'DESC';

    if (isset($_POST['city_id']) && !empty($_POST['city_id'])) {
        $filter_user = $db->escapeString($fn->xss_clean($_POST['city_id']));
        $where .= ' where u.city=' . $filter_user;
    }
    if (isset($_POST['search']) && !empty($_POST['search'])) {
        $search = $db->escapeString($fn->xss_clean($_POST['search']));
        if (isset($_POST['city_id']) && !empty($_POST['city_id'])) {
            $where .= " and `id` like '%" . $search . "%' OR `name` like '%" . $search . "%' OR `email` like '%" . $search . "%' OR `mobile` like '%" . $search . "%' OR `city` like '%" . $search . "%' OR `area` like '%" . $search . "%' OR `street` like '%" . $search . "%' OR `status` like '%" . $search . "%' OR `created_at` like '%" . $search . "%'";
        } else {
            $where .= " Where `id` like '%" . $search . "%' OR `name` like '%" . $search . "%' OR `email` like '%" . $search . "%' OR `mobile` like '%" . $search . "%' OR `city` like '%" . $search . "%' OR `area` like '%" . $search . "%' OR `street` like '%" . $search . "%' OR `status` like '%" . $search . "%' OR `created_at` like '%" . $search . "%'";
        }
    }
    $sql = "SELECT COUNT(id) as total FROM `users` u " . $where;
    $db->sql($sql);
    $res = $db->getResult();
    if (!empty($res)) {
        foreach ($res as $row)
            $total = $row['total'];

        $sql = "SELECT *,(SELECT name FROM area a WHERE a.id=u.area) as area_name,(SELECT name FROM city c WHERE c.id=u.city) as city_name FROM `users` u " . $where . " ORDER BY `" . $sort . "` " . $order . " LIMIT " . $offset . ", " . $limit;
        $db->sql($sql);
        $res = $db->getResult();
        $rows = array();
        $tempRow = array();

        foreach ($res as $row) {
            $tempRow['id'] = $row['id'];
            $tempRow['name'] = $row['name'];
            $path = DOMAIN_URL . 'upload/profile/';
            if (!empty($row['profile'])) {
                $tempRow['profile'] = $path . $row['profile'];
            } else {
                $tempRow['profile'] = $path . "default_user_profile.png";
            }
            $tempRow['email'] = $row['email'];
            $tempRow['mobile'] = $row['mobile'];
            $tempRow['balance'] = $row['balance'];
            $tempRow['referral_code'] = $row['referral_code'];
            $tempRow['friends_code'] = !empty($row['friends_code']) ? $row['friends_code'] : '';
            $tempRow['city_id'] = $row['city'];
            $tempRow['city'] = !empty($row['city_name']) ? $row['city_name'] : "";
            $tempRow['area_id'] = $row['area'];
            $tempRow['area'] = !empty($row['area_name']) ? $row['area_name'] : "";
            $tempRow['street'] = $row['street'];
            $tempRow['apikey'] = $row['apikey'];
            $tempRow['status'] = $row['status'];
            $tempRow['created_at'] = $row['created_at'];
            $rows[] = $tempRow;
        }
        $response['error'] = false;
        $response['message'] = "Customers fatched successfully.";
        $response['total'] = $total;
        $response['data'] = $rows;
    } else {
        $response['error'] = true;
        $response['message'] = "Something went wrong, please try again leter.";
    }
    print_r(json_encode($response));
}

/* 
---------------------------------------------------------------------------------------------------------
*/

if (isset($_POST['get_products']) && !empty($_POST['get_products'])) {
    /* 
   3.get_products
	   	accesskey:90336
        get_products:1
        filter:low_stock | out_stock
	   	id:119  {optional}
	   	category_id:119  {optional}
	   	subcategory_id:119  {optional}
		limit:10 // {optional}
    	offset:0 // {optional}
    	sort:new / old / high / low // {optional}
	   	search:value {optional}
   */
    $where = "";
    $sort = (isset($_POST['sort']) && !empty($_POST['sort'])) ? $db->escapeString($fn->xss_clean($_POST['sort'])) : 'id';
    $filter = (isset($_POST['filter']) && !empty($_POST['filter'])) ? $db->escapeString($fn->xss_clean($_POST['filter'])) : '';
    $subcategory_id = (isset($_POST['category_id']) && is_numeric($_POST['category_id'])) ? $db->escapeString($fn->xss_clean($_POST['category_id'])) : "";
    $user_id = (isset($_POST['user_id']) && is_numeric($_POST['user_id'])) ? $db->escapeString($fn->xss_clean($_POST['user_id'])) : "";
    $limit = (isset($_POST['limit']) && !empty($_POST['limit']) && is_numeric($_POST['limit'])) ? $db->escapeString($fn->xss_clean($_POST['limit'])) : '10';
    $offset = (isset($_POST['offset']) && !empty($_POST['offset']) && is_numeric($_POST['offset'])) ? $db->escapeString($fn->xss_clean($_POST['offset'])) : '0';

    if ($sort == 'new') {
        $sort = 'ORDER BY date_added DESC';
        $price = 'MIN(pv.price)';
        $price_sort = 'ORDER BY pv.price ASC';
    } elseif ($sort == 'old') {
        $sort = 'ORDER BY date_added ASC';
        $price = 'MIN(pv.price)';
        $price_sort = 'ORDER BY pv.price ASC';
    } elseif ($sort == 'high') {
        $sort = 'ORDER BY price DESC';
        $price = 'MAX(pv.price)';
        $price_sort = 'ORDER BY pv.price DESC';
    } elseif ($sort == 'low') {
        $sort = 'ORDER BY price ASC';
        $price = 'MIN(pv.price)';
        $price_sort = 'ORDER BY pv.price ASC';
    } else {
        $sort = 'ORDER BY p.row_order ASC';
        $price = 'MIN(pv.price)';
        $price_sort = 'ORDER BY pv.price ASC';
    }
    if (isset($_POST['category_id']) && !empty($_POST['category_id'])) {
        $category_id = $db->escapeString($fn->xss_clean($_POST['category_id']));
        $where .= ' and category_id = ' . $category_id;
    }
    if (isset($_POST['category_id']) && !empty($_POST['category_id']) && isset($_POST['subcategory_id']) && !empty($_POST['subcategory_id'])) {
        $category_id = $db->escapeString($fn->xss_clean($_POST['category_id']));
        $subcategory_id = $db->escapeString($fn->xss_clean($_POST['subcategory_id']));
        $where .= " and category_id = $category_id and subcategory_id = $subcategory_id";
    }
    if (isset($_POST['subcategory_id']) && !empty($_POST['subcategory_id'])) {
        $subcategory_id = $db->escapeString($fn->xss_clean($_POST['subcategory_id']));
        $where .= ' and subcategory_id = ' . $subcategory_id;
    }
    if (isset($_POST['id']) && !empty($_POST['id'])) {
        $id = $db->escapeString($fn->xss_clean($_POST['id']));
        $where .= ' and p.id = ' . $id;
    }
    if (isset($_POST['search']) && !empty($_POST['search'])) {
        $search = $db->escapeString($fn->xss_clean($_POST['search']));
        $where .= " and (p.`id` like '%" . $search . "%' OR p.`name` like '%" . $search . "%' OR p.`slug` like '%" . $search . "%' OR `category_id` like '%" . $search . "%' OR p.`subcategory_id` like '%" . $search . "%' OR p.`manufacturer` like '%" . $search . "%' OR p.`made_in` like '%" . $search . "%' OR p.`return_status` like '%" . $search . "%' OR p.`description` like '%" . $search . "%')";
    }
    if ($filter == "out_stock") {
        $where .= " AND pv.stock <=0 AND pv.serve_for = 'Sold Out'";
    }
    if ($filter == "low_stock") {
        $where .=  " AND pv.stock < $low_stock_limit AND pv.serve_for = 'Available'";
    }

    $sql = "SELECT count(p.id) as total FROM products p join product_variant pv on pv.product_id=p.id WHERE p.`status`=1 $where";
    $db->sql($sql);
    $res = $db->getResult();
    foreach ($res as $row) {
        $total = $row['total'];
    }
    $sql = "SELECT p.* FROM products p join product_variant pv on pv.product_id=p.id WHERE p.`status`=1 $where $sort LIMIT $offset, $limit";
    $db->sql($sql);
    $res = $db->getResult();
    $product = array();

    $i = 0;
    $sql = "SELECT id FROM cart limit 1";
    $db->sql($sql);
    $res_cart = $db->getResult();
    foreach ($res as $row) {
        $sql = "SELECT *,(SELECT short_code FROM unit u WHERE u.id=pv.measurement_unit_id) as measurement_unit_name,(SELECT short_code FROM unit u WHERE u.id=pv.stock_unit_id) as stock_unit_name FROM product_variant pv WHERE  pv.product_id=" . $row['id'] . " " . $price_sort . " ";
        $db->sql($sql);

        $row['other_images'] = json_decode($row['other_images'], 1);
        $row['other_images'] = (empty($row['other_images'])) ? array() : $row['other_images'];

        for ($j = 0; $j < count($row['other_images']); $j++) {
            $row['other_images'][$j] = DOMAIN_URL . $row['other_images'][$j];
        }
        if ($row['tax_id'] == 0) {
            $row['tax_title'] = "";
            $row['tax_percentage'] = "";
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

        $row['image'] = DOMAIN_URL . $row['image'];
        $product[$i] = $row;
        $variants = $db->getResult();
        for ($k = 0; $k < count($variants); $k++) {
            if ($variants[$k]['stock'] <= 0) {
                $variants[$k]['serve_for'] = 'Sold Out';
            }
            if ($variants[$k]['stock'] > 0) {
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
        $product[$i]['variants'] = $variants;
        $i++;
    }
    if (!empty($product)) {
        $response['error'] = false;
        $response['total'] = $total;
        $response['message'] = "products fetched successfully.";
        $response['data'] = $product;
    } else {
        $response['error'] = true;
        $response['message'] = "products not fetched.";
    }
    print_r(json_encode($response));
}

/* 
---------------------------------------------------------------------------------------------------------
*/

if (isset($_POST['get_delivery_boys']) && !empty($_POST['get_delivery_boys'])) {
    /* 
   4.get_delivery_boys
	   accesskey:90336
	   get_delivery_boys:1
	   id:292  {optional}
	   limit:10  {optional}
	   offset:0    {optional}
	   sort:id      {optional}
	   order:ASC/DESC {optional}
	   search:value {optional}
   */
    $where = '';
    $offset = (isset($_POST['offset']) && !empty(trim($_POST['offset'])) && is_numeric($_POST['offset'])) ? $db->escapeString(trim($fn->xss_clean($_POST['offset']))) : 0;
    $limit = (isset($_POST['limit']) && !empty(trim($_POST['limit'])) && is_numeric($_POST['limit'])) ? $db->escapeString(trim($fn->xss_clean($_POST['limit']))) : "";

    $sort = (isset($_POST['sort']) && !empty(trim($_POST['sort']))) ? $db->escapeString(trim($fn->xss_clean($_POST['sort']))) : 'id';
    $order = (isset($_POST['order']) && !empty(trim($_POST['order']))) ? $db->escapeString(trim($fn->xss_clean($_POST['order']))) : 'DESC';

    if (isset($_POST['id']) && !empty($_POST['id'])) {
        $id = $db->escapeString($fn->xss_clean($_POST['id']));
        $where .= ' where id=' . $id;
    }
    if (isset($_POST['search']) && !empty($_POST['search'])) {
        $search = $db->escapeString($fn->xss_clean($_POST['search']));
        if (isset($_POST['id']) && !empty($_POST['id'])) {
            $where .= " and `id` like '%" . $search . "%' OR `name` like '%" . $search . "%' OR `mobile` like '%" . $search . "%' OR `address` like '%" . $search . "%' OR `mobile` like '%" . $search . "%'";
        } else {
            $where .= " Where `id` like '%" . $search . "%' OR `name` like '%" . $search . "%' OR `mobile` like '%" . $search . "%' OR `address` like '%" . $search . "%' OR `mobile` like '%" . $search . "%'";
        }
    }
    $sql = "SELECT COUNT(id) as total FROM `delivery_boys` " . $where;
    $db->sql($sql);
    $res = $db->getResult();
    if (!empty($res)) {
        foreach ($res as $row)
            $total = $row['total'];
        if ($limit == "") {
            $sql = "SELECT * FROM `delivery_boys` " . $where . " ORDER BY " . $sort . " " . $order;
        } else {
            $sql = "SELECT * FROM `delivery_boys` " . $where . " ORDER BY " . $sort . " " . $order . " LIMIT " . $offset . ", " . $limit;
        }
        $db->sql($sql);
        $res = $db->getResult();
        $rows = array();
        $tempRow = array();

        $path = 'upload/delivery-boy/';
        foreach ($res as $row) {
            $tempRow['id'] = $row['id'];
            $tempRow['name'] = $row['name'];
            $tempRow['mobile'] = $row['mobile'];
            $tempRow['address'] = !empty($row['address']) ? $row['address'] : "";
            $tempRow['bonus'] = $row['bonus'];
            $tempRow['balance'] = ceil($row['balance']);
            if (!empty($row['driving_license'])) {
                $tempRow['driving_license'] = DOMAIN_URL . $path . $row['driving_license'];
                $tempRow['national_identity_card'] = DOMAIN_URL . $path . $row['national_identity_card'];
            } else {
                $tempRow['national_identity_card'] = "No National Identity Card";
                $tempRow['driving_license'] = "No Driving License";
            }
            $tempRow['dob'] = !empty($row['dob']) ? $row['dob'] : "";
            $tempRow['bank_account_number'] = !empty($row['bank_account_number']) ? $row['bank_account_number'] : "";
            $tempRow['bank_name'] = !empty($row['bank_name']) ? $row['bank_name'] : "";
            $tempRow['account_name'] = !empty($row['account_name']) ? $row['account_name'] : "";
            $tempRow['other_payment_information'] = (!empty($row['other_payment_information'])) ? $row['other_payment_information'] : "";
            $tempRow['ifsc_code'] = !empty($row['ifsc_code']) ? $row['ifsc_code'] : "";
            if ($row['status'] == 0)
                $tempRow['status'] = "Deactive";
            else
                $tempRow['status'] = "Active";
            $rows[] = $tempRow;
        }
        $response['error'] = false;
        $response['message'] = "Delivery Boys fatched successfully.";
        $response['total'] = $total;
        $response['data'] = $rows;
    } else {
        $response['error'] = true;
        $response['message'] = "Something went wrong, please try again leter.";
    }
    print_r(json_encode($response));
}

/* 
---------------------------------------------------------------------------------------------------------
*/

if (isset($_POST['get_financial_statistics']) && !empty($_POST['get_financial_statistics'])) {
    /* 
   5. get_financial_statistics
	   accesskey:90336
	   get_financial_statistics:1
   */
    $config = $fn->get_configurations();
    $total_orders = $total_products = $total_users = $total_sold_out_products = $total_low_stock_count = 0;

    $low_stock_limit = isset($config['low-stock-limit']) && (!empty($config['low-stock-limit'])) ? $config['low-stock-limit'] : 0;
    $sql = "SELECT * FROM settings WHERE variable='currency'";
    $db->sql($sql);
    $res_currency = $db->getResult();

    // $total_orders = $fn->rows_count('orders');
    $total_orders = 0;
    $total_products = $fn->rows_count('products');
    $total_users = $fn->rows_count('users');
    $total_sold_out_products = $fn->sold_out_count();
    $total_low_stock_count = $fn->low_stock_count($low_stock_limit);

    $year = date("Y");
    $curdate = date('Y-m-d');
    $sql = "SELECT SUM(final_total) AS total_sale,DATE(date_added) AS order_date FROM orders WHERE YEAR(date_added) = '$year' AND DATE(date_added)<'$curdate' AND `active_status`='delivered' GROUP BY DATE(date_added) ORDER BY DATE(date_added) DESC  LIMIT 0,7";
    $db->sql($sql);
    $result_order = $db->getResult();

    if (!empty($total_products) && !empty($total_users)) {

        $response['error'] = false;
        $response['total_orders'] = $total_orders;
        $response['total_products'] = $total_products;
        $response['total_users'] = $total_users;
        $response['total_sold_out_products'] = $total_sold_out_products;
        $response['total_low_stock_count'] = $total_low_stock_count;
        $response['currency'] = $res_currency[0]['value'];
        $response['order_date'] = (!empty($result_order)) ? $result_order[0]['order_date'] : "0";
        $response['total_sale'] = (!empty($result_order)) ? $result_order[0]['total_sale'] : "0";
    } else {
        $response['error'] = true;
        $response['message'] = "Something went wrong, please try again leter.";
    }
    print_r(json_encode($response));
}

/* 
---------------------------------------------------------------------------------------------------------
*/

if (isset($_POST['login']) && !empty($_POST['login'])) {
    /* 
   6.login
	   accesskey:90336
	   username:admin
	   password:admin123
	   fcm_id:YOUR_FCM_ID  {optional}
	   login:1
   */

    if (empty(trim($_POST['username']))) {
        $response['error'] = true;
        $response['message'] = "Username should be filled!";
        print_r(json_encode($response));
        return false;
        exit();
    }
    if (empty($_POST['password'])) {
        $response['error'] = true;
        $response['message'] = "Password should be filled!";
        print_r(json_encode($response));
        return false;
        exit();
    }
    $username = $db->escapeString(trim($fn->xss_clean($_POST['username'])));
    $password = md5($db->escapeString($fn->xss_clean($_POST['password'])));
    $sql = "SELECT * FROM `admin` WHERE username = '" . $username . "' AND password = '" . $password . "'";
    $db->sql($sql);
    $res = $db->getResult();
    $num = $db->numRows($res);
    $rows = $tempRow = $permissions = $permission = array();
    if ($num == 1) {
        $admin_id = $res[0]['id'];

        $fcm_id = (isset($_POST['fcm_id']) && !empty($_POST['fcm_id'])) ? $db->escapeString($fn->xss_clean($_POST['fcm_id'])) : "";
        if (!empty($fcm_id)) {
            $sql1 = "update admin set `fcm_id` ='$fcm_id' where id = $admin_id";
            $db->sql($sql1);
            $db->sql($sql);
            $res = $db->getResult();
        }
        unset($res[0]['password']);
        $permissions = json_decode($res[0]['permissions'], true);

        foreach ($permissions as $per) {

            if (!array_key_exists('create', $per)) {
                $per['create'] = "0";
            }
            if (!array_key_exists('read', $per)) {
                $per['read'] = "0";
            }
            if (!array_key_exists('update', $per)) {
                $per['update'] = "0";
            }
            if (!array_key_exists('delete', $per)) {
                $per['delete'] = "0";
            }
            $permission[] = $per;
        }

        $asd = array_combine(array_keys($permissions), $permission);
        unset($res[0]['permissions']);
        $response['error'] = false;
        $response['message'] = "Admin login successfully";
        $response['permissions'] = $asd;
        $response['data'] = $res;
    } else {
        $response['error'] = true;
        $response['message'] = "Invalid username or password!";
    }
    print_r(json_encode($response));
}

/* 
---------------------------------------------------------------------------------------------------------
*/

if (isset($_POST['update_admin_fcm_id']) && !empty($_POST['update_admin_fcm_id'])) {
    /* 
   7.update_admin_fcm_id
	   accesskey:90336
	   id:1
	   fcm_id:YOUR_FCM_ID
	   update_admin_fcm_id:1
   */

    if (empty($_POST['fcm_id'])) {
        $response['error'] = true;
        $response['message'] = "Please pass the fcm_id!";
        print_r(json_encode($response));
        return false;
        exit();
    }

    $id = $db->escapeString(trim($fn->xss_clean($_POST['id'])));
    if (isset($_POST['fcm_id']) && !empty($_POST['fcm_id'])) {
        $fcm_id = $db->escapeString($fn->xss_clean($_POST['fcm_id']));
        $sql1 = "update admin set `fcm_id` ='$fcm_id' where id = '" . $id . "'";
        if ($db->sql($sql1)) {
            $response['error'] = false;
            $response['message'] = "Admin fcm_id Updeted successfully.";
        } else {
            $response['error'] = true;
            $response['message'] = "Can not update fcm_id of admin.";
        }
        print_r(json_encode($response));
    }
}

/* 
---------------------------------------------------------------------------------------------------------
*/

if (isset($_POST['get_privacy_and_terms']) && !empty($_POST['get_privacy_and_terms'])) {
    /* 
   8. get_privacy_and_terms
	   accesskey:90336
	   get_privacy_and_terms:1
   */
    $sql = "select value from `settings` where variable='manager_app_privacy_policy'";
    $db->sql($sql);
    $res = $db->getResult();
    $sql1 = "select value from `settings` where variable='manager_app_terms_conditions'";
    $db->sql($sql1);
    $res1 = $db->getResult();
    if (!empty($res) && !empty($res1)) {
        $response['error'] = false;
        $response['message'] = "Privacy & Policy fetched!";
        $response['privacy_policy'] = $res[0]['value'];
        $response['terms_conditions'] = $res1[0]['value'];
    } else {
        $response['error'] = true;
        $response['message'] = "Something went wrong!";
    }
    print_r(json_encode($response));
}

/* 
---------------------------------------------------------------------------------------------------------
*/

if (isset($_POST['update_order_status']) && !empty($_POST['update_order_status'])) {
    /* 
   9.update_order_status
	   	accesskey:90336
		update_order_status:1
		id:169
		status:cancelled
		delivery_boy_id:20{optional}
   */

    if (empty($_POST['id']) || empty($_POST['status'])) {
        $response['error'] = true;
        $response['message'] = "Please pass all mandatory fields!";
        print_r(json_encode($response));
        return false;
        exit();
    }

    $id = $db->escapeString(trim($fn->xss_clean($_POST['id'])));
    $postStatus = $db->escapeString($fn->xss_clean($_POST['status']));
    $delivery_boy_id = 0;
    if (isset($_POST['delivery_boy_id']) && !empty($fn->xss_clean($_POST['delivery_boy_id']))) {
        $delivery_boy_id = $db->escapeString($fn->xss_clean($_POST['delivery_boy_id']));
    }
    $response = $fn->update_order_status($id, $postStatus, $delivery_boy_id);
    print_r($response);
}

/* 
---------------------------------------------------------------------------------------------------------
*/

if (isset($_POST['get_permissions']) && !empty($_POST['get_permissions'])) {
    /* 
   10.get_permissions
	   accesskey:90336
	   id:1
       get_permissions:1
       type: orders/payment/customers/featured/products_order/products/subcategories/categories/home_sliders/faqs/reports/locations/settings/transactions/notifications/return_requests/delivery_boys/promo_codes/new_offers   // {optional}
   */

    if (empty(trim($_POST['id']))) {
        $response['error'] = true;
        $response['message'] = "Admin id should be filled!";
        print_r(json_encode($response));
        return false;
        exit();
    }

    $id = $db->escapeString(trim($fn->xss_clean($_POST['id'])));
    $type = (isset($_POST['type']) && !empty($_POST['type'])) ? $db->escapeString(trim($fn->xss_clean($_POST['type']))) : "";
    $sql = "SELECT `permissions` FROM `admin` WHERE id = $id ";
    $db->sql($sql);
    $res = $db->getResult();
    $num = $db->numRows($res);
    $rows = $tempRow = $per = $permissions = $permission = array();
    if ($num == 1) {
        $permissions = json_decode($res[0]['permissions'], true);
        if ($type == "products_order" || $type == "orders" || $type == "payment" || $type == "home_sliders" || $type == "categories" || $type == "subcategories" || $type == "products" || $type == "featured" || $type == "customers" || $type == "payment" || $type == "new_offers" || $type == "promo_codes" || $type == "delivery_boys" || $type == "return_requests" || $type == "notifications" || $type == "transactions" || $type == "settings" || $type == "locations" || $type == "reports" || $type == "faqs") {

            $per = $permissions[$type];

            if (!array_key_exists('create', $per)) {
                $per['create'] = "0";
            }
            if (!array_key_exists('read', $per)) {
                $per['read'] = "0";
            }
            if (!array_key_exists('update', $per)) {
                $per['update'] = "0";
            }
            if (!array_key_exists('delete', $per)) {
                $per['delete'] = "0";
            }
            $permission = $per;
            $response['error'] = false;
            $response['message'] = "Permissions fetched successfully";
            $response['data'][$type] = $permission;
        } else if ($type == "") {
            foreach ($permissions as $per) {
                if (!array_key_exists('create', $per)) {
                    $per['create'] = "0";
                }
                if (!array_key_exists('read', $per)) {
                    $per['read'] = "0";
                }
                if (!array_key_exists('update', $per)) {
                    $per['update'] = "0";
                }
                if (!array_key_exists('delete', $per)) {
                    $per['delete'] = "0";
                }
                $permission[] = $per;
            }
            $asd1 = array_combine(array_keys($permissions), $permission);
            $response['error'] = false;
            $response['message'] = "Permissions fetched successfully";
            $response['data'] = $asd1;
        }
    } else {
        $response['error'] = true;
        $response['message'] = "Permissions can not fetched!";
    }
    print_r(json_encode($response));
}

/* 
---------------------------------------------------------------------------------------------------------
*/

if (isset($_POST['update_order_item_status']) && !empty($_POST['update_order_item_status'])) {
    /* 
   11.update_order_item_status
	   	accesskey:90336
		update_order_item_status:1
		order_item_id:7166
		status:cancelled
		order_id:3445
   */

    if (empty($_POST['order_item_id']) || empty($_POST['status']) || empty($_POST['order_id'])) {
        $response['error'] = true;
        $response['message'] = "Please pass all mandatory fields!";
        print_r(json_encode($response));
        return false;
        exit();
    }

    $order_item_id = $db->escapeString(trim($fn->xss_clean($_POST['order_item_id'])));
    $order_id = $db->escapeString(trim($fn->xss_clean($_POST['order_id'])));
    $postStatus = $db->escapeString($fn->xss_clean($_POST['status']));

    $response = $fn->update_order_item_status($order_item_id, $order_id, $postStatus);
    print_r($response);
}

/* 
---------------------------------------------------------------------------------------------------------
*/

if (isset($_POST['add_delivery_boy']) && !empty($_POST['add_delivery_boy'])) {
    /* 
   12.add_delivery_boy
	   	accesskey:90336
		add_delivery_boy:1		
	   	name:delivery_boy
	   	mobile:9963258652
	   	address:time square
	   	bonus:10
	   	dob:2020-09-12
	   	bank_name:SBI
	   	other_payment_info:description {optional}
	   	account_number:12547896523652
	   	account_name:DEMO
	   	ifsc_code:254SBIfbfg
		password:asd124
		driving_license:image_file
		national_identity_card :image_file 

   */

    if (empty($_POST['name']) || empty($_POST['mobile']) || empty($_POST['address']) || empty($_POST['bonus']) || empty($_POST['dob']) || empty($_POST['bank_name']) ||  empty($_POST['account_number']) || empty($_POST['account_name']) || empty($_POST['ifsc_code']) || empty($_POST['password'])) {
        $response['error'] = true;
        $response['message'] = "Please pass all fields!";
        print_r(json_encode($response));
        return false;
        exit();
    }

    $name = $db->escapeString(trim($fn->xss_clean($_POST['name'])));
    $mobile = $db->escapeString(trim($fn->xss_clean($_POST['mobile'])));
    $address = $db->escapeString(trim($fn->xss_clean($_POST['address'])));
    $bonus = $db->escapeString(trim($fn->xss_clean($_POST['bonus'])));
    $dob = $db->escapeString(trim($fn->xss_clean($_POST['dob'])));
    $bank_name = $db->escapeString(trim($fn->xss_clean($_POST['bank_name'])));
    $other_payment_info = (isset($_POST['other_payment_info']) && !empty(trim($_POST['other_payment_info']))) ? $db->escapeString(trim($_POST['other_payment_info'])) : '';
    $account_number = $db->escapeString(trim($fn->xss_clean($_POST['account_number'])));
    $account_name = $db->escapeString(trim($fn->xss_clean($_POST['account_name'])));
    $ifsc_code = $db->escapeString(trim($fn->xss_clean($_POST['ifsc_code'])));
    $password = $db->escapeString(trim($fn->xss_clean($_POST['password'])));

    $sql = 'SELECT id FROM delivery_boys WHERE mobile=' . $mobile;
    $db->sql($sql);
    $res = $db->getResult();
    $count = $db->numRows($res);

    if ($count > 0) {
        $response['error'] = true;
        $response['message'] = "Mobile Number Already Exists!";
        print_r(json_encode($response));
        return false;
        exit();
    }

    $target_path = '../../upload/delivery-boy/';
    if ($_FILES['driving_license']['error'] == 0 && $_FILES['driving_license']['size'] > 0) {
        if (!is_dir($target_path)) {
            mkdir($target_path, 0777, true);
        }
        $extension = pathinfo($_FILES["driving_license"]["name"])['extension'];

        $result = $fn->validate_image($_FILES["driving_license"]);
        if ($result) {
            $response['error'] = true;
            $response['message'] = "Driving License image type must jpg, jpeg, gif, or png!";
            print_r(json_encode($response));
            return false;
            exit();
        }

        // $mimetype = mime_content_type($_FILES["driving_license"]["tmp_name"]);
        // if (!in_array($mimetype, array('image/jpg', 'image/jpeg', 'image/gif', 'image/png'))) {
        //     $response['error'] = true;
        //     $response['message'] = "Driving License image type must jpg, jpeg, gif, or png!";
        //     print_r(json_encode($response));
        //     return false;
        //     exit();
        // }
        $dr_filename = microtime(true) . '.' . strtolower($extension);
        $dr_full_path = $target_path . "" . $dr_filename;
        if (!move_uploaded_file($_FILES["driving_license"]["tmp_name"], $dr_full_path)) {
            $response['error'] = true;
            $response['message'] = "Invalid directory to load image!";
            print_r(json_encode($response));
            return false;
            exit();
        }
    }
    if ($_FILES['national_identity_card']['error'] == 0 && $_FILES['national_identity_card']['size'] > 0) {
        if (!is_dir($target_path)) {
            mkdir($target_path, 0777, true);
        }
        $extension = pathinfo($_FILES["national_identity_card"]["name"])['extension'];

        $result = $fn->validate_image($_FILES["national_identity_card"]);
        if ($result) {
            $response['error'] = true;
            $response['message'] = "National Identity Card image type must jpg, jpeg, gif, or png!";
            print_r(json_encode($response));
            return false;
            exit();
        }

        // $mimetype = mime_content_type($_FILES["national_identity_card"]["tmp_name"]);
        // if (!in_array($mimetype, array('image/jpg', 'image/jpeg', 'image/gif', 'image/png'))) {
        //     $response['error'] = true;
        //     $response['message'] = "National Identity Card image type must jpg, jpeg, gif, or png!";
        //     print_r(json_encode($response));
        //     return false;
        //     exit();
        // }
        $nic_filename = microtime(true) . '.' . strtolower($extension);
        $nic_full_path = $target_path . "" . $nic_filename;
        if (!move_uploaded_file($_FILES["national_identity_card"]["tmp_name"], $nic_full_path)) {
            $response['error'] = true;
            $response['message'] = "Invalid directory to load image!";
            print_r(json_encode($response));
            return false;
            exit();
        }
    }

    $sql = "INSERT INTO delivery_boys (`name`,`mobile`,`password`,`address`,`bonus`, `driving_license`, `national_identity_card`, `dob`, `bank_account_number`, `bank_name`, `account_name`, `ifsc_code`,`other_payment_information`) VALUES ('$name', '$mobile', '$password', '$address','$bonus','$dr_filename', '$nic_filename', '$dob','$account_number','$bank_name','$account_name','$ifsc_code','$other_payment_info')";
    if ($db->sql($sql)) {
        $response['error'] = true;
        $response['message'] = "Delivery Boy Added Successfully!";
    } else {
        $response['error'] = true;
        $response['message'] = "Some Error Occrred! please try again.";
    }
    print_r(json_encode($response));
}

/* 
---------------------------------------------------------------------------------------------------------
*/

if (isset($_POST['update_delivery_boy']) && !empty($_POST['update_delivery_boy'])) {
    /* 
   13.update_delivery_boy
	   	accesskey:90336
		update_delivery_boy:1
		id:12
	   	name:delivery_boy
	   	mobile:9963258652
	   	address:time square
	   	bonus:10
	   	dob:2020-09-12
	   	bank_name:SBI
	   	other_payment_info:description 
	   	account_number:12547896523652
	   	account_name:DEMO
	   	ifsc_code:254SBIfbfg
		password:asd124
		status:1
		driving_license:image_file ( image type must jpg, jpeg, gif, or png!)      // {optional}
		national_identity_card :image_file ( image type must jpg, jpeg, gif, or png!) // {optional}

   */

    if (empty($_POST['name']) || empty($_POST['mobile']) || empty($_POST['address']) || empty($_POST['bonus']) || empty($_POST['dob']) || empty($_POST['bank_name']) ||  empty($_POST['account_number']) || empty($_POST['account_name']) || empty($_POST['ifsc_code']) || empty($_POST['password'])) {
        $response['error'] = true;
        $response['message'] = "Please pass all fields!";
        print_r(json_encode($response));
        return false;
        exit();
    }
    $id = $db->escapeString($fn->xss_clean($_POST['id']));
    $sql = "SELECT id FROM `delivery_boys` where id=$id";
    $db->sql($sql);
    $res1 = $db->getResult();
    if (!empty($res1)) {
        if ($id == 104) {
            $response['error'] = true;
            $response['message'] = "Sorry you can not update this delivery boy.";
            print_r(json_encode($response));
            return false;
            exit();
        }
        $name = $db->escapeString($fn->xss_clean($_POST['name']));
        $password = !empty($_POST['password']) ? $db->escapeString($fn->xss_clean($_POST['password'])) : '';
        $other_payment_info = !empty($_POST['other_payment_info']) ? $db->escapeString($fn->xss_clean($_POST['other_payment_info'])) : '';
        $address = $db->escapeString($fn->xss_clean($_POST['address']));
        $bonus = $db->escapeString($fn->xss_clean($_POST['bonus']));
        $status = $db->escapeString($fn->xss_clean($_POST['status']));
        $dob = $db->escapeString($fn->xss_clean($_POST['dob']));
        $bank_name = $db->escapeString($fn->xss_clean($_POST['bank_name']));
        $account_number = $db->escapeString($fn->xss_clean($_POST['account_number']));
        $account_name = $db->escapeString($fn->xss_clean($_POST['account_name']));
        $ifsc_code = $db->escapeString($fn->xss_clean($_POST['ifsc_code']));
        $password = !empty($password) ? md5($password) : '';
        $target_path = '../../upload/delivery-boy/';

        $sql = "SELECT id,driving_license,national_identity_card,other_payment_information FROM `delivery_boys` where id=$id";
        $db->sql($sql);
        $res = $db->getResult();
        if ($other_payment_info == '') {
            $other_payment_info = $res[0]['other_payment_information'];
        }

        if (!is_dir($target_path)) {
            mkdir($target_path, 0777, true);
        }
        if (isset($_FILES['driving_license']) && $_FILES['driving_license']['size'] != 0 && $_FILES['driving_license']['error'] == 0 && !empty($_FILES['driving_license'])) {
            if (!empty($res[0]['driving_license'])) {
                $old_image = $res[0]['driving_license'];
                if (!empty($old_image)) {
                    unlink($target_path . $old_image);
                }
            }
            $extension = pathinfo($_FILES["driving_license"]["name"])['extension'];

            $result = $fn->validate_image($_FILES["driving_license"]);
            if ($result) {
                $response['error'] = true;
                $response['message'] = "Driving License image type must jpg, jpeg, gif, or png!";
                print_r(json_encode($response));
                return false;
                exit();
            }
            // $mimetype = mime_content_type($_FILES["driving_license"]["tmp_name"]);
            // if (!in_array($mimetype, array('image/jpg', 'image/jpeg', 'image/gif', 'image/png'))) {
            //     $response['error'] = true;
            //     $response['message'] = "Driving License image type must jpg, jpeg, gif, or png!";
            //     print_r(json_encode($response));
            //     return false;
            //     exit();
            // }
            $dr_filename = microtime(true) . '.' . strtolower($extension);
            $dr_full_path = $target_path . "" . $dr_filename;
            if (!move_uploaded_file($_FILES["driving_license"]["tmp_name"], $dr_full_path)) {
                $response['error'] = true;
                $response['message'] = "Can not upload driving license.";
                print_r(json_encode($response));
                return false;
                exit();
            }
            $sql = "UPDATE delivery_boys SET `driving_license`='" . $dr_filename . "' WHERE `id`=" . $id;
            $db->sql($sql);
        }
        if (isset($_FILES['national_identity_card']) && $_FILES['national_identity_card']['size'] != 0 && $_FILES['national_identity_card']['error'] == 0 && !empty($_FILES['national_identity_card'])) {
            if (!empty($res[0]['national_identity_card'])) {
                $old_image = $res[0]['national_identity_card'];
                if (!empty($old_image)) {
                    unlink($target_path . $old_image);
                }
            }
            $extension = pathinfo($_FILES["national_identity_card"]["name"])['extension'];
            // $mimetype = mime_content_type($_FILES["national_identity_card"]["tmp_name"]);
            // if (!in_array($mimetype, array('image/jpg', 'image/jpeg', 'image/gif', 'image/png'))) {
            //     $response['error'] = true;
            //     $response['message'] = "National Identity Card image type must jpg, jpeg, gif, or png!";
            //     print_r(json_encode($response));
            //     return false;
            //     exit();
            // }
            $result = $fn->validate_image($_FILES["national_identity_card"]);
            if ($result) {
                $response['error'] = true;
                $response['message'] = "National Identity Card image type must jpg, jpeg, gif, or png!";
                print_r(json_encode($response));
                return false;
                exit();
            }
            $nic_filename = microtime(true) . '.' . strtolower($extension);
            $nic_full_path = $target_path . "" . $nic_filename;
            if (!move_uploaded_file($_FILES["national_identity_card"]["tmp_name"], $nic_full_path)) {
                $response['error'] = true;
                $response['message'] = "Can not upload national identity card";
                print_r(json_encode($response));
                return false;
                exit();
            }
            $sql = "UPDATE delivery_boys SET `national_identity_card`='" . $nic_filename . "' WHERE `id`=" . $id;
            $db->sql($sql);
        }

        if (!empty($password)) {
            $sql = "Update delivery_boys set `name`='" . $name . "',password='" . $password . "',`address`='" . $address . "',`bonus`='" . $bonus . "',`status`='" . $status . "',`dob`='$dob',`bank_account_number`='$account_number',`bank_name`='$bank_name',`account_name`='$account_name',`ifsc_code`='$ifsc_code',`other_payment_information`='$other_payment_info' where `id`=" . $id;
        } else {
            $sql = "Update delivery_boys set `name`='" . $name . "',`address`='" . $address . "',`bonus`='" . $bonus . "',`status`='" . $status . "',`dob`='$dob',`bank_account_number`='$account_number',`bank_name`='$bank_name',`account_name`='$account_name',`ifsc_code`='$ifsc_code',`other_payment_information`='$other_payment_info'  where `id`=" . $id;
        }
        if ($db->sql($sql)) {
            $response['error'] = false;
            $response['message'] = "Information Updated Successfully.";
        } else {
            $response['error'] = true;
            $response['message'] = "Some Error Occurred! Please Try Again.";
        }
    } else {
        $response['error'] = true;
        $response['message'] = "Delivery boy does not exist";
    }
    print_r(json_encode($response));
}

/* 
---------------------------------------------------------------------------------------------------------
*/

if (isset($_POST['delete_delivery_boy']) && !empty($_POST['delete_delivery_boy'])) {
    /* 
   14.delete_delivery_boy
	   	accesskey:90336
		delete_delivery_boy:1		
	   	id:302
   */

    if (empty($_POST['id'])) {
        $response['error'] = true;
        $response['message'] = "delivery boy id is missing!";
        print_r(json_encode($response));
        return false;
        exit();
    }

    $id = $db->escapeString($fn->xss_clean($_POST['id']));
    $sql = "SELECT id FROM `delivery_boys` where id=$id";
    $db->sql($sql);
    $res1 = $db->getResult();
    if (!empty($res1)) {
        $target_path = '../../upload/delivery-boy/';

        if ($id == 104) {
            $response['error'] = true;
            $response['message'] = "Sorry you can not delete this delivery boy.";
            print_r(json_encode($response));
            return false;
            exit();
        }
        $sql1 = "SELECT id,driving_license,national_identity_card,other_payment_information FROM `delivery_boys` where id=$id";
        $db->sql($sql1);
        $res1 = $db->getResult();
        $sql = "DELETE FROM `delivery_boys` WHERE id=" . $id;
        if ($db->sql($sql)) {
            $sql = "DELETE FROM `fund_transfers` WHERE delivery_boy_id=" . $id;
            $db->sql($sql);
            $sql = "DELETE FROM `withdrawal_requests` WHERE `type_id`=" . $id . " AND `type`='delivery_boy'";
            $db->sql($sql);

            if (!empty($res1[0]['driving_license']) || $res1[0]['driving_license'] != '') {
                $old_image = $res1[0]['driving_license'];
                if (!empty($old_image)) {
                    unlink($target_path . $old_image);
                }
            }
            if (!empty($res1[0]['national_identity_card']) || $res1[0]['national_identity_card'] != '') {
                $old_image = $res1[0]['national_identity_card'];
                if (!empty($old_image)) {
                    unlink($target_path . $old_image);
                }
            }
            $response['error'] = false;
            $response['message'] = "Delivery boy deleted successfully";
        } else {
            $response['error'] = true;
            $response['message'] = "Delivery boy not deleted.";
        }
    } else {
        $response['error'] = true;
        $response['message'] = "Delivery boy does not exist.";
    }
    print_r(json_encode($response));
}

/* 
---------------------------------------------------------------------------------------------------------
*/

if (isset($_POST['delivery_boy_fund_transfers']) && !empty($_POST['delivery_boy_fund_transfers'])) {
    /* 
   15.delivery_boy_fund_transfers
	   	accesskey:90336
	   	delivery_boy_fund_transfers:1
		delivery_boy_id:104   {optional}
	   	limit:10    {optional}
		offset:0     {optional}
		sort:id       {optional}
		order:ASC/DESC   {optional}
		search:value  {optional}
   */

    $where = '';
    $offset = (isset($_POST['offset']) && !empty(trim($_POST['offset'])) && is_numeric($_POST['offset'])) ? $db->escapeString(trim($fn->xss_clean($_POST['offset']))) : 0;
    $limit = (isset($_POST['limit']) && !empty(trim($_POST['limit'])) && is_numeric($_POST['limit'])) ? $db->escapeString(trim($fn->xss_clean($_POST['limit']))) : 10;

    $sort = (isset($_POST['sort']) && !empty(trim($_POST['sort']))) ? $db->escapeString(trim($fn->xss_clean($_POST['sort']))) : 'id';
    $order = (isset($_POST['order']) && !empty(trim($_POST['order']))) ? $db->escapeString(trim($fn->xss_clean($_POST['order']))) : 'DESC';

    if (isset($_POST['delivery_boy_id']) && !empty($_POST['delivery_boy_id'])) {
        $delivery_boy_id = $db->escapeString($fn->xss_clean($_POST['delivery_boy_id']));
        $where .= " where f.delivery_boy_id = $delivery_boy_id";
    }

    if (isset($_POST['search']) && !empty($_POST['search'])) {
        $search = $db->escapeString($fn->xss_clean($_POST['search']));
        if (isset($_POST['delivery_boy_id']) && !empty($_POST['delivery_boy_id'])) {
            $where .= " and f.`id` like '%" . $search . "%' OR `name` like '%" . $search . "%' OR `mobile` like '%" . $search . "%' OR `address` like '%" . $search . "%' OR `message` like '%" . $search . "%' OR f.`date_created` like '%" . $search . "%'";
        } else {
            $where .= " Where f.`id` like '%" . $search . "%' OR `name` like '%" . $search . "%' OR `mobile` like '%" . $search . "%' OR `address` like '%" . $search . "%' OR `message` like '%" . $search . "%' OR f.`date_created` like '%" . $search . "%'";
        }
    }
    $sql = "SELECT COUNT(f.`id`) as total FROM `fund_transfers` f LEFT JOIN `delivery_boys` d ON f.delivery_boy_id=d.id" . $where;
    $db->sql($sql);
    $res = $db->getResult();
    if (!empty($res)) {
        foreach ($res as $row)
            $total = $row['total'];

        $sql = "SELECT f.*,d.name,d.mobile,d.address FROM `fund_transfers` f LEFT JOIN `delivery_boys` d ON f.delivery_boy_id=d.id  $where ORDER BY $sort $order LIMIT $offset,$limit";
        $db->sql($sql);
        $res = $db->getResult();
        $rows = array();
        $tempRow = array();

        foreach ($res as $row) {
            $tempRow['id'] = $row['id'];
            $tempRow['name'] = $row['name'];
            $tempRow['mobile'] = $row['mobile'];
            $tempRow['address'] = $row['address'];
            $tempRow['delivery_boy_id'] = $row['delivery_boy_id'];
            $tempRow['opening_balance'] = $row['opening_balance'];
            $tempRow['closing_balance'] = $row['closing_balance'];
            $tempRow['amount'] = $row['amount'];
            $tempRow['type'] = $row['type'];
            $tempRow['status'] = $row['status'];
            $tempRow['message'] = $row['message'];
            $tempRow['date_created'] = $row['date_created'];
            $rows[] = $tempRow;
        }
        $response['error'] = false;
        $response['message'] = "Fund transfers fatched successfully.";
        $response['total'] = $total;
        $response['data'] = $rows;
    } else {
        $response['error'] = true;
        $response['message'] = "Something went wrong, please try again leter.";
    }
    print_r(json_encode($response));
}

/* 
---------------------------------------------------------------------------------------------------------
*/

if (isset($_POST['delivery_boy_transfer_fund']) && !empty($_POST['delivery_boy_transfer_fund'])) {
    /* 
   16.delivery_boy_transfer_fund
	   	accesskey:90336
		delivery_boy_transfer_fund:1		
		delivery_boy_id:302
		delivery_boy_balance:20
		amount:20
		message: message from admin {optional}
   */

    if (empty($_POST['delivery_boy_id']) || $_POST['delivery_boy_balance'] == '' || $_POST['amount'] == '') {
        $response['error'] = true;
        $response['message'] = "some parameters are missing!";
        print_r(json_encode($response));
        return false;
        exit();
    }

    $id = $db->escapeString($fn->xss_clean($_POST['delivery_boy_id']));
    $sql = "SELECT id FROM `delivery_boys` where id=$id";
    $db->sql($sql);
    $res1 = $db->getResult();
    if (!empty($res1)) {
        $balance = $db->escapeString($fn->xss_clean($_POST['delivery_boy_balance']));
        if ($balance == 0) {
            $response['error'] = true;
            $response['message'] = "Balance must be greater then zero.";
            print_r(json_encode($response));
            return false;
            exit();
        }
        if (!is_numeric(trim($_POST['amount']))) {
            $response['error'] = true;
            $response['message'] = "Amount must be number.";
            print_r(json_encode($response));
            return false;
            exit();
        }
        $amount = $db->escapeString($fn->xss_clean($_POST['amount']));
        if ($amount > $balance) {
            $response['error'] = true;
            $response['message'] = "Amount must be less then or equal to balance.";
            print_r(json_encode($response));
            return false;
            exit();
        }
        $message = (!empty($_POST['message'])) ? $db->escapeString($fn->xss_clean($_POST['message'])) : 'Fund Transferred By Admin';
        $bal = $balance - $amount;
        $sql = "Update delivery_boys set `balance`='" . $bal . "' where `id`=" . $id;
        $db->sql($sql);
        $sql = "INSERT INTO `fund_transfers` (`delivery_boy_id`,`amount`,`opening_balance`,`closing_balance`,`status`,`message`) VALUES ('" . $id . "','" . $amount . "','" . $balance . "','" . $bal . "','SUCCESS','" . $message . "')";
        if ($db->sql($sql)) {
            $response['error'] = false;
            $response['message'] = "Amount transferred successfully.";
        } else {
            $response['error'] = true;
            $response['message'] = "Amount does not transferred, somthing went wrong.";
        }
    } else {
        $response['error'] = true;
        $response['message'] = "Delivery boy does not exist";
    }
    print_r(json_encode($response));
}

/* 
---------------------------------------------------------------------------------------------------------
*/

if (isset($_POST['get_all_data']) && !empty($_POST['get_all_data'])) {
    /* 
   17.get_all_data
	   	accesskey:90336
		get_all_data:1
   */
    //categories
    $sql = "SELECT * FROM category ORDER BY id DESC ";
    $db->sql($sql);
    $res_categories = $db->getResult();

    for ($i = 0; $i < count($res_categories); $i++) {
        $res_categories[$i]['image'] = (!empty($res_categories[$i]['image'])) ? DOMAIN_URL . '' . $res_categories[$i]['image'] : '';
    }
    // slider images
    $sql = 'SELECT * from slider order by id DESC';
    $db->sql($sql);
    $res_slider_image = $db->getResult();
    $temp = $slider_images = array();
    if (!empty($res_slider_image)) {
        $response['error'] = false;
        foreach ($res_slider_image as $row) {
            $name = "";
            if ($row['type'] == 'category') {
                $sql = 'select `name` from category where id = ' . $row['type_id'] . ' order by id desc';
                $db->sql($sql);
                $result1 = $db->getResult();
                $name = (!empty($result1[0]['name'])) ? $result1[0]['name'] : "";
            }
            if ($row['type'] == 'product') {
                $sql = 'select `name` from products where id = ' . $row['type_id'] . ' order by id desc';
                $db->sql($sql);
                $result1 = $db->getResult();
                $name = (!empty($result1[0]['name'])) ? $result1[0]['name'] : "";
            }

            $temp['type'] = $row['type'];
            $temp['type_id'] = $row['type_id'];
            $temp['name'] = $name;
            $temp['image'] = DOMAIN_URL . $row['image'];
            $slider_images[] = $temp;
        }
    }

    // featured sections
    $sql = 'select * from `sections` order by id desc';
    $db->sql($sql);
    $result = $db->getResult();
    $response = $product_ids = $section = $variations = $featured_sections = array();
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
                $row['tax_percentage'] = "";
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
                if ($variants[$k]['stock'] <= 0) {
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
            $row['image'] = DOMAIN_URL . $row['image'];
            $product[$i] = $row;
            $product[$i]['variants'] = $variants;
            $i++;
        }
        $section['products'] = $product;
        $featured_sections[] = $section;
        unset($section['products']);
    }
    // offer images
    $sql = 'SELECT * from offers order by id desc';
    $db->sql($sql);
    $res_offer_images = $db->getResult();
    $response = $temp = $offer_images = array();
    foreach ($res_offer_images as $row) {
        $temp['image'] = DOMAIN_URL . $row['image'];
        $offer_images[] = $temp;
    }

    $response['error'] = false;
    $response['message'] = "Data fetched successfully";
    $response['categories'] = $res_categories;
    $response['slider_images'] = $slider_images;
    $response['sections'] = $featured_sections;
    $response['offer_images'] = $offer_images;
    print_r(json_encode($response));
}
if (isset($_POST['get_categories']) && !empty($_POST['get_categories'])) {
    /* 18.get_categories
        accesskey:90336
        get_categories:1
        category_id:28   {optional}
        limit:10  {optional}
        offset:0    {optional}
        sort:id      {optional}
        order:ASC/DESC {optional}
    */

    $where = '';
    $offset = (isset($_POST['offset']) && !empty(trim($_POST['offset'])) && is_numeric($_POST['offset'])) ? $db->escapeString(trim($fn->xss_clean($_POST['offset']))) : 0;
    $limit = (isset($_POST['limit']) && !empty(trim($_POST['limit'])) && is_numeric($_POST['limit'])) ? $db->escapeString(trim($fn->xss_clean($_POST['limit']))) : 10;
    $sort = (isset($_POST['sort']) && !empty(trim($_POST['sort']))) ? $db->escapeString(trim($fn->xss_clean($_POST['sort']))) : 'id';
    $order = (isset($_POST['order']) && !empty(trim($_POST['order']))) ? $db->escapeString(trim($fn->xss_clean($_POST['order']))) : 'DESC';

    $category_id = (isset($_POST['category_id']) && !empty(trim($_POST['category_id']))) ? $db->escapeString($fn->xss_clean($_POST['category_id'])) : '';
    if (empty($category_id) && $category_id == '') {
        $sql_query = "SELECT * FROM category ORDER BY id ASC";
    } else {
        // get all category data from category table
        $sql_query = "SELECT * FROM category WHERE id = '" . $category_id . "'";
    }
    $db->sql($sql_query);
    $res = $db->getResult();
    if (!empty($res)) {
        for ($i = 0; $i < count($res); $i++) {
            $res[$i]['image'] = (!empty($res[$i]['image'])) ? DOMAIN_URL . '' . $res[$i]['image'] : '';
            $res[$i]['web_image'] = (!empty($res[$i]['web_image'])) ? DOMAIN_URL . '' . $res[$i]['web_image'] : '';
        }
        $tmp = [];
        foreach ($res as $r) {
            $r['childs'] = [];

            $db->sql("SELECT * FROM subcategory WHERE category_id = '" . $r['id'] . "' ORDER BY id DESC");
            $childs = $db->getResult();
            if (!empty($childs)) {
                for ($i = 0; $i < count($childs); $i++) {
                    $childs[$i]['image'] = (!empty($childs[$i]['image'])) ? DOMAIN_URL . '' . $childs[$i]['image'] : '';
                    $r['childs'][$childs[$i]['slug']] = (array)$childs[$i];
                }
            }
            $tmp[] = $r;
        }
        $res = $tmp;
        $response['error'] = "false";
        $response['data'] = $res;
    } else {
        $response['error'] = "true";
        $response['message'] = "No data found!";
    }
    print_r(json_encode($response));
}

if (isset($_POST['get_subcategories']) && !empty($_POST['get_subcategories'])) {
    /* 19.get_categories
        accesskey:90336
        get_subcategories:1
        category_id:28   {optional}
        limit:10  {optional}
        offset:0    {optional}
        sort:id      {optional}
        order:ASC/DESC {optional}
    */
    $where = '';
    $offset = (isset($_POST['offset']) && !empty(trim($_POST['offset'])) && is_numeric($_POST['offset'])) ? $db->escapeString(trim($fn->xss_clean($_POST['offset']))) : 0;
    $limit = (isset($_POST['limit']) && !empty(trim($_POST['limit'])) && is_numeric($_POST['limit'])) ? $db->escapeString(trim($fn->xss_clean($_POST['limit']))) : 10;
    $sort = (isset($_POST['sort']) && !empty(trim($_POST['sort']))) ? $db->escapeString(trim($fn->xss_clean($_POST['sort']))) : 'id';
    $order = (isset($_POST['order']) && !empty(trim($_POST['order']))) ? $db->escapeString(trim($fn->xss_clean($_POST['order']))) : 'DESC';

    $category_id = (isset($_POST['category_id']) && !empty(trim($_POST['category_id']))) ? $db->escapeString($fn->xss_clean($_POST['category_id'])) : '';
    if (empty($category_id) && $category_id == '') {
        $sql_query = "SELECT * FROM subcategory ORDER BY id ASC";
    } else {
        // get all category data from category table
        $sql_query = "SELECT * FROM subcategory WHERE category_id = '" . $category_id . "'";
    }
    $db->sql($sql_query);
    $res = $db->getResult();
    // print_r($res);
    if (!empty($res)) {
        for ($i = 0; $i < count($res); $i++) {
            $res[$i]['image'] = (!empty($res[$i]['image'])) ? DOMAIN_URL . '' . $res[$i]['image'] : '';
            $res[$i]['web_image'] = (!empty($res[$i]['web_image'])) ? DOMAIN_URL . '' . $res[$i]['web_image'] : '';
        }
        $tmp = [];
        foreach ($res as $r) {
            $r['childs'] = [];

            $db->sql("SELECT * FROM subcategory WHERE category_id = '" . $r['category_id'] . "' ORDER BY id DESC");
            $childs = $db->getResult();
            if (!empty($childs)) {
                for ($i = 0; $i < count($childs); $i++) {
                    $childs[$i]['image'] = (!empty($childs[$i]['image'])) ? DOMAIN_URL . '' . $childs[$i]['image'] : '';
                    $r['childs'][$childs[$i]['slug']] = (array)$childs[$i];
                }
            }
            $tmp[] = $r;
        }
        $res = $tmp;
        $response['error'] = "false";
        $response['data'] = $res;
    } else {
        $response['error'] = "true";
        $response['message'] = "No data found!";
    }
    print_r(json_encode($response));
}
