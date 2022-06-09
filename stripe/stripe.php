<?php
include_once '../includes/crud.php';
include_once '../includes/custom-functions.php';
/* 
    1. get_credentials()
    2. create_customer($customer_data)
    3. construct_event($request_body, $sigHeader, $secret,$tolerance = DEFAULT_TOLERANCE)
    4. create_payment_intent($c_data)
    5. curl($url, $method = 'GET', $data = [])
*/
const DEFAULT_TOLERANCE = 300;
class Stripe
{
    private $server_key = "";
    private $publishable_key = "";
    private $url = "";

    function __construct()
    {
        $fn = new custom_functions();
        $settings = $fn->get_settings('payment_methods', true);

        $this->server_key = $settings['stripe_secret_key'];
        $this->publishable_key = $settings['stripe_publishable_key']; 
        $this->webhook_secret_key = $settings['stripe_webhook_secret_key'];
        $this->currency_code = strtolower($settings['stripe_currency_code']);
        $this->url = "https://api.stripe.com/";
    }
    public function get_credentials()
    {
        $data['server_key'] = $this->server_key;
        $data['publishable_key'] = $this->publishable_key;
        $data['webhook_key'] = $this->webhook_secret_key;
        $data['url'] = $this->url;
        return $data;
    }
    public function create_customer($customer_data)
    {
        $create_customer['name'] = $customer_data['name'];

        $create_customer['address']['line1'] = $customer_data['line1'];
        $create_customer['address']['postal_code'] = $customer_data['postal_code'];
        $create_customer['address']['city'] = $customer_data['city'];
        $url = $this->url . 'v1/customers';
        $method = 'POST';
        $response = $this->curl($url, $method, $create_customer);
        $res = json_decode($response['body'],true);
        return $res;
    }
    public function construct_event($request_body, $sigHeader, $secret,$tolerance = DEFAULT_TOLERANCE)
    {
        $explode_header = explode(",",$sigHeader);
        for($i = 0; $i<count($explode_header); $i++){
            $data[] = explode("=",$explode_header[$i]);
        }
        if(empty($data[0][1]) || $data[0][1] == "" || empty($data[1][1]) || $data[1][1] == ""){
            $response['error'] = true;
	        $response['message'] = "Unable to extract timestamp and signatures from header" ;
            return $response;
        }
        $timestamp = $data[0][1];
        $signs = $data[1][1];

        $signed_payload = "{$timestamp}.{$request_body}";
        $expectedSignature = hash_hmac('sha256', $signed_payload, $secret);        
        if($expectedSignature == $signs){
            if (($tolerance > 0) && (\abs(\time() - $timestamp) > $tolerance)) {
                $response['error'] = true;
                $response['message'] = "Timestamp outside the tolerance zone";
                return $response;
            }else{
                return "Matched";
            }
        }else{
            $response['error'] = true;
            $response['message'] = "No signatures found matching the expected signature for payload" ;
            return $response;
        }       
    }

    public function create_payment_intent($c_data)
    {
        $c_data['currency'] = $this->currency_code;
        $url = $this->url . 'v1/payment_intents';
        $method = 'POST';
        $response = $this->curl($url, $method,$c_data);
        $res = json_decode($response['body'],true);
        return $res;
    }

    public function curl($url, $method = 'GET', $data = [])
    {
        $ch = curl_init();
        $curl_options = array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_HEADER => 0,
            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/x-www-form-urlencoded',
                'Authorization: Basic ' . base64_encode($this->server_key . ':')
            )
        );
        if (strtolower($method) == 'post') {
            $curl_options[CURLOPT_POST] = 1;
            $curl_options[CURLOPT_POSTFIELDS] = http_build_query($data);
        } else {
            $curl_options[CURLOPT_CUSTOMREQUEST] = 'GET';
        }
        curl_setopt_array($ch, $curl_options);
        $result = array(
            'body' => curl_exec($ch),
            'http_code' => curl_getinfo($ch, CURLINFO_HTTP_CODE),
        );
        return $result;
    }
    public function add_wallet_balance($order_id,$user_id, $amount, $type,$message)
    {
        $data = array('add_wallet_balance' => '1', 'user_id' => $user_id,'order_id' => $order_id, 'amount' => $amount, 'type' => $type , 'message' => $message,'ajaxCall' => 1);
        $ch = curl_init();
      
        curl_setopt($ch, CURLOPT_URL, DOMAIN_URL . "api-firebase/get-user-transactions.php");
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch,CURLINFO_HEADER_OUT,true);
        $response = curl_exec($ch);
        curl_close($ch);
        return $response;
    }
}
