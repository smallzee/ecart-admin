<?php
include_once '../includes/crud.php';
include_once '../includes/custom-functions.php';
/* 
    1. get_credentials()
    2. create_transaction($order_id, $amount)
    3. get_transaction_status($order_id)
    4. curl($url, $method = 'GET', $data = [])
*/

class Midtrans
{
    private $server_key = "";
    private $client_key = "";
    private $is_production = false;
    private $url = "";

    function __construct()
    {
        $fn = new custom_functions();
        $settings = $fn->get_settings('payment_methods', true);

        $this->server_key = $settings['midtrans_server_key'];
        $this->client_key = $settings['midtrans_client_key'];
        $this->is_production = $settings['is_production'];
        $this->app_url = ($this->is_production) ? 'https://app.midtrans.com/' : 'https://app.sandbox.midtrans.com/';
        $this->api_url = ($this->is_production) ? 'https://api.midtrans.com/' : 'https://api.sandbox.midtrans.com/';
    }
    public function get_credentials()
    {
        $data['server_key'] = $this->server_key;
        $data['client_key'] = $this->client_key;
        $data['is_production'] = $this->is_production;
        $data['url'] = $this->url;
        return $data;
    }
    public function create_transaction($order_id, $amount)
    {
        $data['transaction_details']['order_id'] = $order_id;
        $data['transaction_details']['gross_amount'] = $amount;
        // $data['metadata']['user_id'] = $user_id;
        $data = json_encode($data);
        $url = $this->app_url . 'snap/v1/transactions/';
        $method = 'POST';
        $response = $this->curl($url, $method, $data);
        return $response;
    }

    public function get_transaction_status($order_id)
    {
        $url = $this->api_url . 'v2/' . $order_id . '/status';
        $response = $this->curl($url);
        return $response;
    }

    public function curl($url, $method = 'GET', $data = [])
    {
        $ch = curl_init();
        $curl_options = array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_HEADER => 0,
            // Add header to the request, including Authorization generated from server key
            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/json',
                'Accept: application/json',
                'Authorization: Basic ' . base64_encode($this->server_key . ':')
            )
        );
        if (strtolower($method) == 'post') {
            $curl_options[CURLOPT_POST] = 1;
            $curl_options[CURLOPT_POSTFIELDS] = $data;
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
    public function add_wallet_balance($order_id, $user_id, $amount, $type,$message)
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
