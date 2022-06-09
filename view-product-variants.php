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
include 'header.php';
?>

<head>
    <title>Sub Category Products | <?= $settings['app_name'] ?> - Dashboard</title>
</head>
<?php
include_once('includes/custom-functions.php');
$fn = new custom_functions;

if (isset($_GET['id'])) {
    $ID = $db->escapeString($fn->xss_clean($_GET['id']));
} else {
    $ID = "";
}
$sql = "SELECT p.*,v.*,v.id as variant_id,(SELECT short_code FROM unit u where u.id=v.measurement_unit_id)as mesurement_unit_name,(SELECT short_code FROM unit u where u.id=v.stock_unit_id)as stock_unit_name FROM products p JOIN product_variant v ON v.product_id=p.id where p.id=" . $ID;
$db->sql($sql);
$res = $db->getResult();
?>
<?php
if ($db->numRows($result) == 0) { ?>
    <div class="content-wrapper">
        <section class="content-header">
            <h1>
                No Variants Available
                <small><a href='products.php'><i class='fa fa-angle-double-left'></i>&nbsp;&nbsp;&nbsp;Back to Products</a></small>
            </h1>
        </section>
    </div>
<?php } else {
?>
    <div class="content-wrapper">
        <?php
        if ($permissions['products']['read'] == 1) { ?>
            <section class="content">
                <!-- Main row -->
                <div class="row">
                    <!-- Left col -->
                    <div class="col-xs-12">
                        <div class="box">
                            <div class="box-header">
                                <?php
                                $sql = "SELECT name FROM products WHERE id=" . $ID;
                                $db->sql($sql);

                                $product_name = $db->getResult();
                                ?>
                                <h3 class="box-title">Product : <?php echo $product_name[0]['name']; ?><small><a href='products.php'> <i class='fa fa-angle-double-left'></i>&nbsp;&nbsp;&nbsp;Back to Products</a></small></h3>
                                <div class="box-tools">
                                </div>
                            </div>
                            <!-- /.box-header -->
                            <div class="box-body table-responsive">
                                <table class="table table-hover">
                                    <tr>
                                        <th>No.</th>
                                        <th>Name</th>
                                        <th>Image</th>
                                        <th>Measurement</th>
                                        <th>Status</th>
                                        <th>Stock</th>
                                        <th>Price(<?= $settings['currency'] ?>)</th>
                                        <th>Discounted Price(<?= $settings['currency'] ?>)</th>
                                        <th>Indicator</th>
                                        <th>Manufacturer</th>
                                        <th>Made In</th>
                                        <th>Description</th>
                                        <th>Return</th>
                                        <th>Cancellation</th>
                                        <th>Till Status</th>
                                        <th>Action</th>
                                    </tr>
                                    <?php

                                    // get all data using while loop
                                    $count = 1;
                                    // delete all menu image files from directory
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
                                        $return_status = $row['return_status'] == 1 ? "<span class='label label-success'>Allowed</span>" : "<span class='label label-danger'>Not Allowed</span>";

                                        $till_status = '<label class="label label-danger">Not Specified</label>';
                                        
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
                                        $cancelable_status = $row['cancelable_status'] == 1 ? "<span class='label label-success'>Allowed</span>" : "<span class='label label-danger'>Not Allowed</span>";
                                    ?>
                                        <tr>
                                            <td><?php echo $count; ?></td>
                                            <td><?php echo $row['name']; ?></td>
                                            <td width="10%"><img src="<?php echo $row['image']; ?>" width="60" height="40" /></td>
                                            <td><?php echo $row['measurement'] . " " . $row['mesurement_unit_name']; ?></td>
                                            <td><?php echo $row['serve_for'] == 'Sold Out' ? "<span class='label label-danger'>Sold Out</label>" : "<span class='label label-success'>Available</label>"; ?></td>
                                            <td><?php echo $row['stock'] . " " . $row['stock_unit_name']; ?></td>
                                            <td><?php echo $row['price']; ?></td>
                                            <td><?php echo $row['discounted_price']; ?></td>
                                            <td><?php echo $indicator; ?></td>
                                            <td><?php echo $row['manufacturer']; ?></td>
                                            <td><?php echo $row['made_in']; ?></td>
                                            <td><?php echo $row['description']; ?></td>
                                            <td><?php echo $return_status; ?></td>
                                            <td><?php echo $cancelable_status; ?></td>
                                            <td><?php echo $till_status; ?></td>
                                            <td><a href="product-detail.php?id=<?php echo $row['variant_id']; ?>"><i class="fa fa-folder-open"></i>View</a> <a href="edit-product.php?id=<?php echo $row['product_id']; ?>"><i class="fa fa-edit"></i>Edit</a></td>


                                        </tr>
                                    <?php $count++;
                                    } ?>
                                </table>
                            </div>
                            <!-- /.box-body -->
                        </div>
                        <!-- /.box -->
                    </div>
                    <!-- right col (We are only adding the ID to make the widgets sortable)-->
                </div>
                <!-- /.row (main row) -->
            </section>
        <?php } else { ?>
            <div class="alert alert-danger topmargin-sm">You have no permission to view product variant.</div>
            <a href='products.php'> <i class='fa fa-angle-double-left'></i>&nbsp;&nbsp;&nbsp;Back to products</a>
        <?php } ?>
        <!-- /.content -->
    </div>
<?php } ?>
<?php $db->disconnect(); ?>
<?php include 'footer.php'; ?>