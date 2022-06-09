<?php
include_once('../includes/custom-functions.php');
$function = new custom_functions;
if (isset($_GET['id'])) {
    $ID = $db->escapeString($fn->xss_clean($_GET['id']));
} else {
    $ID = "";
}
// create array variable to handle error
$allowed = ALLOW_MODIFICATION;
$error = array();
if (isset($_POST['update_order_status'])) {
    $process = $db->escapeString($fn->xss_clean($_POST['status']));
}
$sql = "SELECT oi.*,oi.active_status as oi_active_status,o.total as order_total,u.*,p.*,v.*,o.*,u.name as uname,o.status as order_status,p.name as pname,(SELECT short_code FROM unit un where un.id=v.measurement_unit_id)as mesurement_unit_name FROM `order_items` oi JOIN users u ON u.id=oi.user_id JOIN product_variant v ON oi.product_variant_id=v.id JOIN products p ON p.id=v.product_id JOIN orders o ON o.id=oi.order_id WHERE o.id=" . $ID;
$db->sql($sql);
$config = $fn->get_configurations();
// print_r($config);
$generate_otp = $config['generate-otp'];
$res = $db->getResult();
$items = [];
$otp = "";
foreach ($res as $row) {
    $otp = $row['otp'];
    $data = array($row['product_id'], $row['product_variant_id'], $row['pname'], $row['measurement'], $row['mesurement_unit_name'], $row['quantity'], $row['discounted_price'], $row['price'], $row['oi_active_status'], $row['otp']);
    array_push($items, $data);
}

?>
<section class="content-header">
    <h1>Order Detail</h1>
    <?php echo isset($error['update_data']) ? $error['update_data'] : ''; ?>
    <ol class="breadcrumb">
        <li><a href="home.php"><i class="fa fa-home"></i> Home</a></li>
    </ol>
</section>
<section class="content">
    <div class="row">
        <div class="col-md-6">
            <div class="box">
                <div class="box-header with-border">
                    <h3 class="box-title">Order Detail</h3>
                </div>
                <!-- /.box-header -->
                <div class="box-body">
                    <!--                    <form  id="update_status_form">-->
                    <table class="table table-bordered">
                        <tr>
                            <input type="hidden" name="hidden" id="order_id" value="<?php echo $res[0]['id']; ?>">
                            <th style="width: 10px">ID</th>
                            <td><?php echo $res[0]['id']; ?></td>
                        </tr>
                        <tr>
                            <th style="width: 10px">Name</th>
                            <td><?php echo $res[0]['uname']; ?></td>
                        </tr>
                        <tr>
                            <th style="width: 10px">Email</th>
                            <td><?php echo $res[0]['email']; ?></td>
                        </tr>
                        <tr>
                            <th style="width: 10px">Contact</th>
                            <td><?php echo $res[0]['mobile']; ?></td>
                        </tr>
                        <tr>
                            <th style="width: 10px">Order Note</th>
                            <td><?php echo $res[0]['order_note']; ?></td>
                        </tr>
                        <tr>
                            <th style="width: 10px">Items</th>
                            <td><?php $total = 0;

                                foreach ($items as $item) {
                                    // echo $item[8];
                                    if ($item[8] == 'received') {
                                        $active_status = '<label class="label label-primary">' . $item[8] . '</label>';
                                    }
                                    if ($item[8] == 'processed') {
                                        $active_status = '<label class="label label-info">' . $item[8] . '</label>';
                                    }
                                    if ($item[8] == 'shipped') {
                                        $active_status = '<label class="label label-warning">' . $item[8] . '</label>';
                                    }
                                    if ($item[8] == 'delivered') {
                                        $active_status = '<label class="label label-success">' . $item[8] . '</label>';
                                    }
                                    if ($item[8] == 'returned' || $item[8] == 'cancelled') {
                                        $active_status = '<label class="label label-danger">' . $item[8] . '</label>';
                                    }
                                    $total += $subtotal = ($item[6] != 0 && $item[6] < $item[7]) ? ($item[6] * $item[5]) : ($item[7] * $item[5]);
                                    echo "<b>Product Id : </b>" . $item[0];
                                    echo "<b> Product Variant Id : </b>" . $item[1];
                                    echo " <b>Name : </b>" . $item[2];
                                    echo " <b>Unit : </b>" . $item[3] . " " . $item[4];
                                    echo " <b>Quantity : </b>" . $item[5];
                                    echo " <b>Price : </b>" . $item[7];
                                    echo " <b>Discounted Price : </b>" . $item[6];
                                    echo " <b>Subtotal : </b>" . $subtotal;
                                    echo " <b>Active Status : </b>" . $active_status . "<br>
                                        -----------------------------------<br>";
                                } ?>

                            </td>
                        </tr>
                        <tr>
                            <th style="width: 10px">Total (<?= $settings['currency'] ?>)</th>
                            <td><?php echo $res[0]['order_total']; ?></td>
                        </tr>
                        <tr>
                            <th style="width: 10px">D.Charge (<?= $settings['currency'] ?>)</th>
                            <td><?php echo $res[0]['delivery_charge']; ?></td>

                        </tr>
                        <tr>
                            <th style="width: 10px">Tax <?= $settings['currency'] ?>(%)</th>
                            <td><?php echo $res[0]['tax_amount'] . '(' . $res[0]['tax_percentage'] . '%)'; ?></td>
                        </tr>
                        <?php
                        $discounted_amount = $res[0]['total'] * $res[0]['discount'] / 100; /*  */
                        $final_total = $res[0]['total'] - $discounted_amount;
                        $discount_in_rupees = $res[0]['total'] - $final_total;
                        ?>
                        <tr>
                            <th style="width: 10px">Disc. <?= $settings['currency'] ?>(%)</th>
                            <td><?php echo  $discount_in_rupees . '(' . $res[0]['discount'] . '%)'; ?></td>
                        </tr>

                        <tr>
                            <th style="width: 10px">Promo Disc. (<?= $settings['currency'] ?>)</th>
                            <td><?php echo $res[0]['promo_discount']; ?></td>
                        </tr>
                        <tr>
                            <th style="width: 10px">Wallet Used</th>
                            <td><?php echo $res[0]['wallet_balance']; ?></td>
                        </tr>


                        <input type="hidden" name="total_amount" id="total_amount" value="<?php echo $res[0]['order_total'] + $res[0]['delivery_charge'] ?>">
                        <input type="hidden" name="final_amount" id="final_amount" value="<?php echo $res[0]['final_total']; ?>">

                        <tr>
                            <th style="width: 10px">Discount %</th>
                            <td><input type="text" class="form-control" value="<?php echo $res[0]['discount']; ?>" disabled min=0></td>

                        </tr>


                        <tr>
                            <th style="width: 10px">Payable Total(<?= $settings['currency'] ?>)</th>
                            <td><input type="text" class="form-control" id="final_total" name="final_total" value="<?= ceil($res[0]['final_total']); ?>" disabled></td>
                        </tr>
                        <tr>
                            <th>Deliver By</th>


                            <td>
                                <p>You.</p>

                            </td>


                        </tr>
                        <tr>
                            <th style="width: 10px">Payment Method</th>
                            <td><?php echo $res[0]['payment_method']; ?></td>
                        </tr>
                        <tr>
                            <th style="width: 10px">Promo Code</th>
                            <td><?= (!empty($res[0]['promo_code']) || $res[0]['promo_code'] != null) ? $res[0]['promo_code'] : ""; ?></td>
                        </tr>
                        <tr>
                            <th style="width: 10px">Address</th>
                            <td><?php echo $res[0]['address']; ?></td>
                        </tr>
                        <tr>
                            <th style="width: 10px">Order Date</th>
                            <td><?php echo date('d-m-Y', strtotime($row['date_added'])); ?></td>
                        </tr>
                        <tr>
                            <th style="width: 10px">Delivery Time</th>
                            <td><?php echo $res[0]['delivery_time']; ?></td>
                        </tr>
                        <tr>
                            <th>Status</th>
                            <td>
                                <?php
                                $status = json_decode($res[0]['order_status']);
                                $i = count($status);
                                $currentStatus = $status[$i - 1][0];
                                ?>

                                <select name="status" id="status" class="form-control">
                                    <option value="received">Received</option>
                                    <option value="processed">Processed</option>
                                    <option value="shipped">Shipped</option>
                                    <option value="delivered">Delivered</option>
                                    <option value="cancelled">Cancel</option>
                                    <option value="returned">Returned</option>
                                </select>
                            </td>
                        </tr>

                    </table>

                    <!-- /.box-body -->
                    <div class="alert alert-danger" id="result_fail" style="display:none"></div>
                    <div class="alert alert-success" id="result_success" style="display:none"></div>
                    <div class="box-footer clearfix">
                        <a href="#" title='update' class="btn btn-primary update_order_status" id="submit_btn" data-id='<?= $res[0]['id']; ?>'>Update</a>
                        <a class="btn btn-primary" data-fancybox="" data-options="{&quot;iframe&quot; : {&quot;css&quot; : {&quot;width&quot; : &quot;80%&quot;, &quot;height&quot; : &quot;80%&quot;}}}" href="https://www.google.com/maps/search/?api=1&amp;query=<?= $res[0]['latitude']; ?>,<?= $res[0]['longitude']; ?>&hl=es;z=14&amp;output=embed">Locate</a>
                    </div>


                    <!--                    </form>-->
                </div>

                <?php if ($currentStatus == "received") { ?>
                    <button class="btn btn-primary pull-right" onclick="myfunction()" style="margin-right: 5px; margin-top: -45px;"><i class="fa fa-download"></i>Generate Invoice</button>
                <?php } elseif ($currentStatus == "processed") { ?>
                    <button class="btn btn-primary pull-right" onclick="myfunction()" style="margin-right: 5px; margin-top: -45px;"><i class="fa fa-download"></i> Generate Invoice</button>
                <?php } elseif ($currentStatus == "shipped") { ?>
                    <button class="btn btn-primary pull-right" onclick="myfunction()" style="margin-right: 5px; margin-top: -45px;"><i class="fa fa-download"></i> Generate Invoice</button>
                <?php } elseif ($currentStatus == "delivered") { ?>
                    <button class="btn btn-primary pull-right" onclick="myfunction()" style="margin-right: 5px; margin-top: -45px;"><i class="fa fa-download"></i> Generate Invoice</button>
                <?php } else { ?>
                    <button class="btn btn-primary disabled pull-right" style="margin-right: 5px; margin-top: -45px;"><i class="fa fa-download"></i> Generate Invoice</button>
                <?php } ?>
            </div>
            <!-- /.box -->
        </div>
        <div class="col-md-6">
            <ul class="timeline">
                <?php foreach ($status as $s) { ?>
                    <!-- timeline time label -->
                    <li class="time-label">
                        <span class="bg-blue">
                            <?= $s[0]; ?>
                        </span>
                    </li>
                    <!-- /.timeline-label -->
                    <!-- timeline item -->
                    <li>
                        <!-- timeline icon -->
                        <i class="fa fa-circle bg-blue"></i>
                        <div class="timeline-item">
                            <!--<span class="time"><i class="fa fa-clock-o"></i> 12:05</span>-->
                            <h3 class="timeline-header"><?= $s[1]; ?></h3>
                            <div class="timeline-body">
                            </div>
                        </div>
                    </li>
                    <!-- timeline time label -->
                    <!-- /.timeline-label -->
                    <!-- timeline item -->
                    <!-- END timeline item -->
                <?php } ?>
            </ul>
        </div>
    </div>
</section>
<!-- <script>
    var total_amount=$('#total_amount').val();
    $("#final_total").val(total_amount);
</script> -->

<script>
    var otp = '<?php echo $otp; ?>';
    var allowed  = '<?= $allowed; ?>';
    var generate_otp = '<?= $generate_otp ?>';
    // alert(generate_otp);
    $(document).on('click', '.update_order_status', function(e) {
        e.preventDefault();
        if (allowed == 0) {
            alert('Sorry! This operation is not allowed in demo panel!.');
            window.location.reload();
            return false;
        }
        var status = $('#status').val();
        var id = $('#order_id').val();
        var deliver_by = $('#deliver_by').val();
        var dataString = 'update_order_status=true&id=' + id + '&status=' + status + '&delivery_boy_id=' + <?= $_SESSION['delivery_boy_id']; ?> + '&ajaxCall=1';
        if (generate_otp == 1) {
          
            if (status == "delivered") {
                // alert(status);
                var entered_otp = prompt("Please enter OTP:");
                if (entered_otp == null || entered_otp == "") {
                    alert("Must required valid OTP.");
                } else {
                    if (entered_otp == otp) {
                        $.ajax({
                            url: "../api-firebase/order-process.php",
                            type: "POST",
                            data: dataString,
                            beforeSend: function() {
                                $('#submit_btn').html('Please wait..');
                                $('#submit_btn').attr('disabled', true);
                            },
                            dataType: "json",
                            success: function(data) {
                                if (data.error == true) {
                                    $('#result_fail').html(data.message);
                                    $('#result_fail').show().delay(6000).fadeOut();
                                } else {
                                    $('#result_success').html(data.message);
                                    $('#result_success').show().delay(6000).fadeOut();
                                }


                                $('#submit_btn').attr('disabled', false);
                                $('#submit_btn').html('Update');
                            }
                        });
                    } else {
                        alert("OTP is not matching");
                    }
                }
            } else if (status == "received" || status == "processed" || status == "shipped" || status == "cancelled" || status == "returned") {
                // alert(status);
                $.ajax({
                    url: "../api-firebase/order-process.php",
                    type: "POST",
                    data: dataString,
                    beforeSend: function() {
                        $('#submit_btn').html('Please wait..');
                        $('#submit_btn').attr('disabled', true);
                    },
                    dataType: "json",
                    success: function(data) {
                        if (data.error == true) {
                            $('#result_fail').html(data.message);
                            $('#result_fail').show().delay(6000).fadeOut();
                        } else {
                            $('#result_success').html(data.message);
                            $('#result_success').show().delay(6000).fadeOut();
                        }
                        $('#submit_btn').attr('disabled', false);
                        $('#submit_btn').html('Update');
                    }
                });
            }
        } else {
            $.ajax({
                url: "../api-firebase/order-process.php",
                type: "POST",
                data: dataString,
                beforeSend: function() {
                    $('#submit_btn').html('Please wait..');
                    $('#submit_btn').attr('disabled', true);
                },
                dataType: "json",
                success: function(data) {
                    if (data.error == true) {
                        $('#result_fail').html(data.message);
                        $('#result_fail').show().delay(6000).fadeOut();
                    } else {
                        $('#result_success').html(data.message);
                        $('#result_success').show().delay(6000).fadeOut();
                    }
                    $('#submit_btn').attr('disabled', false);
                    $('#submit_btn').html('Update');
                }
            });
        }
    });
</script>
<script type="text/javascript">
    /* function sendMail(){
    var process = $('#status').val();
    window.location.href = './public/send-message.php?process='+process+'&id=<?php //echo $data['id']; 
                                                                                ?>';
} */
</script>

<script>
    $(document).ready(function() {
        $("#status").val("<?= $GLOBALS['currentStatus'] ?>");
    });
</script>
<script>
    function myfunction() {
        window.location.href = 'invoice.php?id=<?php echo $res[0]['id']; ?>';
    }
</script>

<?php $db->disconnect(); ?>