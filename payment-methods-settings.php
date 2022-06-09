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

include "header.php"; ?>
<html>

<head>
    <title>Payment Gateways & Payment Methods Settings | <?= $settings['app_name'] ?> - Dashboard</title>
</head>
</body>
<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <section class="content-header">

        <h2>Payment Gateways & Methods Settings</h2>
        <?php
        $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != "off" ? "https" : "http";
        $data = $fn->get_settings('payment_methods', true);
        ?>
        <ol class="breadcrumb">
            <li><a href="home.php"><i class="fa fa-home"></i> Home</a></li>
        </ol>
        <hr />
    </section>
    <?php if ($permissions['settings']['read'] == 1) { ?>
        <section class="content">
            <div class="row">
                <div class="col-md-12">
                    <!-- general form elements -->
                    <div class="box box-primary">
                        <div class="box-header with-border">
                            <h3 class="box-title">Payment Methods Settings</h3>
                        </div>
                        <!-- /.box-header -->
                        <!-- form start -->
                        <div class="box-body">
                            <div class="col-md-4">
                                <form method="post" id="payment_method_settings_form">
                                    <input type="hidden" id="payment_method_settings" name="payment_method_settings" required="" value="1" aria-required="true">
                                    <h5>COD Payments </h5>
                                    <hr>
                                    <div class="form-group">
                                        <label for="cod_payment_method">COD Payments <small>[ Enable / Disable ] </small></label><br>
                                        <input type="checkbox" id="cod_payment_method_btn" class="js-switch" <?php if (isset($data['cod_payment_method']) && !empty($data['cod_payment_method']) && $data['cod_payment_method'] == '1') {
                                                                                                                    echo 'checked';
                                                                                                                } ?>>
                                        <input type="hidden" id="cod_payment_method" name="cod_payment_method" value="<?= (isset($data['cod_payment_method']) && !empty($data['cod_payment_method'])) ? $data['cod_payment_method'] : 0; ?>">
                                    </div>
                                    <hr>
                                    <h5>Paypal Payments </h5>
                                    <hr>
                                    <div class="form-group">
                                        <label for="paypal_payment_method">Paypal Payments <small>[ Enable / Disable ] </small></label><br>
                                        <input type="checkbox" id="paypal_payment_method_btn" class="js-switch" <?= (!empty($data['paypal_payment_method']) && $data['paypal_payment_method'] == 1) ? 'checked' : ''; ?>>
                                        <input type="hidden" id="paypal_payment_method" name="paypal_payment_method" value="<?= (isset($data['paypal_payment_method']) && !empty($data['paypal_payment_method'])) ? $data['paypal_payment_method'] : 0; ?>">
                                    </div>
                                    <div class="form-group">
                                        <label for="">Payment Mode <small>[ sandbox / live ]</small></label>
                                        <select name="paypal_mode" class="form-control">
                                            <option value="">Select Mode </option>
                                            <option value="sandbox" <?= (isset($data['paypal_mode']) && $data['paypal_mode'] == 'sandbox') ? "selected" : "" ?>>Sandbox ( Testing )</option>
                                            <option value="production" <?= (isset($data['paypal_mode']) && $data['paypal_mode'] == 'production') ? "selected" : "" ?>>Production ( Live )</option>
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label for="">Currency Code <small>[ PayPal supported ]</small> <a href="https://developer.paypal.com/docs/api/reference/currency-codes/" target="_BLANK"><i class="fa fa-link"></i></a></label>
                                        <select name="paypal_currency_code" class="form-control">
                                            <option value="">Select Currency Code </option>
                                            <option value="INR" <?= (isset($data['paypal_currency_code']) && $data['paypal_currency_code'] == 'INR') ? "selected" : "" ?>>Indian rupee </option>
                                            <option value="AUD" <?= (isset($data['paypal_currency_code']) && $data['paypal_currency_code'] == 'AUD') ? "selected" : "" ?>>Australian dollar </option>
                                            <option value="BRL" <?= (isset($data['paypal_currency_code']) && $data['paypal_currency_code'] == 'BRL') ? "selected" : "" ?>>Brazilian real </option>
                                            <option value="CAD" <?= (isset($data['paypal_currency_code']) && $data['paypal_currency_code'] == 'CAD') ? "selected" : "" ?>>Canadian dollar </option>
                                            <option value="CNY" <?= (isset($data['paypal_currency_code']) && $data['paypal_currency_code'] == 'CNY') ? "selected" : "" ?>>Chinese Renmenbi </option>
                                            <option value="CZK" <?= (isset($data['paypal_currency_code']) && $data['paypal_currency_code'] == 'CZK') ? "selected" : "" ?>>Czech koruna </option>
                                            <option value="DKK" <?= (isset($data['paypal_currency_code']) && $data['paypal_currency_code'] == 'DKK') ? "selected" : "" ?>>Danish krone </option>
                                            <option value="EUR" <?= (isset($data['paypal_currency_code']) && $data['paypal_currency_code'] == 'EUR') ? "selected" : "" ?>>Euro </option>
                                            <option value="HKD" <?= (isset($data['paypal_currency_code']) && $data['paypal_currency_code'] == 'HKD') ? "selected" : "" ?>>Hong Kong dollar </option>
                                            <option value="HUF" <?= (isset($data['paypal_currency_code']) && $data['paypal_currency_code'] == 'HUF') ? "selected" : "" ?>>Hungarian forint </option>
                                            <option value="ILS" <?= (isset($data['paypal_currency_code']) && $data['paypal_currency_code'] == 'ILS') ? "selected" : "" ?>>Israeli new shekel </option>
                                            <option value="JPY" <?= (isset($data['paypal_currency_code']) && $data['paypal_currency_code'] == 'JPY') ? "selected" : "" ?>>Japanese yen </option>
                                            <option value="MYR" <?= (isset($data['paypal_currency_code']) && $data['paypal_currency_code'] == 'MYR') ? "selected" : "" ?>>Malaysian ringgit </option>
                                            <option value="MXN" <?= (isset($data['paypal_currency_code']) && $data['paypal_currency_code'] == 'MXN') ? "selected" : "" ?>>Mexican peso </option>
                                            <option value="TWD" <?= (isset($data['paypal_currency_code']) && $data['paypal_currency_code'] == 'TWD') ? "selected" : "" ?>>New Taiwan dollar </option>
                                            <option value="NZD" <?= (isset($data['paypal_currency_code']) && $data['paypal_currency_code'] == 'NZD') ? "selected" : "" ?>>New Zealand dollar </option>
                                            <option value="NOK" <?= (isset($data['paypal_currency_code']) && $data['paypal_currency_code'] == 'NOK') ? "selected" : "" ?>>Norwegian krone </option>
                                            <option value="PHP" <?= (isset($data['paypal_currency_code']) && $data['paypal_currency_code'] == 'PHP') ? "selected" : "" ?>>Philippine peso </option>
                                            <option value="PLN" <?= (isset($data['paypal_currency_code']) && $data['paypal_currency_code'] == 'PLN') ? "selected" : "" ?>>Polish złoty </option>
                                            <option value="GBP" <?= (isset($data['paypal_currency_code']) && $data['paypal_currency_code'] == 'GBP') ? "selected" : "" ?>>Pound sterling </option>
                                            <option value="RUB" <?= (isset($data['paypal_currency_code']) && $data['paypal_currency_code'] == 'RUB') ? "selected" : "" ?>>Russian ruble </option>
                                            <option value="SGD" <?= (isset($data['paypal_currency_code']) && $data['paypal_currency_code'] == 'SGD') ? "selected" : "" ?>>Singapore dollar </option>
                                            <option value="SEK" <?= (isset($data['paypal_currency_code']) && $data['paypal_currency_code'] == 'SEK') ? "selected" : "" ?>>Swedish krona </option>
                                            <option value="CHF" <?= (isset($data['paypal_currency_code']) && $data['paypal_currency_code'] == 'CHF') ? "selected" : "" ?>>Swiss franc </option>
                                            <option value="THB" <?= (isset($data['paypal_currency_code']) && $data['paypal_currency_code'] == 'THB') ? "selected" : "" ?>>Thai baht </option>
                                            <option value="USD" <?= (isset($data['paypal_currency_code']) && $data['paypal_currency_code'] == 'USD') ? "selected" : "" ?>>United States dollar </option>
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label for="paypal_business_email">Paypal Business Email</label>
                                        <input type="text" class="form-control" name="paypal_business_email" value="<?= (isset($data['paypal_business_email'])) ? $data['paypal_business_email'] : '' ?>" placeholder="Paypal Business Email" />
                                    </div>
                                    <div class="form-group">
                                        <label for="paypal_notification_url">Notification URL <small>(Set this as IPN notification URL in you PayPal account)</small></label>
                                        <input type="text" class="form-control" name="paypal_notification_url" value="<?= $protocol . "://" . $_SERVER['SERVER_NAME'] . "/paypal/ipn.php" ?>" placeholder="Paypal IPN notification URL" disabled />
                                    </div>
                                    <hr>
                                    <h5>PayUMoney Payments </h5>
                                    <hr>
                                    <div class="form-group">
                                        <label for="payumoney_payment_method">PayUMoney Payments <small>[ Enable / Disable ] </small></label><br>
                                        <input type="checkbox" id="payumoney_payment_method_btn" class="js-switch" <?= (isset($data['payumoney_payment_method']) && !empty($data['payumoney_payment_method']) && $data['payumoney_payment_method'] == '1') ? 'checked' : ""; ?>>
                                        <input type="hidden" id="payumoney_payment_method" name="payumoney_payment_method" value="<?= (isset($data['payumoney_payment_method']) && !empty($data['payumoney_payment_method'])) ? $data['payumoney_payment_method'] : 0; ?>">
                                    </div>
                                    <div class="form-group">
                                        <label for="">Payment Mode <small>[ sandbox / live ]</small></label>
                                        <select name="payumoney_mode" class="form-control">
                                            <option value="">Select Mode </option>
                                            <option value="sandbox" <?= (isset($data['payumoney_mode']) && $data['payumoney_mode'] == 'sandbox') ? "selected" : "" ?>>Sandbox ( Testing )</option>
                                            <option value="production" <?= (isset($data['payumoney_mode']) && $data['payumoney_mode'] == 'production') ? "selected" : "" ?>>Production ( Live )</option>
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label for="payumoney_merchant_key">Merchant key</label>
                                        <input type="text" class="form-control" name="payumoney_merchant_key" value="<?= (isset($data['payumoney_merchant_key'])) ? $data['payumoney_merchant_key'] : '' ?>" placeholder="PayUMoney Merchant Key" />
                                    </div>
                                    <div class="form-group">
                                        <label for="payumoney_merchant_id">Merchant ID</label>
                                        <input type="text" class="form-control" name="payumoney_merchant_id" value="<?= (isset($data['payumoney_merchant_id'])) ? $data['payumoney_merchant_id'] : '' ?>" placeholder="PayUMoney Merchant ID" />
                                    </div>
                                    <div class="form-group">
                                        <label for="payumoney_salt">Salt</label>
                                        <input type="text" class="form-control" name="payumoney_salt" value="<?= (isset($data['payumoney_salt'])) ? $data['payumoney_salt'] : '' ?>" placeholder="PayUMoney Merchant ID" />
                                    </div>
                                    <hr>
                                    <h5>Razorpay Payments </h5>
                                    <hr>
                                    <div class="form-group">
                                        <label for="razorpay_payment_method">Razorpay Payments <small>[ Enable / Disable ] </small></label><br>
                                        <input type="checkbox" id="razorpay_payment_method_btn" class="js-switch" <?= (isset($data['razorpay_payment_method']) && !empty($data['razorpay_payment_method']) && $data['razorpay_payment_method'] == '1') ? 'checked' : ""; ?>>
                                        <input type="hidden" id="razorpay_payment_method" name="razorpay_payment_method" value="<?= (isset($data['razorpay_payment_method']) && !empty($data['razorpay_payment_method'])) ? $data['razorpay_payment_method'] : 0; ?>">
                                    </div>
                                    <div class="form-group">
                                        <label for="razorpay_key">Razorpay key ID</label>
                                        <input type="text" class="form-control" name="razorpay_key" value="<?= (isset($data['razorpay_key'])) ? $data['razorpay_key'] : '' ?>" placeholder="Razor Key ID" />
                                    </div>
                                    <div class="form-group">
                                        <label for="razorpay_secret_key">Secret Key</label>
                                        <input type="text" class="form-control" name="razorpay_secret_key" value="<?= (isset($data['razorpay_secret_key'])) ? $data['razorpay_secret_key'] : '' ?>" placeholder="Razorpay Secret Key " />
                                    </div>
                                    <hr>
                                    <h5>Paystack Payments </h5>
                                    <hr>
                                    <div class="form-group">
                                        <label for="paystack_payment_method">Paystack Payments <small>[ Enable / Disable ] </small></label><br>
                                        <input type="checkbox" id="paystack_payment_method_btn" class="js-switch" <?= (isset($data['paystack_payment_method']) && !empty($data['paystack_payment_method']) && $data['paystack_payment_method'] == '1') ? 'checked' : ""; ?>>
                                        <input type="hidden" id="paystack_payment_method" name="paystack_payment_method" value="<?= (isset($data['paystack_payment_method']) && !empty($data['paystack_payment_method'])) ? $data['paystack_payment_method'] : 0; ?>">
                                    </div>
                                    <div class="form-group">
                                        <label for="paystack_public_key">Paystack Public key</label>
                                        <input type="text" class="form-control" name="paystack_public_key" value="<?= (isset($data['paystack_public_key'])) ? $data['paystack_public_key'] : '' ?>" placeholder="Paystack Public key" />
                                    </div>
                                    <div class="form-group">
                                        <label for="paystack_secret_key">Paystack Secret Key</label>
                                        <input type="text" class="form-control" name="paystack_secret_key" value="<?= (isset($data['paystack_secret_key'])) ? $data['paystack_secret_key'] : '' ?>" placeholder="Paystack Secret Key " />
                                    </div>
                                    <hr>
                                    <h5>Flutterwave Payments </h5>
                                    <hr>
                                    <div class="form-group">
                                        <label for="flutterwave_payment_method">Flutterwave Payments <small>[ Enable / Disable ] </small></label><br>
                                        <input type="checkbox" id="flutterwave_payment_method_btn" class="js-switch" <?= (isset($data['flutterwave_payment_method']) && !empty($data['flutterwave_payment_method']) && $data['flutterwave_payment_method'] == '1') ? 'checked' : ''; ?>>
                                        <input type="hidden" id="flutterwave_payment_method" name="flutterwave_payment_method" value="<?= (isset($data['flutterwave_payment_method']) && !empty($data['flutterwave_payment_method'])) ? $data['flutterwave_payment_method'] : 0; ?>">
                                    </div>
                                    <div class="form-group">
                                        <label for="flutterwave_public_key">Flutterwave Public key</label>
                                        <input type="text" class="form-control" name="flutterwave_public_key" value="<?= (isset($data['flutterwave_public_key'])) ? $data['flutterwave_public_key'] : '' ?>" placeholder="Flutterwave Public key" />
                                    </div>
                                    <div class="form-group">
                                        <label for="flutterwave_secret_key">Flutterwave Secret Key</label>
                                        <input type="text" class="form-control" name="flutterwave_secret_key" value="<?= (isset($data['flutterwave_secret_key'])) ? $data['flutterwave_secret_key'] : '' ?>" placeholder="Flutterwave Secret Key " />
                                    </div>
                                    <div class="form-group">
                                        <label for="flutterwave_encryption_key">Flutterwave Encryption key</label>
                                        <input type="text" class="form-control" name="flutterwave_encryption_key" value="<?= (isset($data['flutterwave_encryption_key'])) ? $data['flutterwave_encryption_key'] : '' ?>" placeholder="Flutterwave Encryption key" />
                                    </div>
                                    <div class="form-group">
                                        <label for="">Currency Code <small>[ Flutterwave supported ]</small> </label>
                                        <select name="flutterwave_currency_code" class="form-control">
                                            <option value="">Select Currency Code </option>
                                            <option value="NGN" <?= (isset($data['flutterwave_currency_code']) && $data['flutterwave_currency_code'] == 'NGN') ? "selected" : "" ?>>Nigerian Naira</option>
                                            <option value="USD" <?= (isset($data['flutterwave_currency_code']) && $data['flutterwave_currency_code'] == 'USD') ? "selected" : "" ?>>United States dollar</option>
                                            <option value="TZS" <?= (isset($data['flutterwave_currency_code']) && $data['flutterwave_currency_code'] == 'TZS') ? "selected" : "" ?>>Tanzanian Shilling</option>
                                            <option value="SLL" <?= (isset($data['flutterwave_currency_code']) && $data['flutterwave_currency_code'] == 'SLL') ? "selected" : "" ?>>Sierra Leonean Leone</option>
                                            <option value="MUR" <?= (isset($data['flutterwave_currency_code']) && $data['flutterwave_currency_code'] == 'MUR') ? "selected" : "" ?>>Mauritian Rupee</option>
                                            <option value="MWK" <?= (isset($data['flutterwave_currency_code']) && $data['flutterwave_currency_code'] == 'MWK') ? "selected" : "" ?>>Malawian Kwacha </option>
                                            <option value="GBP" <?= (isset($data['flutterwave_currency_code']) && $data['flutterwave_currency_code'] == 'GBP') ? "selected" : "" ?>>UK Bank Accounts</option>
                                            <option value="GHS" <?= (isset($data['flutterwave_currency_code']) && $data['flutterwave_currency_code'] == 'GHS') ? "selected" : "" ?>>Ghanaian Cedi</option>
                                            <option value="RWF" <?= (isset($data['flutterwave_currency_code']) && $data['flutterwave_currency_code'] == 'RWF') ? "selected" : "" ?>>Rwandan franc</option>
                                            <option value="UGX" <?= (isset($data['flutterwave_currency_code']) && $data['flutterwave_currency_code'] == 'UGX') ? "selected" : "" ?>>Ugandan Shilling</option>
                                            <option value="ZMW" <?= (isset($data['flutterwave_currency_code']) && $data['flutterwave_currency_code'] == 'ZMW') ? "selected" : "" ?>>Zambian Kwacha</option>
                                            <option value="KES" <?= (isset($data['flutterwave_currency_code']) && $data['flutterwave_currency_code'] == 'KES') ? "selected" : "" ?>>Mpesa</option>
                                            <option value="ZAR" <?= (isset($data['flutterwave_currency_code']) && $data['flutterwave_currency_code'] == 'ZAR') ? "selected" : "" ?>>South African Rand</option>
                                            <option value="XAF" <?= (isset($data['flutterwave_currency_code']) && $data['flutterwave_currency_code'] == 'XAF') ? "selected" : "" ?>>Central African CFA franc</option>
                                            <option value="XOF" <?= (isset($data['flutterwave_currency_code']) && $data['flutterwave_currency_code'] == 'XAF') ? "selected" : "" ?>>West African CFA franc</option>
                                            <option value="AUD" <?= (isset($data['flutterwave_currency_code']) && $data['flutterwave_currency_code'] == 'AUD') ? "selected" : "" ?>>Australian Dollar</option>
                                        </select>
                                    </div>
                                    <hr>
                                    <h5>Midtrans Payments </h5>
                                    <hr>
                                    <div class="form-group">
                                        <label for="midtrans_payment_method">Midtrans Payments <small>[ Enable / Disable ] </small></label><br>
                                        <input type="checkbox" id="midtrans_payment_method_btn" class="js-switch" <?= (isset($data['midtrans_payment_method']) && !empty($data['midtrans_payment_method']) && $data['midtrans_payment_method'] == '1') ? 'checked' : ''; ?>>
                                        <input type="hidden" id="midtrans_payment_method" name="midtrans_payment_method" value="<?= (isset($data['midtrans_payment_method']) && !empty($data['midtrans_payment_method'])) ? $data['midtrans_payment_method'] : 0; ?>">
                                    </div>
                                    <div class="form-group">
                                        <label for="">Payment Mode <small>[ sandbox / live ]</small></label>
                                        <select name="is_production" class="form-control">
                                            <option value="">Select Mode </option>
                                            <option value="1" <?= (isset($data['is_production']) && $data['is_production'] == '1') ? "selected" : "" ?>>Production ( Live )</option>
                                            <option value="0" <?= (isset($data['is_production']) && $data['is_production'] == '0') ? "selected" : "" ?>>Sandbox ( Testing )</option>
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label for="midtrans_merchant_id">Midtrans Merchant ID</label>
                                        <input type="text" class="form-control" name="midtrans_merchant_id" value="<?= (isset($data['midtrans_merchant_id'])) ? $data['midtrans_merchant_id'] : '' ?>" placeholder="Midtrans Merchant ID" />
                                    </div>
                                    <div class="form-group">
                                        <label for="midtrans_client_key">Midtrans Client Key</label>
                                        <input type="text" class="form-control" name="midtrans_client_key" value="<?= (isset($data['midtrans_client_key'])) ? $data['midtrans_client_key'] : '' ?>" placeholder="Midtrans Clients Key " />
                                    </div>
                                    <div class="form-group">
                                        <label for="midtrans_server_key">Midtrans Server Key</label>
                                        <input type="text" class="form-control" name="midtrans_server_key" value="<?= (isset($data['midtrans_server_key'])) ? $data['midtrans_server_key'] : '' ?>" placeholder="Midtrans Server key" />
                                    </div>
                                    <div class="form-group">
                                        <label for="paypal_notification_url">Notification URL <small>(Set this as Webhook URL in your Midtrans account)</small></label>
                                        <input type="text" class="form-control" name="midtrans_notification_url" value="<?= DOMAIN_URL . "midtrans/notification-handler.php" ?>" placeholder="Midtrans Webhook URL" disabled />
                                    </div>
                                    <div class="form-group">
                                        <label for="paypal_notification_url">Payment Return URL <small>(Set this as Finish URL in your Midtrans account)</small></label>
                                        <input type="text" class="form-control" name="midtrans_return_url" value="<?= DOMAIN_URL . "midtrans/payment-process.php" ?>" placeholder="Midtrans return URL" disabled />
                                    </div>
                                    <hr>
                                    <h5>Stripe Payments </h5>
                                    <hr>
                                    <div class="form-group">
                                        <label for="stripe_payment_method">Stripe Payments <small>[ Enable / Disable ] </small></label><br>
                                        <input type="checkbox" id="stripe_payment_method_btn" class="js-switch" <?= (isset($data['stripe_payment_method']) && !empty($data['stripe_payment_method']) && $data['stripe_payment_method'] == '1') ? 'checked' : ""; ?>>
                                        <input type="hidden" id="stripe_payment_method" name="stripe_payment_method" value="<?= (isset($data['stripe_payment_method']) && !empty($data['stripe_payment_method'])) ? $data['stripe_payment_method'] : 0; ?>">
                                    </div>
                                    <div class="form-group">
                                        <label for="stripe_publishable_key">Stripe Publishable Key</label>
                                        <input type="text" class="form-control" name="stripe_publishable_key" value="<?= (isset($data['stripe_publishable_key'])) ? $data['stripe_publishable_key'] : '' ?>" placeholder="Stripe Publishable Key" />
                                    </div>
                                    <div class="form-group">
                                        <label for="stripe_secret_key">Stripe Secret Key</label>
                                        <input type="text" class="form-control" name="stripe_secret_key" value="<?= (isset($data['stripe_secret_key'])) ? $data['stripe_secret_key'] : '' ?>" placeholder="Stripe Secret Key " />
                                    </div>
                                    <div class="form-group">
                                        <label for="stripe_webhook_secret_key">Stripe Webhook Secret Key</label>
                                        <input type="text" class="form-control" name="stripe_webhook_secret_key" value="<?= (isset($data['stripe_webhook_secret_key'])) ? $data['stripe_webhook_secret_key'] : '' ?>" placeholder="Stripe Webhook Secret Key " />
                                    </div>
                                    <div class="form-group">
                                        <label for="paypal_notification_url">Payment Endpoint URL <small>(Set this as Endpoint URL in your Stripe account)</small></label>
                                        <input type="text" class="form-control" name="stripe_webhook_url" value="<?= DOMAIN_URL . "stripe/webhook.php" ?>" disabled />
                                    </div>
                                    <div class="form-group">
                                        <label for="">Currency Code <small>[ Stripe supported ]</small> <a href="https://stripe.com/docs/currencies" target="_BLANK"><i class="fa fa-link"></i></a></label>
                                        <select name="stripe_currency_code" class="form-control">
                                            <option value="">Select Currency Code </option>
                                            <option value="INR" <?= (isset($data['stripe_currency_code']) && $data['stripe_currency_code'] == 'INR') ? "selected" : "" ?>>Indian rupee </option>
                                            <option value="USD" <?= (isset($data['stripe_currency_code']) && $data['stripe_currency_code'] == 'USD') ? "selected" : "" ?>>United States dollar </option>
                                            <option value="AED" <?= (isset($data['stripe_currency_code']) && $data['stripe_currency_code'] == 'AED') ? "selected" : "" ?>>United Arab Emirates Dirham </option>
                                            <option value="AFN" <?= (isset($data['stripe_currency_code']) && $data['stripe_currency_code'] == 'AFN') ? "selected" : "" ?>>Afghan Afghani </option>
                                            <option value="ALL" <?= (isset($data['stripe_currency_code']) && $data['stripe_currency_code'] == 'ALL') ? "selected" : "" ?>>Albanian Lek </option>
                                            <option value="AMD" <?= (isset($data['stripe_currency_code']) && $data['stripe_currency_code'] == 'AMD') ? "selected" : "" ?>>Armenian Dram </option>
                                            <option value="ANG" <?= (isset($data['stripe_currency_code']) && $data['stripe_currency_code'] == 'ANG') ? "selected" : "" ?>>Netherlands Antillean Guilder </option>
                                            <option value="AOA" <?= (isset($data['stripe_currency_code']) && $data['stripe_currency_code'] == 'AOA') ? "selected" : "" ?>>Angolan Kwanza </option>
                                            <option value="ARS" <?= (isset($data['stripe_currency_code']) && $data['stripe_currency_code'] == 'ARS') ? "selected" : "" ?>>Argentine Peso</option>
                                            <option value="AUD" <?= (isset($data['stripe_currency_code']) && $data['stripe_currency_code'] == 'AUD') ? "selected" : "" ?>> Australian Dollar</option>
                                            <option value="AWG" <?= (isset($data['stripe_currency_code']) && $data['stripe_currency_code'] == 'AWG') ? "selected" : "" ?>> Aruban Florin</option>
                                            <option value="AZN" <?= (isset($data['stripe_currency_code']) && $data['stripe_currency_code'] == 'AZN') ? "selected" : "" ?>> Azerbaijani Manat </option>
                                            <option value="BAM" <?= (isset($data['stripe_currency_code']) && $data['stripe_currency_code'] == 'BAM') ? "selected" : "" ?>> Bosnia-Herzegovina Convertible Mark </option>
                                            <option value="BBD" <?= (isset($data['stripe_currency_code']) && $data['stripe_currency_code'] == 'BBD') ? "selected" : "" ?>> Bajan dollar </option>
                                            <option value="BDT" <?= (isset($data['stripe_currency_code']) && $data['stripe_currency_code'] == 'BDT') ? "selected" : "" ?>> Bangladeshi Taka</option>
                                            <option value="BGN" <?= (isset($data['stripe_currency_code']) && $data['stripe_currency_code'] == 'BGN') ? "selected" : "" ?>> Bulgarian Lev </option>
                                            <option value="BIF" <?= (isset($data['stripe_currency_code']) && $data['stripe_currency_code'] == 'BIF') ? "selected" : "" ?>>Burundian Franc</option>
                                            <option value="BMD" <?= (isset($data['stripe_currency_code']) && $data['stripe_currency_code'] == 'BMD') ? "selected" : "" ?>> Bermudan Dollar</option>
                                            <option value="BND" <?= (isset($data['stripe_currency_code']) && $data['stripe_currency_code'] == 'BND') ? "selected" : "" ?>> Brunei Dollar </option>
                                            <option value="BOB" <?= (isset($data['stripe_currency_code']) && $data['stripe_currency_code'] == 'BOB') ? "selected" : "" ?>> Bolivian Boliviano </option>
                                            <option value="BRL" <?= (isset($data['stripe_currency_code']) && $data['stripe_currency_code'] == 'BRL') ? "selected" : "" ?>> Brazilian Real </option>
                                            <option value="BSD" <?= (isset($data['stripe_currency_code']) && $data['stripe_currency_code'] == 'BSD') ? "selected" : "" ?>> Bahamian Dollar </option>
                                            <option value="BWP" <?= (isset($data['stripe_currency_code']) && $data['stripe_currency_code'] == 'BWP') ? "selected" : "" ?>> Botswanan Pula </option>
                                            <option value="BZD" <?= (isset($data['stripe_currency_code']) && $data['stripe_currency_code'] == 'BZD') ? "selected" : "" ?>> Belize Dollar </option>
                                            <option value="CAD" <?= (isset($data['stripe_currency_code']) && $data['stripe_currency_code'] == 'CAD') ? "selected" : "" ?>> Canadian Dollar </option>
                                            <option value="CDF" <?= (isset($data['stripe_currency_code']) && $data['stripe_currency_code'] == 'CDF') ? "selected" : "" ?>> Congolese Franc </option>
                                            <option value="CHF" <?= (isset($data['stripe_currency_code']) && $data['stripe_currency_code'] == 'CHF') ? "selected" : "" ?>> Swiss Franc </option>
                                            <option value="CLP" <?= (isset($data['stripe_currency_code']) && $data['stripe_currency_code'] == 'CLP') ? "selected" : "" ?>> Chilean Peso </option>
                                            <option value="CNY" <?= (isset($data['stripe_currency_code']) && $data['stripe_currency_code'] == 'CNY') ? "selected" : "" ?>> Chinese Yuan </option>
                                            <option value="COP" <?= (isset($data['stripe_currency_code']) && $data['stripe_currency_code'] == 'COP') ? "selected" : "" ?>> Colombian Peso </option>
                                            <option value="CRC" <?= (isset($data['stripe_currency_code']) && $data['stripe_currency_code'] == 'CRC') ? "selected" : "" ?>> Costa Rican Colón </option>
                                            <option value="CVE" <?= (isset($data['stripe_currency_code']) && $data['stripe_currency_code'] == 'CVE') ? "selected" : "" ?>> Cape Verdean Escudo </option>
                                            <option value="CZK" <?= (isset($data['stripe_currency_code']) && $data['stripe_currency_code'] == 'CZK') ? "selected" : "" ?>> Czech Koruna </option>
                                            <option value="DJF" <?= (isset($data['stripe_currency_code']) && $data['stripe_currency_code'] == 'DJF') ? "selected" : "" ?>> Djiboutian Franc </option>
                                            <option value="DKK" <?= (isset($data['stripe_currency_code']) && $data['stripe_currency_code'] == 'DKK') ? "selected" : "" ?>> Danish Krone </option>
                                            <option value="DOP" <?= (isset($data['stripe_currency_code']) && $data['stripe_currency_code'] == 'DOP') ? "selected" : "" ?>> Dominican Peso </option>
                                            <option value="DZD" <?= (isset($data['stripe_currency_code']) && $data['stripe_currency_code'] == 'DZD') ? "selected" : "" ?>> Algerian Dinar </option>
                                            <option value="EGP" <?= (isset($data['stripe_currency_code']) && $data['stripe_currency_code'] == 'EGP') ? "selected" : "" ?>> Egyptian Pound </option>
                                            <option value="ETB" <?= (isset($data['stripe_currency_code']) && $data['stripe_currency_code'] == 'ETB') ? "selected" : "" ?>> Ethiopian Birr </option>
                                            <option value="EUR" <?= (isset($data['stripe_currency_code']) && $data['stripe_currency_code'] == 'EUR') ? "selected" : "" ?>> Euro </option>
                                            <option value="FJD" <?= (isset($data['stripe_currency_code']) && $data['stripe_currency_code'] == 'FJD') ? "selected" : "" ?>> Fijian Dollar </option>
                                            <option value="FKP" <?= (isset($data['stripe_currency_code']) && $data['stripe_currency_code'] == 'FKP') ? "selected" : "" ?>> Falkland Island Pound </option>
                                            <option value="GBP" <?= (isset($data['stripe_currency_code']) && $data['stripe_currency_code'] == 'GBP') ? "selected" : "" ?>> Pound sterling </option>
                                            <option value="GEL" <?= (isset($data['stripe_currency_code']) && $data['stripe_currency_code'] == 'GEL') ? "selected" : "" ?>> Georgian Lari </option>
                                            <option value="GIP" <?= (isset($data['stripe_currency_code']) && $data['stripe_currency_code'] == 'GIP') ? "selected" : "" ?>> Gibraltar Pound </option>
                                            <option value="GMD" <?= (isset($data['stripe_currency_code']) && $data['stripe_currency_code'] == 'GMD') ? "selected" : "" ?>> Gambian dalasi </option>
                                            <option value="GNF" <?= (isset($data['stripe_currency_code']) && $data['stripe_currency_code'] == 'GNF') ? "selected" : "" ?>> Guinean Franc </option>
                                            <option value="GTQ" <?= (isset($data['stripe_currency_code']) && $data['stripe_currency_code'] == 'GTQ') ? "selected" : "" ?>> Guatemalan Quetzal </option>
                                            <option value="GYD" <?= (isset($data['stripe_currency_code']) && $data['stripe_currency_code'] == 'GYD') ? "selected" : "" ?>> Guyanaese Dollar </option>
                                            <option value="HKD" <?= (isset($data['stripe_currency_code']) && $data['stripe_currency_code'] == 'HKD') ? "selected" : "" ?>> Hong Kong Dollar </option>
                                            <option value="HNL" <?= (isset($data['stripe_currency_code']) && $data['stripe_currency_code'] == 'HNL') ? "selected" : "" ?>> Honduran Lempira </option>
                                            <option value="HRK" <?= (isset($data['stripe_currency_code']) && $data['stripe_currency_code'] == 'HRK') ? "selected" : "" ?>> Croatian Kuna </option>
                                            <option value="HTG" <?= (isset($data['stripe_currency_code']) && $data['stripe_currency_code'] == 'HTG') ? "selected" : "" ?>> Haitian Gourde </option>
                                            <option value="HUF" <?= (isset($data['stripe_currency_code']) && $data['stripe_currency_code'] == 'HUF') ? "selected" : "" ?>> Hungarian Forint </option>
                                            <option value="IDR" <?= (isset($data['stripe_currency_code']) && $data['stripe_currency_code'] == 'IDR') ? "selected" : "" ?>> Indonesian Rupiah </option>
                                            <option value="ILS" <?= (isset($data['stripe_currency_code']) && $data['stripe_currency_code'] == 'ILS') ? "selected" : "" ?>> Israeli New Shekel </option>
                                            <option value="ISK" <?= (isset($data['stripe_currency_code']) && $data['stripe_currency_code'] == 'ISK') ? "selected" : "" ?>> Icelandic Króna </option>
                                            <option value="JMD" <?= (isset($data['stripe_currency_code']) && $data['stripe_currency_code'] == 'JMD') ? "selected" : "" ?>> Jamaican Dollar </option>
                                            <option value="JPY" <?= (isset($data['stripe_currency_code']) && $data['stripe_currency_code'] == 'JPY') ? "selected" : "" ?>> Japanese Yen </option>
                                            <option value="KES" <?= (isset($data['stripe_currency_code']) && $data['stripe_currency_code'] == 'KES') ? "selected" : "" ?>> Kenyan Shilling </option>
                                            <option value="KGS" <?= (isset($data['stripe_currency_code']) && $data['stripe_currency_code'] == 'KGS') ? "selected" : "" ?>> Kyrgystani Som </option>
                                            <option value="KHR" <?= (isset($data['stripe_currency_code']) && $data['stripe_currency_code'] == 'KHR') ? "selected" : "" ?>> Cambodian riel </option>
                                            <option value="KMF" <?= (isset($data['stripe_currency_code']) && $data['stripe_currency_code'] == 'KMF') ? "selected" : "" ?>> Comorian franc </option>
                                            <option value="KRW" <?= (isset($data['stripe_currency_code']) && $data['stripe_currency_code'] == 'KRW') ? "selected" : "" ?>> South Korean won </option>
                                            <option value="KYD" <?= (isset($data['stripe_currency_code']) && $data['stripe_currency_code'] == 'KYD') ? "selected" : "" ?>> Cayman Islands Dollar </option>
                                            <option value="KZT" <?= (isset($data['stripe_currency_code']) && $data['stripe_currency_code'] == 'KZT') ? "selected" : "" ?>> Kazakhstani Tenge </option>
                                            <option value="LAK" <?= (isset($data['stripe_currency_code']) && $data['stripe_currency_code'] == 'LAK') ? "selected" : "" ?>> Laotian Kip </option>
                                            <option value="LBP" <?= (isset($data['stripe_currency_code']) && $data['stripe_currency_code'] == 'LBP') ? "selected" : "" ?>> Lebanese pound </option>
                                            <option value="LKR" <?= (isset($data['stripe_currency_code']) && $data['stripe_currency_code'] == 'LKR') ? "selected" : "" ?>> Sri Lankan Rupee </option>
                                            <option value="LRD" <?= (isset($data['stripe_currency_code']) && $data['stripe_currency_code'] == 'LRD') ? "selected" : "" ?>> Liberian Dollar </option>
                                            <option value="LSL" <?= (isset($data['stripe_currency_code']) && $data['stripe_currency_code'] == 'LSL') ? "selected" : "" ?>>Lesotho loti </option>
                                            <option value="MAD" <?= (isset($data['stripe_currency_code']) && $data['stripe_currency_code'] == 'MAD') ? "selected" : "" ?>> Moroccan Dirham </option>
                                            <option value="MDL" <?= (isset($data['stripe_currency_code']) && $data['stripe_currency_code'] == 'MDL') ? "selected" : "" ?>> Moldovan Leu </option>
                                            <option value="MGA" <?= (isset($data['stripe_currency_code']) && $data['stripe_currency_code'] == 'MGA') ? "selected" : "" ?>> Malagasy Ariary </option>
                                            <option value="MKD" <?= (isset($data['stripe_currency_code']) && $data['stripe_currency_code'] == 'MKD') ? "selected" : "" ?>> Macedonian Denar </option>
                                            <option value="MMK" <?= (isset($data['stripe_currency_code']) && $data['stripe_currency_code'] == 'MMK') ? "selected" : "" ?>> Myanmar Kyat </option>
                                            <option value="MNT" <?= (isset($data['stripe_currency_code']) && $data['stripe_currency_code'] == 'MNT') ? "selected" : "" ?>> Mongolian Tugrik </option>
                                            <option value="MOP" <?= (isset($data['stripe_currency_code']) && $data['stripe_currency_code'] == 'MOP') ? "selected" : "" ?>> Macanese Pataca </option>
                                            <option value="MRO" <?= (isset($data['stripe_currency_code']) && $data['stripe_currency_code'] == 'MRO') ? "selected" : "" ?>> Mauritanian Ouguiya </option>
                                            <option value="MUR" <?= (isset($data['stripe_currency_code']) && $data['stripe_currency_code'] == 'MUR') ? "selected" : "" ?>> Mauritian Rupee</option>
                                            <option value="MVR" <?= (isset($data['stripe_currency_code']) && $data['stripe_currency_code'] == 'MVR') ? "selected" : "" ?>> Maldivian Rufiyaa </option>
                                            <option value="MWK" <?= (isset($data['stripe_currency_code']) && $data['stripe_currency_code'] == 'MWK') ? "selected" : "" ?>> Malawian Kwacha </option>
                                            <option value="MXN" <?= (isset($data['stripe_currency_code']) && $data['stripe_currency_code'] == 'MXN') ? "selected" : "" ?>> Mexican Peso </option>
                                            <option value="MYR" <?= (isset($data['stripe_currency_code']) && $data['stripe_currency_code'] == 'MYR') ? "selected" : "" ?>> Malaysian Ringgit </option>
                                            <option value="MZN" <?= (isset($data['stripe_currency_code']) && $data['stripe_currency_code'] == 'MZN') ? "selected" : "" ?>> Mozambican metical </option>
                                            <option value="NAD" <?= (isset($data['stripe_currency_code']) && $data['stripe_currency_code'] == 'NAD') ? "selected" : "" ?>> Namibian dollar </option>
                                            <option value="NGN" <?= (isset($data['stripe_currency_code']) && $data['stripe_currency_code'] == 'NGN') ? "selected" : "" ?>> Nigerian Naira </option>
                                            <option value="NIO" <?= (isset($data['stripe_currency_code']) && $data['stripe_currency_code'] == 'NIO') ? "selected" : "" ?>>Nicaraguan Córdoba </option>
                                            <option value="NOK" <?= (isset($data['stripe_currency_code']) && $data['stripe_currency_code'] == 'NOK') ? "selected" : "" ?>> Norwegian Krone </option>
                                            <option value="NPR" <?= (isset($data['stripe_currency_code']) && $data['stripe_currency_code'] == 'NPR') ? "selected" : "" ?>> Nepalese Rupee </option>
                                            <option value="NZD" <?= (isset($data['stripe_currency_code']) && $data['stripe_currency_code'] == 'NZD') ? "selected" : "" ?>> New Zealand Dollar </option>
                                            <option value="PAB" <?= (isset($data['stripe_currency_code']) && $data['stripe_currency_code'] == 'PAB') ? "selected" : "" ?>> Panamanian Balboa </option>
                                            <option value="PEN" <?= (isset($data['stripe_currency_code']) && $data['stripe_currency_code'] == 'PEN') ? "selected" : "" ?>> Sol </option>
                                            <option value="PGK" <?= (isset($data['stripe_currency_code']) && $data['stripe_currency_code'] == 'PGK') ? "selected" : "" ?>> Papua New Guinean Kina </option>
                                            <option value="PHP" <?= (isset($data['stripe_currency_code']) && $data['stripe_currency_code'] == 'PHP') ? "selected" : "" ?>>Philippine peso </option>
                                            <option value="PKR" <?= (isset($data['stripe_currency_code']) && $data['stripe_currency_code'] == 'PKR') ? "selected" : "" ?>> Pakistani Rupee </option>
                                            <option value="PLN" <?= (isset($data['stripe_currency_code']) && $data['stripe_currency_code'] == 'PLN') ? "selected" : "" ?>> Poland złoty </option>
                                            <option value="PYG" <?= (isset($data['stripe_currency_code']) && $data['stripe_currency_code'] == 'PYG') ? "selected" : "" ?>> Paraguayan Guarani </option>
                                            <option value="QAR" <?= (isset($data['stripe_currency_code']) && $data['stripe_currency_code'] == 'QAR') ? "selected" : "" ?>> Qatari Rial </option>
                                            <option value="RON" <?= (isset($data['stripe_currency_code']) && $data['stripe_currency_code'] == 'RON') ? "selected" : "" ?>>Romanian Leu </option>
                                            <option value="RSD" <?= (isset($data['stripe_currency_code']) && $data['stripe_currency_code'] == 'RSD') ? "selected" : "" ?>> Serbian Dinar </option>
                                            <option value="RUB" <?= (isset($data['stripe_currency_code']) && $data['stripe_currency_code'] == 'RUB') ? "selected" : "" ?>> Russian Ruble </option>
                                            <option value="RWF" <?= (isset($data['stripe_currency_code']) && $data['stripe_currency_code'] == 'RWF') ? "selected" : "" ?>> Rwandan franc </option>
                                            <option value="SAR" <?= (isset($data['stripe_currency_code']) && $data['stripe_currency_code'] == 'SAR') ? "selected" : "" ?>> Saudi Riyal </option>
                                            <option value="SBD" <?= (isset($data['stripe_currency_code']) && $data['stripe_currency_code'] == 'SBD') ? "selected" : "" ?>> Solomon Islands Dollar </option>
                                            <option value="SCR" <?= (isset($data['stripe_currency_code']) && $data['stripe_currency_code'] == 'SCR') ? "selected" : "" ?>>Seychellois Rupee </option>
                                            <option value="SEK" <?= (isset($data['stripe_currency_code']) && $data['stripe_currency_code'] == 'SEK') ? "selected" : "" ?>> Swedish Krona </option>
                                            <option value="SGD" <?= (isset($data['stripe_currency_code']) && $data['stripe_currency_code'] == 'SGD') ? "selected" : "" ?>> Singapore Dollar </option>
                                            <option value="SHP" <?= (isset($data['stripe_currency_code']) && $data['stripe_currency_code'] == 'SHP') ? "selected" : "" ?>> Saint Helenian Pound </option>
                                            <option value="SLL" <?= (isset($data['stripe_currency_code']) && $data['stripe_currency_code'] == 'SLL') ? "selected" : "" ?>> Sierra Leonean Leone </option>
                                            <option value="SOS" <?= (isset($data['stripe_currency_code']) && $data['stripe_currency_code'] == 'SOS') ? "selected" : "" ?>>Somali Shilling </option>
                                            <option value="SRD" <?= (isset($data['stripe_currency_code']) && $data['stripe_currency_code'] == 'SRD') ? "selected" : "" ?>> Surinamese Dollar </option>
                                            <option value="STD" <?= (isset($data['stripe_currency_code']) && $data['stripe_currency_code'] == 'STD') ? "selected" : "" ?>> Sao Tome Dobra </option>
                                            <option value="SZL" <?= (isset($data['stripe_currency_code']) && $data['stripe_currency_code'] == 'SZL') ? "selected" : "" ?>> Swazi Lilangeni </option>
                                            <option value="THB" <?= (isset($data['stripe_currency_code']) && $data['stripe_currency_code'] == 'THB') ? "selected" : "" ?>> Thai Baht </option>
                                            <option value="TJS" <?= (isset($data['stripe_currency_code']) && $data['stripe_currency_code'] == 'TJS') ? "selected" : "" ?>> Tajikistani Somoni </option>
                                            <option value="TOP" <?= (isset($data['stripe_currency_code']) && $data['stripe_currency_code'] == 'TOP') ? "selected" : "" ?>> Tongan Paʻanga </option>
                                            <option value="TRY" <?= (isset($data['stripe_currency_code']) && $data['stripe_currency_code'] == 'TRY') ? "selected" : "" ?>> Turkish lira </option>
                                            <option value="TTD" <?= (isset($data['stripe_currency_code']) && $data['stripe_currency_code'] == 'TTD') ? "selected" : "" ?>> Trinidad & Tobago Dollar </option>
                                            <option value="TWD" <?= (isset($data['stripe_currency_code']) && $data['stripe_currency_code'] == 'TWD') ? "selected" : "" ?>> New Taiwan dollar </option>
                                            <option value="TZS" <?= (isset($data['stripe_currency_code']) && $data['stripe_currency_code'] == 'TZS') ? "selected" : "" ?>> Tanzanian Shilling </option>
                                            <option value="UAH" <?= (isset($data['stripe_currency_code']) && $data['stripe_currency_code'] == 'UAH') ? "selected" : "" ?>> Ukrainian hryvnia </option>
                                            <option value="UGX" <?= (isset($data['stripe_currency_code']) && $data['stripe_currency_code'] == 'UGX') ? "selected" : "" ?>> Ugandan Shilling </option>
                                            <option value="UYU" <?= (isset($data['stripe_currency_code']) && $data['stripe_currency_code'] == 'UYU') ? "selected" : "" ?>> Uruguayan Peso </option>
                                            <option value="UZS" <?= (isset($data['stripe_currency_code']) && $data['stripe_currency_code'] == 'UZS') ? "selected" : "" ?>> Uzbekistani Som </option>
                                            <option value="VND" <?= (isset($data['stripe_currency_code']) && $data['stripe_currency_code'] == 'VND') ? "selected" : "" ?>> Vietnamese dong </option>
                                            <option value="VUV" <?= (isset($data['stripe_currency_code']) && $data['stripe_currency_code'] == 'VUV') ? "selected" : "" ?>> Vanuatu Vatu </option>
                                            <option value="WST" <?= (isset($data['stripe_currency_code']) && $data['stripe_currency_code'] == 'WST') ? "selected" : "" ?>> Samoa Tala</option>
                                            <option value="XAF" <?= (isset($data['stripe_currency_code']) && $data['stripe_currency_code'] == 'XAF') ? "selected" : "" ?>> Central African CFA franc </option>
                                            <option value="XCD" <?= (isset($data['stripe_currency_code']) && $data['stripe_currency_code'] == 'XCD') ? "selected" : "" ?>> East Caribbean Dollar </option>
                                            <option value="XOF" <?= (isset($data['stripe_currency_code']) && $data['stripe_currency_code'] == 'XOF') ? "selected" : "" ?>> West African CFA franc </option>
                                            <option value="XPF" <?= (isset($data['stripe_currency_code']) && $data['stripe_currency_code'] == 'XPF') ? "selected" : "" ?>> CFP Franc </option>
                                            <option value="YER" <?= (isset($data['stripe_currency_code']) && $data['stripe_currency_code'] == 'YER') ? "selected" : "" ?>> Yemeni Rial </option>
                                            <option value="ZAR" <?= (isset($data['stripe_currency_code']) && $data['stripe_currency_code'] == 'ZAR') ? "selected" : "" ?>> South African Rand </option>
                                            <option value="ZMW" <?= (isset($data['stripe_currency_code']) && $data['stripe_currency_code'] == 'ZMW') ? "selected" : "" ?>> Zambian Kwacha </option>
                                        </select>
                                    </div>
                                    <hr>
                                    <h5>Paytm Payments </h5>
                                    <hr>
                                    <div class="form-group">
                                        <label for="paytm_payment_method">Paytm Payments <small>[ Enable / Disable ] </small></label><br>
                                        <input type="checkbox" id="paytm_payment_method_btn" class="js-switch" <?= (isset($data['paytm_payment_method']) && !empty($data['paytm_payment_method']) && $data['paytm_payment_method'] == '1') ? 'checked' : ""; ?>>
                                        <input type="hidden" id="paytm_payment_method" name="paytm_payment_method" value="<?= (isset($data['paytm_payment_method']) && !empty($data['paytm_payment_method'])) ? $data['paytm_payment_method'] : 0; ?>">
                                    </div>
                                    <div class="form-group">
                                        <label for="">Paytm Payment Mode <small>[ sandbox / live ]</small></label>
                                        <select name="paytm_mode" class="form-control">
                                            <option value="sandbox" <?= (isset($data['paytm_mode']) && $data['paytm_mode'] == 'sandbox') ? "selected" : "" ?>>Sandbox ( Testing )</option>
                                            <option value="production" <?= (isset($data['paytm_mode']) && $data['paytm_mode'] == 'production') ? "selected" : "" ?>>Production ( Live )</option>
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label for="paytm_merchant_key">Merchant key</label>
                                        <input type="text" class="form-control" name="paytm_merchant_key" value="<?= (isset($data['paytm_merchant_key'])) ? $data['paytm_merchant_key'] : '' ?>" placeholder="Paytm Merchant Key" />
                                    </div>
                                    <div class="form-group">
                                        <label for="paytm_merchant_id">Merchant ID</label>
                                        <input type="text" class="form-control" name="paytm_merchant_id" value="<?= (isset($data['paytm_merchant_id'])) ? $data['paytm_merchant_id'] : '' ?>" placeholder="Paytm Merchant ID" />
                                    </div>
                                    <br>
                                    <div class="form-group">
                                        <input type="submit" id="btn_update" class="btn-primary btn" value="Save" name="btn_update" />
                                    </div>
                                    <div class="form-group">
                                        <div id="result"></div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    <!-- /.box -->
                </div>
            </div>
        </section>
    <?php } else { ?>
        <div class="alert alert-danger">You have no permission to view settings</div>
    <?php } ?>
    <div class="separator"> </div>
</div><!-- /.content-wrapper -->
</body>

</html>
<?php include "footer.php"; ?>
<!-- <script type="text/javascript" src="css/js/ckeditor/ckeditor.js"></script>
<script type="text/javascript">
    CKEDITOR.replace('contact_us');
</script> -->
<script type="text/javascript">
    /* paypal change button value */
    var changeCheckbox = document.querySelector('#paypal_payment_method_btn');
    var init = new Switchery(changeCheckbox);
    changeCheckbox.onchange = function() {
        if ($(this).is(':checked')) {
            $('#paypal_payment_method').val(1);
        } else {
            $('#paypal_payment_method').val(0);
        }
    };

    /* payumoney change button value */

    var changeCheckbox = document.querySelector('#payumoney_payment_method_btn');
    var init = new Switchery(changeCheckbox);
    changeCheckbox.onchange = function() {
        if ($(this).is(':checked')) {
            $('#payumoney_payment_method').val(1);
        } else {
            $('#payumoney_payment_method').val(0);
        }
    };

    /* razorpay change button value */

    var changeCheckbox = document.querySelector('#razorpay_payment_method_btn');
    var init = new Switchery(changeCheckbox);
    changeCheckbox.onchange = function() {
        if ($(this).is(':checked')) {
            $('#razorpay_payment_method').val(1);
        } else {
            $('#razorpay_payment_method').val(0);
        }
    };

    /* COD button value */

    var changeCheckbox = document.querySelector('#cod_payment_method_btn');
    var init = new Switchery(changeCheckbox);
    changeCheckbox.onchange = function() {
        if ($(this).is(':checked')) {
            $('#cod_payment_method').val(1);
        } else {
            $('#cod_payment_method').val(0);
        }
    };

    /* Paystack button value */
    var changeCheckbox = document.querySelector('#paystack_payment_method_btn');
    var init = new Switchery(changeCheckbox);
    changeCheckbox.onchange = function() {
        if ($(this).is(':checked')) {
            $('#paystack_payment_method').val(1);
        } else {
            $('#paystack_payment_method').val(0);
        }
    };

    //  /* Flutterwave button value */
    var changeCheckbox = document.querySelector('#flutterwave_payment_method_btn');
    var init = new Switchery(changeCheckbox);
    changeCheckbox.onchange = function() {
        if ($(this).is(':checked')) {
            $('#flutterwave_payment_method').val(1);
        } else {
            $('#flutterwave_payment_method').val(0);
        }
    };

    /* Midtrans button value */
    var changeCheckbox = document.querySelector('#midtrans_payment_method_btn');
    var init = new Switchery(changeCheckbox);
    changeCheckbox.onchange = function() {
        if ($(this).is(':checked')) {
            $('#midtrans_payment_method').val(1);
        } else {
            $('#midtrans_payment_method').val(0);
        }
    };

    /* Midtrans button value */
    var changeCheckbox = document.querySelector('#stripe_payment_method_btn');
    var init = new Switchery(changeCheckbox);
    changeCheckbox.onchange = function() {
        if ($(this).is(':checked')) {
            $('#stripe_payment_method').val(1);
        } else {
            $('#stripe_payment_method').val(0);
        }
    };

    /* paytm change button value */

    var changeCheckbox = document.querySelector('#paytm_payment_method_btn');
    var init = new Switchery(changeCheckbox);
    changeCheckbox.onchange = function() {
        if ($(this).is(':checked')) {
            $('#paytm_payment_method').val(1);
        } else {
            $('#paytm_payment_method').val(0);
        }
    };

</script>
<script>
    $('#payment_method_settings_form').on('submit', function(e) {
        e.preventDefault();
        var formData = new FormData(this);
        $.ajax({
            type: 'POST',
            url: 'public/db-operation.php',
            data: formData,
            beforeSend: function() {
                $('#btn_update').val('Please wait..').attr('disabled', true);
            },
            cache: false,
            contentType: false,
            processData: false,
            success: function(result) {
                $('#result').html(result);
                $('#result').show().delay(5000).fadeOut();
                $('#btn_update').val('Save').attr('disabled', false);
            }
        });
    });
</script>