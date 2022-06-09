<?php
header('Access-Control-Allow-Origin: *');
include_once('send-email.php');
include_once('send-sms.php');
include_once('../includes/crud.php');
include_once('../includes/custom-functions.php');
include_once('../includes/variables.php');
include_once('verify-token.php');
$db = new Database();
$db->connect();
$db->sql("SET NAMES utf8");
$function = new custom_functions();
$settings = $function->get_settings('system_timezone', true);
$app_name = $settings['app_name'];
$support_email = $settings['support_email'];
$config = $function->get_configurations();
if (isset($config['system_timezone']) && isset($config['system_timezone_gmt'])) {
    date_default_timezone_set($config['system_timezone']);
    $db->sql("SET `time_zone` = '" . $config['system_timezone_gmt'] . "'");
} else {
    date_default_timezone_set('Asia/Kolkata');
    $db->sql("SET `time_zone` = '+05:30'");
}
$generate_otp = $config['generate-otp'];
$response = array();
$cancel_order_from = "";
if (isset($_POST['ajaxCall']) && !empty($_POST['ajaxCall'])) {
    $accesskey = "90336";
    $cancel_order_from = "admin";
} else {
    if (isset($_POST['accesskey']) && !empty($_POST['accesskey'])) {
        $accesskey = $db->escapeString($function->xss_clean($_POST['accesskey']));
    } else {
        $response['error'] = true;
        $response['message'] = "accesskey required";
        print_r(json_encode($response));
        return false;
    }
}

if ($access_key != $accesskey) {
    $response['error'] = true;
    $response['message'] = "invalid accesskey";
    print_r(json_encode($response));
    return false;
}

if (isset($_POST['place_order']) && isset($_POST['user_id']) && !empty($_POST['product_variant_id'])) {
    if (!verify_token()) {
        return false;
    }
    $user_id = $db->escapeString($function->xss_clean($_POST['user_id']));
    $mobile = $db->escapeString($function->xss_clean($_POST['mobile']));
    $order_note = (isset($_POST['order_note']) && !empty($_POST['order_note'])) ? $db->escapeString($function->xss_clean($_POST['order_note'])) : "";
    $wallet_balance = (isset($_POST['wallet_balance']) && is_numeric($_POST['wallet_balance'])) ? $db->escapeString($function->xss_clean($_POST['wallet_balance'])) : 0;
    $wallet_used = (isset($_POST['wallet_used']) && $function->xss_clean($_POST['wallet_used']) == 'true') ? 'true' : 'false';
    $items = $function->xss_clean($_POST['product_variant_id']);
    $total = $db->escapeString($function->xss_clean($_POST['total']));
    $delivery_charge = $db->escapeString($function->xss_clean($_POST['delivery_charge']));
    $discount = (isset($_POST['discount'])) ? $db->escapeString($function->xss_clean($_POST['discount'])) : 0;
    $tax_percentage = (isset($_POST['tax_percentage']) && is_numeric($_POST['tax_percentage'])) ? $db->escapeString($function->xss_clean($_POST['tax_percentage'])) : 0;
    $tax_amount = (isset($_POST['tax_amount']) && is_numeric($_POST['tax_amount'])) ? $db->escapeString($function->xss_clean($_POST['tax_amount'])) : 0;
    $wallet_balance = (isset($_POST['wallet_balance']) && is_numeric($_POST['wallet_balance'])) ? $db->escapeString($function->xss_clean($_POST['wallet_balance'])) : 0;
    $final_total = $db->escapeString($function->xss_clean($_POST['final_total']));
    $payment_method = $db->escapeString($function->xss_clean($_POST['payment_method']));
    $address = $db->escapeString($function->xss_clean($_POST['address']));
    $delivery_time = (isset($_POST['delivery_time'])) ? $db->escapeString($function->xss_clean($_POST['delivery_time'])) : "";
    $latitude = $db->escapeString($function->xss_clean($_POST['latitude']));
    $longitude = $db->escapeString($function->xss_clean($_POST['longitude']));
    $promo_code = (isset($_POST['promo_code']) && !empty($_POST['promo_code'])) ? $db->escapeString($_POST['promo_code']) : "";
    $promo_discount = (isset($_POST['promo_discount']) && !empty($_POST['promo_discount'])) ? $db->escapeString($function->xss_clean($_POST['promo_discount'])) : 0;
    $active_status = (isset($_POST['status']) && !empty($_POST['status'])) ? $db->escapeString($function->xss_clean($_POST['status'])) : 'received';
    $order_from = (isset($_POST['order_from']) && !empty($_POST['order_from'])) ? $db->escapeString($function->xss_clean($_POST['order_from'])) : 0;


    $status[] = array($active_status, date("d-m-Y h:i:sa"));
    $item_details = $function->get_product_by_variant_id($items);
    $total_amount = $total + $delivery_charge + $tax_amount - $discount;
    $quantity = $function->xss_clean($_POST['quantity']);
    $quantity_arr = json_decode($quantity, 1);
    $otp_number = 0;
    if ($generate_otp == 1) {
        $otp_number = mt_rand(100000, 999999);
    } else {
        $otp_number = 0;
    }
    /* validate promo code if applied */
    if (isset($_POST['promo_code']) && $_POST['promo_code'] != '') {
        $promo_code = $db->escapeString($function->xss_clean($_POST['promo_code']));
        $response = $function->validate_promo_code($user_id, $promo_code, $total);
        if ($response['error'] == true) {
            echo json_encode($response);
            exit();
        }
    }
    if ($wallet_used == 'true') {
        $user_wallet_balance = $function->get_wallet_balance($user_id);
        if ($user_wallet_balance < $wallet_balance) {
            $response['error'] = "true";
            $response['message'] = "Insufficient wallet balance.";
            echo json_encode($response);
            return false;
        }
    }
    if ($total < $settings['min_order_amount']) {
        $response['error'] = "true";
        $response['message'] = "Minimum order amount is " . $settings['min_order_amount'] . ".";
        echo json_encode($response);
        return false;
    }
    $data = array(
        'user_id' => $user_id,
        'mobile' => $mobile,
        'order_note' => $order_note,
        'delivery_charge' => $delivery_charge,
        'wallet_balance' => ($wallet_used) ? $wallet_balance : 0,
        'total' => $total,
        'tax_percentage' => $tax_percentage,
        'tax_amount' => $tax_amount,
        'final_total' => $final_total,
        'payment_method' => $payment_method,
        'address' => $address,
        'delivery_time' => $delivery_time,
        'status' => $db->escapeString(json_encode($status)),
        'latitude' => $latitude,
        'longitude' => $longitude,
        'promo_code' => $promo_code,
        'promo_discount' => $promo_discount,
        'discount' => $discount,
        'active_status' => $active_status,
        'otp' => $otp_number,
        'order_from' => $order_from
    );
    $walletvalue = ($wallet_used) ? $wallet_balance : 0;
    $order_status = $db->escapeString(json_encode($status));
    $sql = "INSERT INTO `orders`(`user_id`,`otp`,  `mobile`,`order_note`, `total`, `delivery_charge`, `tax_amount`, `tax_percentage`, `wallet_balance`, `discount`, `promo_code`, `promo_discount`, `final_total`, `payment_method`, `address`, `latitude`, `longitude`, `delivery_time`, `status`, `active_status`,`order_from`) VALUES ('$user_id','$otp_number','$mobile','$order_note','$total','$delivery_charge','$tax_amount','$tax_percentage','$walletvalue','$discount','$promo_code','$promo_discount','$final_total','$payment_method','$address','$latitude','$longitude','$delivery_time','$order_status','$active_status','$order_from')";
    $db->sql($sql);
    $sql = "SELECT id FROM orders where user_id=$user_id and active_status = '$active_status' order by id desc limit 1";
    $db->sql($sql);
    $res_order_id = $db->getResult();
    // print_r($res_order_id);
    $order_id = $res_order_id[0]['id'];
    /* process wallet balance */
    $user_wallet_balance = $function->get_wallet_balance($user_id);
    if ($wallet_used == 'true') {
        /* deduct the balance & set the wallet transaction */
        $new_balance = $user_wallet_balance < $wallet_balance ? 0 : $user_wallet_balance - $wallet_balance;
        $function->update_wallet_balance($new_balance, $user_id);
        $wallet_txn_id = $function->add_wallet_transaction($order_id, $user_id, 'debit', $wallet_balance, 'Used against Order Placement');
    }
    for ($i = 0; $i < count($item_details); $i++) {
        $product_id = $item_details[$i]['product_id'];
        $measurement = $item_details[$i]['measurement'];
        $product_variant_id = $item_details[$i]['id'];
        $measurement_unit_id = $item_details[$i]['measurement_unit_id'];
        $stock_unit_id = $item_details[$i]['stock_unit_id'];
        $price = $item_details[$i]['price'];
        $discounted_price = $item_details[$i]['discounted_price'];
        $type = $item_details[$i]['type'];
        $total_stock = $item_details[$i]['stock'];
        $quantity = $quantity_arr[$i];
        $tax_title = $item_details[$i]['tax_title'];
        $tax_percentage = $item_details[$i]['tax_percentage'];
        $tax_amt = $discounted_price != 0 ? (($tax_percentage / 100) * $discounted_price)  : (($tax_percentage / 100) * $price);
        $sub_total = $discounted_price != 0 ? ($discounted_price + ($tax_percentage / 100) * $discounted_price) * $quantity : ($price + ($tax_percentage / 100) * $price) * $quantity;

        $data = array(
            'user_id' => $user_id,
            'order_id' => $db->escapeString($order_id),
            'product_variant_id' => $db->escapeString($product_variant_id),
            'quantity' => $db->escapeString($quantity),
            'price' => $db->escapeString($price),
            'discounted_price' => $db->escapeString($discounted_price),
            'tax_amount' => $db->escapeString($tax_amt),
            'tax_percentage' => (empty($tax_percentage) || $tax_percentage == "") ? 0 : $db->escapeString($tax_percentage),
            'discount' => $discount,
            'sub_total' => $db->escapeString($sub_total),
            'status' => $db->escapeString(json_encode($status)),
            'active_status' => $active_status

        );
        $neworder_id         = $db->escapeString($order_id);
        $product_variant_id = $db->escapeString($product_variant_id);
        $quantity             = $db->escapeString($quantity);
        $order_price        = $db->escapeString($price);
        $discounteds_price    = $db->escapeString($discounted_price);
        $tax_amount = $db->escapeString($tax_amt);
        $tax_percentage = (empty($tax_percentage) || $tax_percentage == "") ? 0 : $db->escapeString($tax_percentage);
        $order_sub_total    = $db->escapeString($sub_total);
        $order_item_status  = $db->escapeString(json_encode($status));
        $sql = "INSERT INTO `order_items`(`user_id`, `order_id`, `product_variant_id`, `quantity`, `price`, `discounted_price`,`tax_amount`,`tax_percentage`, `discount`, `sub_total`, `status`, `active_status`) VALUES ('$user_id','$neworder_id','$product_variant_id','$quantity','$order_price','$discounteds_price','$tax_amount', $tax_percentage ,'$discount','$order_sub_total','$order_item_status','$active_status')";
        $db->sql($sql);
        $res = $db->getResult();
        $balance = $final_total / 10;
        if ($type == 'packet') {
            $stock = $total_stock - $quantity;
            $sql = "update product_variant set stock = $stock where id = $product_variant_id";
            $db->sql($sql);
            $res = $db->getResult();
            $db->select("product_variant", "stock", null, "id='" . $product_variant_id . "'");
            $variant_qty = $db->getResult();
            if ($variant_qty[0]['stock'] <= 0) {
                $data = array(
                    "serve_for" => "Sold Out",
                );
                $db->update("product_variant", $data, "id=$product_variant_id");
                $res = $db->getResult();
            }
        } elseif ($type == 'loose') {
            if ($measurement_unit_id == $stock_unit_id) {
                $stock = $quantity * $measurement;
            } else {
                $db->select('unit', '*', null, 'id=' . $measurement_unit_id);
                $unit = $db->getResult();
                $stock = $function->convert_to_parent(($measurement * $quantity), $unit[0]['id']);
            }

            $sql = "update product_variant set stock = stock - $stock where product_id = $product_id AND id=$product_variant_id AND type='loose'";
            $db->sql($sql);
            $res = $db->getResult();
            $sql = "select stock from product_variant where product_id=" . $product_id;
            $db->sql($sql);
            $res_stck = $db->getResult();
            if ($res_stck[0]['stock'] <= 0) {
                $sql = "update product_variant set serve_for='Sold Out' where product_id=" . $product_id;
                $db->sql($sql);
            }
        }
    }
    $data = array(
        'final_total' => $final_total
    );
    if ($db->update('orders', $data, 'id=' . $order_id)) {
        $res = $db->getResult();
        $response['error'] = "false";
        $response['message'] = "Order placed successfully.";
        $response['order_id'] = $order_id;

        /* send email notification for the order received */
        if ($active_status == "received") {
            $sql = "select name, email, mobile, country_code from users where id=" . $user_id;
            $db->sql($sql);
            $res = $db->getResult();
            $to = $res[0]['email'];
            $mobile = $res[0]['mobile'];
            $country_code = $res[0]['country_code'];
            $subject = "Order received successfully";
            $message = "Hello, Dear " . ucwords($res[0]['name']) . ", We have received your order successfully. Your order summaries are as followed:<br><br>";
            $user_msg = "Hello, Dear " . ucwords($res[0]['name']) . ", We have received your order successfully. Your order summaries are as followed:<br><br>";
            $otp_msg = "Here is your OTP. Please, give it to delivery boy only while getting your order.";
            $message .= "<b>Order ID :</b> #" . $response['order_id'] . "<br><br>Ordered Items : <br>";
            $items = $db->escapeString($_POST['product_variant_id']);
            $quantity_arr = json_decode($_POST['quantity'], 1);
            $item_details = $function->get_product_by_variant_id($items);
            $subtotal = $item_data1 = array();
            for ($i = 0; $i < count($item_details); $i++) {
                $product_id = $item_details[$i]['product_id'];
                $measurement = $item_details[$i]['measurement'];
                $product_variant_id = $item_details[$i]['id'];
                $measurement_unit_id = $item_details[$i]['measurement_unit_id'];
                $stock_unit_id = $item_details[$i]['stock_unit_id'];
                $price = $item_details[$i]['price'];
                $discounted_price = $item_details[$i]['discounted_price'];
                $type = $item_details[$i]['type'];
                $total_stock = $item_details[$i]['stock'];
                $quantity = $quantity_arr[$i];
                $price = $item_details[$i]['discounted_price'] == 0 ? $item_details[$i]['price'] : $item_details[$i]['discounted_price'];
                $message .= "<b>Name : </b>" . $item_details[$i]['name'] . "<b> Unit :</b>" . $item_details[$i]['measurement'] . " " . $item_details[$i]['measurement_unit_name'] . "<b> QTY :</b>" . $quantity . "<b> Subtotal :</b>" . $price * $quantity . "<br>";
                $item_data1[] = array('name' => $item_details[$i]['name'], 'tax_amount' => $tax_amt, 'tax_percentage' => $tax_percentage, 'tax_title' => $item_details[$i]['tax_title'], 'unit' =>  $item_details[$i]['measurement'] . " " . $item_details[$i]['measurement_unit_name'], 'qty' => $quantity, 'subtotal' => ($price * $quantity));
            }
            $message .= "<b>OTP : </b>" . $otp_number . "<b>Total Amount : </b>" . $total . " <b>Delivery Charge : </b>" . $delivery_charge . " <b>Tax Amount : </b>" . $tax_amount . " <b>Discount : </b>" . $discount . " <b>Wallet Used : </b>" . $wallet_balance . " <b>Final Total :</b>" . $final_total;
            $message .= "<br>Payment Method : " . $payment_method;

            $order_data = array('total_amount' => $total, 'delivery_charge' => $delivery_charge, 'discount' => $discount, 'wallet_used' => $wallet_balance, 'final_total' => $final_total, 'payment_method' => $payment_method, 'address' => $address, 'user_msg' => $user_msg, 'otp_msg' => $otp_msg, 'otp' => $otp_number);

            $message .= "<br><br>Thank you for placing an order with us!<br><br>You will receive future updates on your order via Email!";
            send_smtp_mail($to, $subject, $item_data1, $order_data);
            $subject = "New order placed for $app_name";
            $message = "New order ID : #" . $response['order_id'] . " received please take note of it and proceed further";
            $function->send_notification_to_admin("New Order Arrived.", $message, "admin_notification", $response['order_id']);
            send_email($support_email, $subject, $message);
            $function->send_order_update_notification($user_id, "Your order has been received", $message, 'order', $response['order_id']);
        }
        print_r(json_encode($response));
    } else {
        $response['error'] = "true";
        $response['message'] = "Could not place order. Try again!";
        $response['order_id'] = 0;
        print_r(json_encode($response));
    }
} elseif (isset($_POST['place_order']) && isset($_POST['user_id']) && empty(json_decode($_POST['product_variant_id']))) {
    $response['error'] = "true";
    $response['message'] = "Order without items in cart can not be placed!";
    $response['order_id'] = 0;
    print_r(json_encode($response));
}

if (isset($_POST['get_orders']) && isset($_POST['user_id'])) {
    if (!verify_token()) {
        return false;
    }
    $where = '';
    $user_id = $db->escapeString($function->xss_clean($_POST['user_id']));
    $order_id = (isset($_POST['order_id']) && !empty($_POST['order_id']) && is_numeric($_POST['order_id'])) ? $db->escapeString($function->xss_clean($_POST['order_id'])) : "";
    $status = (isset($_POST['status']) && !empty($_POST['status'])) ? $db->escapeString($function->xss_clean($_POST['status'])) : "";
    $limit = (isset($_POST['limit']) && !empty($_POST['limit']) && is_numeric($_POST['limit'])) ? $db->escapeString($function->xss_clean($_POST['limit'])) : 10;
    $offset = (isset($_POST['offset']) && !empty($_POST['offset']) && is_numeric($_POST['offset'])) ? $db->escapeString($function->xss_clean($_POST['offset'])) : 0;
    $where = !empty($order_id) ? " AND id = " . $order_id : "";
    $where .= !empty($status) ? " AND active_status = '$status'" : "";
    $sql = "select count(o.id) as total from orders o where user_id=" . $user_id . $where;
    $db->sql($sql);
    $res = $db->getResult();
    $total = $res[0]['total'];
    $sql = "select *,(select name from users u where u.id=o.user_id) as user_name from orders o where user_id=" . $user_id . $where . " ORDER BY date_added DESC LIMIT $offset,$limit";

    $db->sql($sql);
    $res = $db->getResult();
    $i = 0;
    $j = 0;
    foreach ($res as $row) {
        if ($row['discount'] > 0) {
            $discounted_amount = $row['total'] * $row['discount'] / 100;
            $final_total = $row['total'] - $discounted_amount;
            $discount_in_rupees = $row['total'] - $final_total;
        } else {
            $discount_in_rupees = 0;
        }

        $res[$i]['discount_rupees'] = "$discount_in_rupees";
        $final_total = ceil($res[$i]['final_total']);
        $res[$i]['final_total'] = "$final_total";
        $res[$i]['date_added'] = date('d-m-Y h:i:sa', strtotime($res[$i]['date_added']));
        $sql = "select oi.*,v.id as variant_id, p.name,p.image,p.manufacturer,p.made_in,p.return_status,p.cancelable_status,p.till_status,v.measurement,(select short_code from unit u where u.id=v.measurement_unit_id) as unit from order_items oi join product_variant v on oi.product_variant_id=v.id join products p on p.id=v.product_id where order_id=" . $row['id'];
        $db->sql($sql);
        $res[$i]['items'] = $db->getResult();
        $res[$i]['status'] = json_decode($res[$i]['status']);

        if (in_array('awaiting_payment', array_column($res[$i]['status'], '0'))) {
            $temp_array = array_column($res[$i]['status'], '0');
            $index = array_search("awaiting_payment", $temp_array);
            unset($res[$i]['status'][$index]);
            $res[$i]['status'] = array_values($res[$i]['status']);
        }

        for ($j = 0; $j < count($res[$i]['items']); $j++) {
            $res[$i]['items'][$j]['status'] = (!empty($res[$i]['items'][$j]['status'])) ? json_decode($res[$i]['items'][$j]['status']) : array();

            if (in_array('awaiting_payment', array_column($res[$i]['items'][$j]['status'], '0'))) {
                $temp_array = array_column($res[$i]['items'][$j]['status'], '0');
                $index = array_search("awaiting_payment", $temp_array);
                unset($res[$i]['items'][$j]['status'][$index]);
                $res[$i]['items'][$j]['status'] = array_values($res[$i]['items'][$j]['status']);
            }

            $res[$i]['items'][$j]['image'] = DOMAIN_URL . $res[$i]['items'][$j]['image'];
            $sql = "SELECT id from return_requests where product_variant_id = " . $res[$i]['items'][$j]['variant_id'] . " AND user_id = " . $user_id;
            $db->sql($sql);
            $return_request = $db->getResult();
            if (empty($return_request)) {
                $res[$i]['items'][$j]['applied_for_return'] = false;
            } else {
                $res[$i]['items'][$j]['applied_for_return'] = true;
            }
        }
        $i++;
    }
    $orders = $order = array();

    if (!empty($res)) {
        $orders['error'] = false;
        $orders['total'] = $total;
        $orders['data'] = array_values($res);
        print_r(json_encode($orders));
    } else {
        $res['error'] = true;
        $res['message'] = "No orders found!";
        print_r(json_encode($res));
    }
}

if (isset($_POST['update_order_item_status']) && isset($_POST['order_item_id'])) {
    // if (!verify_token()) {
    //     return false;
    // }
    $order_item_id = $db->escapeString($function->xss_clean($_POST['order_item_id']));
    $order_id = $db->escapeString($function->xss_clean($_POST['order_id']));
    $postStatus = $db->escapeString($function->xss_clean($_POST['status']));
    $sql = "SELECT COUNT(id) as cancelled FROM `order_items` WHERE id=" . $order_item_id . " && status LIKE '%$postStatus%'";
    $db->sql($sql);
    $res_cancelled = $db->getResult();
    if ($res_cancelled[0]['cancelled'] == 'awaiting_payment' && ($postStatus == 'returned' || $postStatus == 'delivered' || $postStatus == 'shipped' || $postStatus == 'processed')) {
        $response['error'] = true;
        $response['message'] = "Order item can not $postStatus. Because it is on awaiting status.";
        print_r(json_encode($response));
        return false;
    }
    if ($res_cancelled[0]['cancelled'] > 0) {
        $response['error'] = true;
        $response['message'] = 'Could not update order status. Item is already ' . ucwords($postStatus) . '!';
        print_r(json_encode($response));
        return false;
    }

    $sql = "SELECT user_id,status,sub_total FROM order_items WHERE id =" . $order_item_id;
    $db->sql($sql);
    $result = $db->getResult();

    if (!empty($result)) {
        $status = json_decode($result[0]['status']);
        if ($postStatus == 'cancelled') {
            if($cancel_order_from == ""){
                $response = $function->is_product_cancellable($order_item_id);
                if ($response["error"] == 1) {
                    print_r(json_encode($response));
                    return false;
                }
            }
            $sql = 'SELECT final_total,total,user_id,payment_method,wallet_balance,delivery_charge,tax_amount,status FROM orders WHERE id=' . $order_id;
            $db->sql($sql);
            $res_order = $db->getResult();
            $sql = 'SELECT oi.*,oi.`product_variant_id`,oi.`quantity`,oi.`discounted_price`,oi.`price`,pv.`product_id`,pv.`type`,pv.`stock`,pv.`stock_unit_id`,pv.`measurement`,pv.`measurement_unit_id` FROM `order_items` oi join `product_variant` pv on pv.id = oi.product_variant_id WHERE oi.`id`=' . $order_item_id;
            $db->sql($sql);
            $res_oi = $db->getResult();
            $price = ($res_oi[0]['discounted_price'] == 0) ? ($res_oi[0]['price'] * $res_oi[0]['quantity']) - $res_oi[0]['tax_amount']  : $res_oi[0]['discounted_price'] * $res_oi[0]['quantity']  - $res_oi[0]['tax_amount'];
            $total = $res_order[0]['total'];
            $final_total = $res_order[0]['final_total'];
            $delivery_charge = $res_order[0]['delivery_charge'];
            if ($total - $price >= 0) {
                $sql_total = "update orders set total=$total-$price where id=" . $order_id;
                $db->sql($sql_total);
            }
            $sql = "select total from orders where id=" . $order_id;
            $db->sql($sql);
            $res_total = $db->getResult();
            $total = $res_total[0]['total'];

            if ($total < $config['min_amount']) {
                if ($delivery_charge == 0) {
                    $dchrg = $config['delivery_charge'];
                    $sql_delivery_chrg = "update orders set delivery_charge=$dchrg where id=" . $order_id;
                    $db->sql($sql_delivery_chrg);
                    $sql_final_total = "update orders set final_total=$final_total-$price+$dchrg where id=" . $order_id;
                } else {
                    $sql_final_total = "update orders set final_total=$final_total-$price where id=" . $order_id;
                }
                $db->sql($sql_final_total);
            } else {
                $sql_final_total = "update orders set final_total=$final_total-$price where id=" . $order_id;
            }
            $db->sql($sql_final_total);
            if ($total == 0) {
                $sql = "update orders set delivery_charge=0,tax_amount=0,tax_percentage=0,final_total=0 where id=" . $order_id;
                $db->sql($sql);
            }

            if ($res_oi[0]['type'] == 'packet') {
                $sql = "UPDATE product_variant SET stock = stock + " . $res_oi[0]['quantity'] . " WHERE id='" . $res_oi[0]['product_variant_id'] . "'";
                $db->sql($sql);

                $sql = "select stock from product_variant where id=" . $res_oi[0]['product_variant_id'];
                $db->sql($sql);
                $res_stock = $db->getResult();
                if ($res_stock[0]['stock'] > 0) {
                    $sql = "UPDATE product_variant set serve_for='Available' WHERE id='" . $res_oi[0]['product_variant_id'] . "'";
                    $db->sql($sql);
                }
            } else {
                /* When product type is loose */
                if ($res_oi[0]['measurement_unit_id'] != $res_oi[0]['stock_unit_id']) {
                    $stock = $function->convert_to_parent($res_oi[0]['measurement'], $res_oi[0]['measurement_unit_id']);
                    $stock = $stock * $res_oi[0]['quantity'];
                    // $sql = "UPDATE product_variant SET stock = stock + " . $stock . " WHERE product_id='" . $res_oi[0]['product_id'] . "'";
                    $sql = "UPDATE product_variant SET stock = stock + " . $stock . " WHERE product_id='" . $res_oi[0]['product_id'] . "'" .  " AND id='" . $res_oi[0]['product_variant_id'] . "'";

                    $db->sql($sql);
                } else {
                    $stock = $res_oi[0]['measurement'] * $res_oi[0]['quantity'];
                    // $sql = "UPDATE product_variant SET stock = stock + " . $stock . " WHERE product_id='" . $res_oi[0]['product_id'] . "'";
                    $sql = "UPDATE product_variant SET stock = stock + " . $stock . " WHERE product_id='" . $res_oi[0]['product_id'] . "'" .  " AND id='" . $res_oi[0]['product_variant_id'] . "'";
                    $db->sql($sql);
                }
                $sql = "select stock from product_variant where product_id=" . $res_oi[0]['product_id'];
                $db->sql($sql);
                $res_stck = $db->getResult();
                if ($res_stck[0]['stock'] > 0) {
                    // $sql = "UPDATE product_variant set serve_for='Available' WHERE product_id='" . $res_oi[0]['product_id'] . "'";
                    $sql = "UPDATE product_variant SET stock = stock + " . $stock . " WHERE product_id='" . $res_oi[0]['product_id'] . "'" .  " AND id='" . $res_oi[0]['product_variant_id'] . "'";
                    $db->sql($sql);
                }
            }
            $status[] = array($postStatus, date("d-m-Y h:i:sa"));
            $currentStatus = $postStatus;

            $oi_status = $db->escapeString(json_encode($status));
            $sql = "UPDATE order_items SET `status` = '" . $oi_status . "',active_status = '" . $currentStatus . "' WHERE id=" . $order_item_id;
            $db->sql($sql);

            $sql = "SELECT id FROM order_items WHERE order_id=" . $order_id;
            $db->sql($sql);
            $total = $db->numRows();
            $sql = "SELECT id FROM `order_items` WHERE order_id=" . $order_id . " && (`active_status` LIKE '%cancelled%' OR `active_status` LIKE '%returned%' )";
            $db->sql($sql);
            $cancelled = $db->numRows();
            if ($cancelled == $total) {
                if (strtolower($res_order[0]['payment_method']) != 'cod') {
                    /* update user's wallet */
                    $user_id = $res_order[0]['user_id'];
                    $total_amount = $res_order[0]['total'] + $res_order[0]['delivery_charge'] + $res_order[0]['tax_amount'];
                    $user_wallet_balance = $function->get_wallet_balance($user_id);
                    $new_balance = $user_wallet_balance + $total_amount;
                    $function->update_wallet_balance($new_balance, $user_id);
                    $wallet_txn_id = $function->add_wallet_transaction($order_id, $user_id, 'credit', $total_amount, 'Balance credited against item cancellation...');
                } else {
                    if ($res_order[0]['wallet_balance'] != 0) {
                        $user_id = $res_order[0]['user_id'];
                        $user_wallet_balance = $function->get_wallet_balance($user_id);
                        $new_balance = ($user_wallet_balance + $res_order[0]['wallet_balance']);
                        $function->update_wallet_balance($new_balance, $user_id);
                        $wallet_txn_id = $function->add_wallet_transaction($order_id, $user_id, 'credit', $res_order[0]['wallet_balance'], 'Balance credited against item cancellation!!');
                    }
                }

                $data_order = array(
                    'status' => $db->escapeString(json_encode($status)),
                    'active_status' => $currentStatus
                );
                $db->update('orders', $data_order, 'id=' . $order_id);
            }

            $response['error'] = false;
            $response['message'] = 'Order item cancelled successfully!';
            $response['subtotal'] = $result[0]['sub_total'];
            print_r(json_encode($response));
            return false;
        }
        if ($postStatus == 'returned') {
            // checking for product is returnable or not
            $response = $function->is_product_returnable($order_item_id);
            if ($response["error"] == 1) {
                print_r(json_encode($response));
                return false;
            }
            $is_item_delivered = 0;
            foreach ($status as $each_status) {
                if (in_array('delivered', $each_status)) {
                    $is_item_delivered = 1;
                    $config['max-product-return-days'];
                    $now = time(); // or your date as well
                    $status_date = strtotime($each_status[1]);
                    $datediff = $now - $status_date;
                    $no_of_days = round($datediff / (60 * 60 * 24));
                    if ($no_of_days > $config['max-product-return-days']) {
                        $response['error'] = true;
                        $response['message'] = 'Oops! Sorry you cannot return the item now. You have crossed product\'s maximum return period';
                        print_r(json_encode($response));
                        return false;
                    }
                }
            }
            if (!$is_item_delivered) {
                $response['error'] = true;
                $response['message'] = 'Cannot return item unless it is delivered!';
                print_r(json_encode($response));
                return false;
            }
            if ($function->is_return_request_exists($result[0]['user_id'], $order_item_id)) {
                $response['error'] = true;
                $response['message'] = 'Already applied for return';
                print_r(json_encode($response));
                return false;
            }
            /* store return request */
            $function->store_return_request($result[0]['user_id'], $order_id, $order_item_id);

            $response['error'] = false;
            $response['message'] = 'Order item returned request received successfully! Please wait for approval.';
            $response['subtotal'] = $result[0]['sub_total'];
            print_r(json_encode($response));
            return false;
        }
    } else {
        $response['error'] = true;
        $response['message'] = 'Order item not found!';
        print_r(json_encode($response));
        return false;
    }
}

if (isset($_POST['update_order_status']) && isset($_POST['id'])) {
    // if (!verify_token()) {
    //     return false;
    // }
    $id = $db->escapeString($function->xss_clean($_POST['id']));
    $postStatus = $db->escapeString($function->xss_clean($_POST['status']));
    $sql = "select user_id,payment_method,wallet_balance,total,delivery_charge,tax_amount,status,active_status,delivery_boy_id from orders where id=" . $id;
    $db->sql($sql);
    $res = $db->getResult();
    if ($res[0]['active_status'] == 'awaiting_payment' && ($postStatus == 'returned' || $postStatus == 'delivered' || $postStatus == 'shipped' || $postStatus == 'processed')) {
        $response['error'] = true;
        $response['message'] = "Order can not $postStatus. Because it is on awaiting status.";
        print_r(json_encode($response));
        return false;
    }

    if (isset($_POST['delivery_boy_id']) && !empty($_POST['delivery_boy_id']) && $_POST['delivery_boy_id'] != "") {
        if ($postStatus == 'awaiting_payment') {
            $response['error'] = true;
            $response['message'] = "You can not assign delivery boy when order status is Awaiting Payment.";
            print_r(json_encode($response));
            return false;
        }
        $delivery_boy_id = $db->escapeString($function->xss_clean($_POST['delivery_boy_id']));
        $sql = "SELECT delivery_boy_id,status FROM `orders` where id=$id";
        $db->sql($sql);
        $res_delivery_boy_id = $db->getResult();

        if (($res_delivery_boy_id[0]['delivery_boy_id'] == 0)
            || ($res_delivery_boy_id[0]['delivery_boy_id'] != $delivery_boy_id && $res_delivery_boy_id[0]['status'] != 'cancelled')
        ) {
            $sql_get_name = "select name from delivery_boys where id='$delivery_boy_id'";
            $db->sql($sql_get_name);
            $delivery_boy_name = $db->getResult();
            if ($postStatus == 'delivered') {
                $message_delivery_boy = "Hello, Dear " . ucwords($delivery_boy_name[0]['name']) . ", your order has been delivered. order ID : #" . $id . ". Please take a note of it.";
            } else {
                $message_delivery_boy = "Hello, Dear " . ucwords($delivery_boy_name[0]['name']) . ", You have new order to deliver. Here is your order ID : #" . $id . ". Please take a note of it.";
            }
            $function->send_notification_to_delivery_boy($delivery_boy_id, "Your new order with ID : #$id has been " . ucwords($postStatus), $message_delivery_boy, 'delivery_boys', $id);
            $function->store_delivery_boy_notification($delivery_boy_id, $id, "Your new order with ID : #$id  has been " . ucwords($postStatus), $message_delivery_boy, 'order_reward');
        }
        $sql = "UPDATE orders SET `delivery_boy_id`='" . $delivery_boy_id . "' WHERE id=" . $id;
        $db->sql($sql);
    }
    $sql = "SELECT COUNT(id) as cancelled FROM `orders` WHERE id=" . $id . " && (active_status LIKE '%cancelled%' OR active_status LIKE '%returned%')";
    $db->sql($sql);
    $res_cancelled = $db->getResult();
    if ($res_cancelled[0]['cancelled'] > 0) {
        $response['error'] = true;
        $response['message'] = 'Could not update order status once cancelled or returned!';
        print_r(json_encode($response));
        return false;
    }

    if ($res[0]['active_status'] != 'delivered' && $postStatus == 'returned') {
        $response['error'] = true;
        $response['message'] = 'Cannot return order unless it is delivered!';
        print_r(json_encode($response));
        return false;
    }
    $sql = "SELECT sub_total FROM order_items WHERE order_id=" . $id;
    $db->sql($sql);
    $res_query = $db->getResult();
    $sql = "SELECT COUNT(id) as total FROM `orders` WHERE user_id=" . $res[0]['user_id'] . " && status LIKE '%delivered%'";
    $db->sql($sql);
    $res_count = $db->getResult();
    $sql = "SELECT * FROM `users` WHERE id=" . $res[0]['user_id'];
    $db->sql($sql);
    $res_user = $db->getResult();
    if (!empty($res)) {
        $status = json_decode($res[0]['status']);
        $user_id =  $res[0]['user_id'];
        foreach ($status as $each) {
            if (in_array($postStatus, $each)) {
                $response['error'] = true;
                $response['message'] = isset($_POST['delivery_boy_id']) && $_POST['delivery_boy_id'] != '' && ($res[0]['delivery_boy_id'] != 0) ? 'Delivery Boy updated, Order already ' . $postStatus : 'Order already ' . $postStatus;
                print_r(json_encode($response));
                return false;
            }
        }
        if ($postStatus == 'cancelled' || $postStatus == 'returned') {

            $sql = 'SELECT oi.`id` as order_item_id,oi.`product_variant_id`,oi.`quantity`,pv.`product_id`,pv.`type`,pv.`stock`,pv.`stock_unit_id`,pv.`measurement`,pv.`measurement_unit_id` FROM `order_items` oi join `product_variant` pv on pv.id = oi.product_variant_id WHERE `order_id`=' . $id;
            $db->sql($sql);
            $res_oi = $db->getResult();
            if($cancel_order_from == ""){
                if ($postStatus == 'cancelled') {
                    $cancelation_error = 0;
                    for ($j = 0; $j < count($res_oi); $j++) {
                        $resp = $function->is_product_cancellable($res_oi[$j]['order_item_id']);
                        if ($resp['till_status_error'] == 1 || $resp['cancellable_status_error'] == 1) {
                            $cancelation_error = 1;
                        }
                    }
                    if ($cancelation_error == 1) {
                        $resp['error'] = true;
                        $resp['message'] = "Found one or more items in order which is either not cancelable or not matching cancelation criteria!";
                        print_r(json_encode($resp));
                        return false;
                    }
                } else {
                    $return_error = 0;
                    for ($j = 0; $j < count($res_oi); $j++) {
                        $resp = $function->is_product_returnable($res_oi[$j]['order_item_id']);
                        if ($resp['return_status_error'] == 1) {
                            $return_error = 1;
                        }
                    }
                    if ($return_error == 1) {
                        $resp['error'] = true;
                        $resp['message'] = "Found one or more items in order which is not returnable!";
                        print_r(json_encode($resp));
                        return false;
                    }
                }
            }
            for ($i = 0; $i < count($res_oi); $i++) {
                if ($res_oi[$i]['type'] == 'packet') {
                    $sql = "UPDATE product_variant SET stock = stock + " . $res_oi[$i]['quantity'] . " WHERE id='" . $res_oi[$i]['product_variant_id'] . "'";
                    $db->sql($sql);
                    $sql = "select stock from product_variant where id=" . $res_oi[0]['product_variant_id'];
                    $db->sql($sql);
                    $res_stock = $db->getResult();
                    if ($res_stock[0]['stock'] > 0) {
                        $sql = "UPDATE product_variant set serve_for='Available' WHERE id='" . $res_oi[0]['product_variant_id'] . "'";
                        $db->sql($sql);
                    }
                } else {
                    /* When product type is loose */
                    if ($res_oi[$i]['measurement_unit_id'] != $res_oi[$i]['stock_unit_id']) {
                        $stock = $function->convert_to_parent($res_oi[$i]['measurement'], $res_oi[$i]['measurement_unit_id']);
                        $stock = $stock * $res_oi[$i]['quantity'];
                        // $sql = "UPDATE product_variant SET stock = stock + " . $stock . " WHERE product_id='" . $res_oi[$i]['product_id'] . "'";
                        $sql = "UPDATE product_variant SET stock = stock + " . $stock . " WHERE product_id='" . $res_oi[0]['product_id'] . "'" .  " AND id='" . $res_oi[0]['product_variant_id'] . "'";

                        $db->sql($sql);
                    } else {
                        $stock = $res_oi[$i]['measurement'] * $res_oi[$i]['quantity'];
                        // $sql = "UPDATE product_variant SET stock = stock + " . $stock . " WHERE product_id='" . $res_oi[$i]['product_id'] . "'";
                        $sql = "UPDATE product_variant SET stock = stock + " . $stock . " WHERE product_id='" . $res_oi[0]['product_id'] . "'" .  " AND id='" . $res_oi[0]['product_variant_id'] . "'";

                        $db->sql($sql);
                    }
                    // $sql = "select stock from product_variant where product_id=" . $res_oi[0]['product_id'];
                    // $db->sql($sql);
                    // $res_stck = $db->getResult();
                    // if ($res_stck[0]['stock'] > 0) {
                    //     // $sql = "UPDATE product_variant set serve_for='Available' WHERE product_id='" . $res_oi[0]['product_id'] . "'";
                    //     echo  "4st".  $sql = "UPDATE product_variant SET stock = stock + " . $stock . " WHERE product_id='" . $res_oi[0]['product_id'] . "'".  " AND id='" . $res_oi[0]['product_variant_id'] . "'";

                    //     $db->sql($sql);
                    // }
                }
            }
            if (strtolower($res[0]['payment_method']) != 'cod') {
                /* update user's wallet */
                $user_id = $res[0]['user_id'];
                $total = $res[0]['total'] + $res[0]['delivery_charge'] + $res[0]['tax_amount'];
                $user_wallet_balance = $function->get_wallet_balance($user_id);
                $new_balance = $user_wallet_balance + $total;
                $function->update_wallet_balance($new_balance, $user_id);
                /* add wallet transaction */
                $wallet_txn_id = $function->add_wallet_transaction($id, $user_id, 'credit', $total, 'Balance credited against item cancellation..');
            } else {
                if ($res[0]['wallet_balance'] != 0) {
                    /* update user's wallet */
                    $user_id = $res[0]['user_id'];
                    $total = $res[0]['total'] + $res[0]['delivery_charge'] + $res[0]['tax_amount'];
                    $user_wallet_balance = $function->get_wallet_balance($user_id);
                    $new_balance = ($user_wallet_balance + $res[0]['wallet_balance']);
                    $function->update_wallet_balance($new_balance, $user_id);
                    /* add wallet transaction */
                    $wallet_txn_id = $function->add_wallet_transaction($id, $user_id, 'credit', $total, 'Balance credited against item cancellation!');
                }
            }
        }


        if ($postStatus == 'delivered') {
            $sql = "SELECT delivery_boy_id,final_total,total FROM orders WHERE id=" . $id;
            $db->sql($sql);
            $res_boy = $db->getResult();
            if ($res_boy[0]['delivery_boy_id'] != 0) {
                $sql = "SELECT bonus,name FROM delivery_boys WHERE id=" . $res_boy[0]['delivery_boy_id'];
                $db->sql($sql);
                $res_bonus = $db->getResult();
                $reward = $res_boy[0]['total'] / 100 * $res_bonus[0]['bonus'];

                if ($reward > 0) {
                    $sql = "UPDATE delivery_boys SET balance = balance + $reward WHERE id=" . $res_boy[0]['delivery_boy_id'];
                    $db->sql($sql);
                    $comission = $function->add_delivery_boy_commission($delivery_boy_id, 'credit', $reward, 'Order Delivery Commission.');
                    $sql = "SELECT value FROM `settings` WHERE variable='currency'";
                    $db->sql($sql);
                    $currency = $db->getResult();
                    $message_delivery_boy = "Hello, Dear " . ucwords($res_bonus[0]['name']) . ", Here is the new update on your order for the order ID : #" . $id . ". Your Commission of" . $reward . " is credited. Please take a note of it.";
                    $function->send_notification_to_delivery_boy($delivery_boy_id, "Your commission " . $reward . " " . $currency[0]['value'] . " has been credited", "$message_delivery_boy", 'delivery_boys', $id);
                    $function->store_delivery_boy_notification($delivery_boy_id, $id, "Your commission " . $reward . " " . $currency[0]['value'] . " has been credited", $message_delivery_boy, 'order_reward');
                }
            }
            if ($config['is-refer-earn-on'] == 1) {
                if ($res_boy[0]['total'] >= $config['min-refer-earn-order-amount']) {
                    if ($res_count[0]['total'] == 0) {
                        if ($res_user[0]['friends_code'] != '') {
                            if ($config['refer-earn-method'] == 'percentage') {
                                $percentage = $config['refer-earn-bonus'];
                                $bonus_amount = $res_boy[0]['total'] / 100 * $percentage;
                                if ($bonus_amount > $config['max-refer-earn-amount']) {
                                    $bonus_amount = $config['max-refer-earn-amount'];
                                }
                            } else {
                                $bonus_amount = $config['refer-earn-bonus'];
                            }
                            $sql  = "SELECT name,friends_code FROM users WHERE id=" . $res[0]['user_id'];
                            $db->sql($sql);
                            $res_data = $db->getResult();

                            $sql = " select id from `users` where `referral_code` = '" . $res_data[0]['friends_code'] . "'";
                            $db->sql($sql);
                            $friend_user = $db->getResult();

                            if (!empty($friend_user))
                                $function->add_wallet_transaction($id, $friend_user[0]['id'], 'credit', floor($bonus_amount), 'Refer & Earn Bonus on first order by ' . ucwords($res_data[0]['name']));

                            $sql = "UPDATE users SET balance = balance + floor($bonus_amount) WHERE referral_code='" . $res_data[0]['friends_code'] . "'";
                            $db->sql($sql);
                        }
                    }
                }
            }
        }
        $temp = [];
        foreach ($status as $s) {
            array_push($temp, $s[0]);
        }
        $sql = "SELECT id,active_status FROM order_items WHERE order_id=" . $id;
        $db->sql($sql);
        $result = $db->getResult();
        if ($postStatus == 'cancelled') {
            if (!in_array('cancelled', $temp)) {
                $status[] = array('cancelled', date("d-m-Y h:i:sa"));
                $data = array(
                    'status' => $db->escapeString(json_encode($status)),
                );
            }
            $db->update('orders', $data, 'id=' . $id);
            foreach ($result as $item) {
                if ($item['active_status'] != 'cancelled') {
                    $item_data = array(
                        'status' => $db->escapeString(json_encode($status)),
                        'active_status' => 'cancelled'
                    );
                    $db->update('order_items', $item_data, 'id=' . $item['id']);
                }
            }
        }

        if ($postStatus == 'processed') {
            if (!in_array('processed', $temp)) {
                $status[] = array('processed', date("d-m-Y h:i:sa"));
                $data = array(
                    'status' => $db->escapeString(json_encode($status))
                );
            }
            $db->update('orders', $data, 'id=' . $id);
            foreach ($result as $item) {
                $item_data = array(
                    'status' => $db->escapeString(json_encode($status)),
                    'active_status' => 'processed'
                );
                if ($item['active_status'] != 'cancelled') {
                    $db->update('order_items', $item_data, 'id=' . $item['id']);
                }
            }
        }

        if ($postStatus == 'received') {
            if (!in_array('received', $temp)) {
                $status[] = array('received', date("d-m-Y h:i:sa"));
                $data = array(
                    'status' => $db->escapeString(json_encode($status))
                );
            }
            $db->update('orders', $data, 'id=' . $id);
            foreach ($result as $item) {
                $item_data = array(
                    'status' => $db->escapeString(json_encode($status)),
                    'active_status' => 'received'
                );
                if ($item['active_status'] != 'cancelled') {
                    $db->update('order_items', $item_data, 'id=' . $item['id']);
                }
            }
            /* get order data */
            $user_id1 = $function->get_data($columns = ['user_id', 'total', 'delivery_charge', 'discount', 'final_total', 'payment_method', 'address', 'otp'], 'id=' . $id, 'orders');

            /* get user data */
            $user_email = $function->get_data($columns = ['email', 'name'], 'id=' . $user_id1[0]['user_id'], 'users');
            $subject = "Order received successfully";

            /* get order item by order id */
            $order_item = $function->get_order_item_by_order_id($id);
            $item_ids = array_column($order_item, 'product_variant_id');

            /* get product details by varient id */
            $item_details = $function->get_product_by_variant_id(json_encode($item_ids));

            for ($i = 0; $i < count($item_details); $i++) {
                $item_data1[] = array(
                    'name' => $item_details[$i]['name'], 'tax_amount' => $order_item[$i]['tax_amount'], 'tax_percentage' => $order_item[$i]['tax_percentage'], 'tax_title' => $item_details[$i]['tax_title'], 'unit' =>  $item_details[$i]['measurement'] . " " . $item_details[$i]['measurement_unit_name'],
                    'qty' => $order_item[$i]['quantity'], 'subtotal' => $order_item[$i]['sub_total']
                );
            }

            $user_wallet_balance = $function->get_wallet_balance($user_id1[0]['user_id']);
            $user_msg = "Hello, Dear " . $user_email[0]['name'] . ", We have received your order successfully. Your order summaries are as followed:<br><br>";
            $otp_msg = "Here is your OTP. Please, give it to delivery boy only while getting your order.";

            $order_data = array('total_amount' => $user_id1[0]['total'], 'delivery_charge' => $user_id1[0]['delivery_charge'], 'discount' => $user_id1[0]['discount'], 'wallet_used' => $user_wallet_balance, 'final_total' => $user_id1[0]['final_total'], 'payment_method' => $user_id1[0]['payment_method'], 'address' => $user_id1[0]['address'], 'user_msg' => $user_msg, 'otp_msg' => $otp_msg, 'otp' => $user_id1[0]['otp']);
            send_smtp_mail($user_email[0]['email'], $subject, $item_data1, $order_data);
            $function->send_order_update_notification($user_id1[0]['user_id'], "Your order has been " . ucwords($postStatus), $user_msg, 'order', $id);
        }
        if ($postStatus == 'shipped') {
            if (!in_array('processed', $temp)) {
                $status[] = array('processed', date("d-m-Y h:i:sa"));
                $data = array('status' => $db->escapeString(json_encode($status)));
            }
            if (!in_array('shipped', $temp)) {
                $status[] = array('shipped', date("d-m-Y h:i:sa"));
                $data = array('status' => $db->escapeString(json_encode($status)));
            }
            $db->update('orders', $data, 'id=' . $id);
            foreach ($result as $item) {
                $item_data = array(
                    'status' => $db->escapeString(json_encode($status)),
                    'active_status' => 'shipped'
                );
                if ($item['active_status'] != 'cancelled') {
                    $db->update('order_items', $item_data, 'id=' . $item['id']);
                }
            }
        }
        if ($postStatus == 'delivered') {
            if (!in_array('processed', $temp)) {
                $status[] = array('processed', date("d-m-Y h:i:sa"));
                $data = array('status' => $db->escapeString(json_encode($status)));
            }
            if (!in_array('shipped', $temp)) {
                $status[] = array('shipped', date("d-m-Y h:i:sa"));
                $data = array('status' => $db->escapeString(json_encode($status)));
            }
            if (!in_array('delivered', $temp)) {
                $status[] = array('delivered', date("d-m-Y h:i:sa"));
                $data = array('status' => $db->escapeString(json_encode($status)));
            }
            $db->update('orders', $data, 'id=' . $id);
            $item_data = array(
                'status' => $db->escapeString(json_encode($status)),
                'active_status' => 'delivered'
            );
            foreach ($result as $item) {

                if ($item['active_status'] != 'cancelled') {
                    $db->update('order_items', $item_data, 'id=' . $item['id']);
                }
            }
        }
        if ($postStatus == 'returned') {
            $status[] = array('returned', date("d-m-Y h:i:sa"));
            $data = array('status' => $db->escapeString(json_encode($status)));
            $db->update('orders', $data, 'id=' . $id);
            $item_data = array(
                'status' => $db->escapeString(json_encode($status)),
                'active_status' => 'returned'
            );
            foreach ($result as $item) {

                if ($item['active_status'] != 'cancelled' && $item['active_status'] == 'delivered') {
                    $db->update('order_items', $item_data, 'id=' . $item['id']);
                }
            }
        }
        $i = sizeof($status);
        $currentStatus = $status[$i - 1][0];
        $final_status = array(
            'active_status' => $currentStatus
        );
        if ($db->update('orders', $final_status, 'id=' . $id)) {
            $response['error'] = false;
            if ($postStatus == 'cancelled') {
                $response['message'] = "Order has been cancelled!";
            } elseif ($postStatus == 'returned') {
                $response['message'] = "Order has been returned!";
            } else {
                $response['message'] = "Order updated successfully.";
            }
            if ($postStatus != 'received') {
                $user_data = $function->get_data($columns = ['name', 'email', 'mobile', 'country_code'], 'id=' . $user_id, 'users');
                $to = $user_data[0]['email'];
                $mobile = $user_data[0]['mobile'];
                $country_code = $user_data[0]['country_code'];
                $subject = "Your order has been " . ucwords($postStatus);
                $message = "Hello, Dear " . ucwords($user_data[0]['name']) . ", Here is the new update on your order for the order ID : #" . $id . ". Your order has been " . ucwords($postStatus) . ". Please take a note of it.";
                $message .= "Thank you for using our services!You will receive future updates on your order via Email!";
                $function->send_order_update_notification($user_id, "Your order has been " . ucwords($postStatus), $message, 'order', $id);
                send_email($to, $subject, $message);
                $message = "Hello, Dear " . ucwords($user_data[0]['name']) . ", Here is the new update on your order for the order ID : #" . $id . ". Your order has been " . ucwords($postStatus) . ". Please take a note of it.";
                $message .= "Thank you for using our services! Contact us for more information";
            }
            $res = $db->getResult();

            print_r(json_encode($response));
        } else {
            $response['error'] = true;
            $response['message'] = isset($_POST['delivery_boy_id']) && $_POST['delivery_boy_id'] != '' ? 'Delivery Boy updated, But could not update order status try again!' : 'Could not update order status try again!';
            print_r(json_encode($response));
        }
    } else {
        $response['error'] = true;
        $response['message'] = "Sorry Invalid order ID";
        print_r(json_encode($response));
    }
}

if (isset($_POST['get_reorder_data'])) {
    if (!verify_token()) {
        return false;
    }
    $id = $db->escapeString($function->xss_clean($_POST['id']));
    $sql = "select * from `orders` where id=$id";
    $db->sql($sql);
    $res = $db->getResult();
    if (empty($res)) {
        $response['error'] = true;
        $response['message'] = "Sorry Invalid order ID";
        print_r(json_encode($response));
    } else {
        $sql = "select * from `order_items` where order_id=$id";
        $db->sql($sql);
        $order_items = $db->getResult();

        $items = $temp = [];
        foreach ($order_items as $item) {
            $temp['product_variant_id'] = $item['product_variant_id'];
            $temp['quantity'] = $item['quantity'];
            $items[] = $temp;
        }

        $res[0]['status'] = json_decode($res[0]['status']);
        $res[0]['items'] = $items;
        $response['error'] = true;
        $response['message'] = "Order data retrived successfully";
        $response['data'] = $res[0];
        print_r(json_encode($response));
    }
}

if (isset($_POST['get_settings'])) {
    if (!verify_token()) {
        return false;
    }
    $sql = "select value from `settings` where variable='system_timezone'";
    $db->sql($sql);
    $res = $db->getResult();
    $sql = "select value from `settings` where variable='currency'";
    $db->sql($sql);
    $res_currency = $db->getResult();
    if (!empty($res)) {
        $response['error'] = false;
        $response['settings'] = json_decode($res[0]['value'], 1);
        $response['settings']['currency'] = $res_currency[0]['value'];
        $response['settings']['delivery_charge'] = empty($response['settings']['delivery_charge']) ? "0" : $response['settings']['delivery_charge'];
        $response['settings']['min-refer-earn-order-amount'] = empty($response['settings']['min-refer-earn-order-amount']) ? "0" : $response['settings']['min-refer-earn-order-amount'];
        $response['settings']['min_amount'] = empty($response['settings']['min_amount']) ? "0" : $response['settings']['min_amount'];
        $response['settings']['max-refer-earn-amount'] = empty($response['settings']['max-refer-earn-amount']) ? "0" : $response['settings']['max-refer-earn-amount'];
        $response['settings']['minimum-withdrawal-amount'] = empty($response['settings']['minimum-withdrawal-amount']) ? "0" : $response['settings']['minimum-withdrawal-amount'];
        $response['settings']['refer-earn-bonus'] = empty($response['settings']['refer-earn-bonus']) ? "0" : $response['settings']['refer-earn-bonus'];
        $response['settings']['current_version'] = empty($response['settings']['current_version']) ? "0" : $response['settings']['current_version'];
        $response['settings']['minimum_version_required'] = empty($response['settings']['minimum_version_required']) ? "0" : $response['settings']['minimum_version_required'];
        $response['settings']['user-wallet-refill-limit'] = (!isset($response['settings']['user-wallet-refill-limit']) || empty($response['settings']['user-wallet-refill-limit'])) ? 0 : $response['settings']['user-wallet-refill-limit'];
        $response['settings']['area-wise-delivery-charge'] = (!isset($response['settings']['area-wise-delivery-charge']) || empty($response['settings']['area-wise-delivery-charge'])) ? 0 : $response['settings']['area-wise-delivery-charge'];
        $response['settings']['area-wise-delivery'] = (!isset($response['settings']['area-wise-delivery']) || empty($response['settings']['area-wise-delivery'])) ? 0 : $response['settings']['area-wise-delivery'];
        $response['settings']['generate-otp'] = (!isset($response['settings']['generate-otp']) || empty($response['settings']['generate-otp'])) ? 1 : $response['settings']['generate-otp'];
        // $response['settings']['tax'] = empty($response['settings']['delivery_charge']) ? "0" : $response['settings']['delivery_charge'];

        print_r(json_encode($response));
    } else {
        $response['error'] = true;
        $response['settings'] = "No settings found!";
        $response['message'] = "Something went wrong!";
        print_r(json_encode($response));
    }
}

if (isset($_POST['update_order_total_payable']) && isset($_POST['id'])) {

    $id = $db->escapeString($function->xss_clean($_POST['id']));
    $discount = $db->escapeString($function->xss_clean($_POST['discount']));
    $deliver_by = $db->escapeString($function->xss_clean($_POST['deliver_by']));
    $total_payble = $db->escapeString($function->xss_clean($_POST['total_payble']));
    $total_payble = round($total_payble, 2);
    $data = array(
        'discount' => $discount,
        'deliver_by' => $deliver_by,
    );
    $data1 = array(
        'discount' => $discount,
        'final_total' => $total_payble,
    );


    if ($discount >= 0) {
        $db->update('order_items', $data, 'order_id=' . $id);
        $db->update('orders', $data1, 'id=' . $id);
        $res = $db->getResult();
        if (!empty($res)) {
            $response['error'] = false;
            $response['message'] = "Order updated successfully.";
            print_r(json_encode($response));
        } else {
            $response['error'] = true;
            $response['message'] = "Could not update order. Try again!";
            print_r(json_encode($response));
        }
    }
}


if (isset($_POST['add_transaction']) && $_POST['add_transaction'] == true) {
    if (!verify_token()) {
        return false;
    }
    /*add data to transaction table*/
    $user_id = $db->escapeString($function->xss_clean($_POST['user_id']));
    $order_id = $db->escapeString($function->xss_clean($_POST['order_id']));
    $type = $db->escapeString($function->xss_clean($_POST['type']));
    $txn_id = $db->escapeString($function->xss_clean($_POST['txn_id']));
    $amount = $db->escapeString($function->xss_clean($_POST['amount']));
    $status = $db->escapeString($function->xss_clean($_POST['status']));
    $message = $db->escapeString($function->xss_clean($_POST['message']));
    $transaction_date = (isset($_POST['addedon']) && !empty($_POST['addedon'])) ? $db->escapeString($function->xss_clean($_POST['addedon'])) : date('Y-m-d H:i:s');
    $data = array(
        'user_id' => $user_id,
        'order_id' => $order_id,
        'type' => $type,
        'txn_id' => $txn_id,
        'amount' => $amount,
        'status' => $status,
        'message' => $message,
        'transaction_date' => $transaction_date
    );
    $db->insert('transactions', $data);
    $res = $db->getResult();
    // if (!empty($res)) {
    //     // $function->send_order_update_notification($user_id, "Payment Transaction", $message, 'payment_transaction', $order_id);
    // }
    $response['error'] = false;
    $response['transaction_id'] = $res[0];
    $response['message'] = "Transaction added successfully!";
    echo json_encode($response);
}

/* 
	accesskey:90336
	delete_order:1 
    order_id:73
*/
if (isset($_POST['delete_order']) && $_POST['delete_order'] == true) {
    if (!verify_token()) {
        return false;
    }
    /*add data to transaction table*/

    $order_id = $db->escapeString($function->xss_clean($_POST['order_id']));

    // delete data from pemesanan table
    $sql_query = "DELETE FROM orders WHERE ID =" . $order_id;
    if ($db->sql($sql_query)) {
        $sql = "DELETE FROM order_items WHERE order_id =" . $order_id;
        $db->sql($sql);

        $response['error'] = false;
        $response['message'] = "Order deleted successfully!";
    } else {
        $response['error'] = true;
        $response['message'] = "Order does not deleted!";
    }
    echo json_encode($response);
}

if (isset($_POST['test']) && $_POST['test'] == true) {
    $res = $function->send_notification_to_admin("test", "hello", "admin_notification", 12);
    print_r($res);
}

function findKey($array, $keySearch)
{
    foreach ($array as $key => $item) {
        if ($key == $keySearch) {
            return true;
        } elseif (is_array($item) && findKey($item, $keySearch)) {
            return true;
        }
    }
    return false;
}
