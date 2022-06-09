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
include 'header.php'; ?>

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
// get image file from table


$db->select('subcategory', '*', null, 'category_id=' . $ID);
$res = $db->getResult();
?>
<?php
if ($db->numRows($result) == 0) { ?>
    <div class="content-wrapper">
        <section class="content-header">
            <h1>
                No Subcategory Available
                <small><a href='categories.php'><i class='fa fa-angle-double-left'></i>&nbsp;&nbsp;&nbsp;Back to Categories</a></small>
            </h1>
        </section>
    </div>
<?php } else {
?>
    <div class="content-wrapper">
        <?php
        if ($permissions['subcategories']['read'] == 1) { ?>
            <section class="content">
                <!-- Main row -->
                <div class="row">
                    <!-- Left col -->
                    <div class="col-xs-12">
                        <div class="box">
                            <div class="box-header">
                                <?php

                                $db->select('category', 'name', null, 'id=' . $ID);
                                $category_name = $db->getResult();
                                // echo $sql;
                                ?>
                                <h3 class="box-title">Category : <?php echo $category_name[0]['name']; ?><small><a href='categories.php'> <i class='fa fa-angle-double-left'></i>&nbsp;&nbsp;&nbsp;Back to Categories</a></small></h3>
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
                                        <th>Products</th>
                                        <th>Action</th>
                                    </tr>
                                    <?php
                                    // get all data using while loop
                                    $count = 1;
                                    foreach ($res as $row) {
                                    ?>
                                        <tr>
                                            <td><?php echo $count; ?></td>
                                            <td><?php echo $row['name']; ?></td>
                                            <td width="10%"><img src="<?php echo $row['image']; ?>" width="60" height="40" /></td>
                                            <td><a href="view-product.php?id=<?php echo $row['id']; ?>"><i class="fa fa-edit"></i>View</a></td>
                                            <td><a href="edit-subcategory.php?id=<?php echo $row['id']; ?>"><i class="fa fa-edit"></i>Edit</a></td>
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
            <div class="alert alert-danger topmargin-sm">You have no permission to view subcategories.</div><a href='categories.php'> <i class='fa fa-angle-double-left'></i>&nbsp;&nbsp;&nbsp;Back to categories</a>
        <?php } ?>

        <!-- /.content -->
    </div>
<?php } ?>
<?php $db->disconnect(); ?>
<?php include 'footer.php'; ?>