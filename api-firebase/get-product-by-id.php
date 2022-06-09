<?php
header('Access-Control-Allow-Origin: *');
include_once('../includes/variables.php');
include_once('../includes/crud.php');
include_once('verify-token.php');
$db = new Database();
$db->connect();
// date_default_timezone_set('Asia/Kolkata');
include_once('../includes/custom-functions.php');
$fn = new custom_functions;

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
get-product-by-id.php
	accesskey:90336
	product_id:230
	user_id:369 {optional}
*/
if (!verify_token()) {
	return false;
}
if(isset($_POST['slug']) && trim($_POST['slug']) != "" && !isset($_POST['product_id'])){
	$slug = $db->escapeString($fn->xss_clean($_POST['slug']));
	$sql = "SELECT * FROM products WHERE slug = '$slug'";
	$db->sql($sql);
	$res = $db->getResult();
	if(isset($res[0]['id']) && intval($res[0]['id'])){
		$_POST['product_id'] = $res[0]['id'];
	}
}
if (isset($_POST['accesskey']) && isset($_POST['product_id'])) {
	$access_key_received = $db->escapeString($fn->xss_clean($_POST['accesskey']));
	$product_id = $db->escapeString($fn->xss_clean($_POST['product_id']));
	$user_id = (isset($_POST['user_id']) && is_numeric($_POST['user_id'])) ? $db->escapeString($fn->xss_clean($_POST['user_id'])) : "";

	if ($access_key_received == $access_key) {


		$sql = "SELECT * FROM products WHERE id = '" . $product_id . "' ";

		$db->sql($sql);
		$res = $db->getResult();
		$product = array();
		$i = 0;
		foreach ($res as $row) {
			$sql = "SELECT *,(SELECT short_code FROM unit u WHERE u.id=pv.measurement_unit_id) as measurement_unit_name,(SELECT short_code FROM unit u WHERE u.id=pv.stock_unit_id) as stock_unit_name FROM product_variant pv WHERE pv.product_id=" . $row['id'] . " ORDER BY serve_for ASC";
			$db->sql($sql);
			$variants = $db->getResult();

			$row['other_images'] = json_decode($row['other_images'], 1);
			$row['other_images'] = (empty($row['other_images'])) ? array() : $row['other_images'];
			for ($j = 0; $j < count($row['other_images']); $j++) {
				$row['other_images'][$j] = DOMAIN_URL . $row['other_images'][$j];
			}
			if($row['tax_id'] == 0){
                $row['tax_title'] = "";
                $row['tax_percentage'] = "0";
            }else{
                $t_id = $row['tax_id'];
                $sql_tax = "SELECT * from taxes where id= $t_id";
                $db->sql($sql_tax);
                $res_tax = $db->getResult(); 
                foreach($res_tax as $tax){
                    $row['tax_title'] = $tax['title'];
                    $row['tax_percentage'] = (!empty($tax['percentage'])) ? $tax['percentage'] : "0";
                }
            }
			// for ($k = 0; $k < count($variants); $k++) {

			// 	if (!empty($user_id)) {
			// 		$sql = "SELECT qty as cart_count FROM cart where product_variant_id= " . $variants[$k]['id'] . " AND user_id=" . $user_id;
			// 		$db->sql($sql);
			// 		$res = $db->getResult();
			// 		if (!empty($res)) {
			// 			foreach ($res as $row1) {
			// 				$variants[$k]['cart_count'] = $row1['cart_count'];
			// 			}
			// 		} else {
			// 			$variants[$k]['cart_count'] = "0";
			// 		}
			// 	} else {
			// 		$variants[$k]['cart_count'] = "0";
			// 	}
            // }
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
			if (!empty($user_id)) {
                $sql = "SELECT id from favorites where product_id = " . $row['id'] . " AND user_id = " . $user_id;
                $db->sql($sql);
                $result = $db->getResult();
                if (!empty($result)) {
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
		if (!empty($product)) {
			$output = json_encode(array(
				'error' => false,
				'data' => $product
			));
		} else {
			$output = json_encode(array(
				'error' => true,
				'data' => 'No products available'
			));
		}
	} else {
		die('accesskey is incorrect.');
	}
} else {
	die('accesskey and product id are required.');
}

//Output the output.
echo $output;

$db->disconnect();
//to check if the string is json or not
function isJSON($string)
{
	return is_string($string) && is_array(json_decode($string, true)) && (json_last_error() == JSON_ERROR_NONE) ? true : false;
}
