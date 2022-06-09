<?php 
    
session_start();
    
    // set time for session timeout
    $currentTime = time() + 25200;
    $expired = 3600;
    
    // if session not set go to login page
    if(!isset($_SESSION['user'])){
        header("location:index.php");
    }
    
    // if current time is more than session timeout back to login page
    if($currentTime > $_SESSION['timeout']){
        session_destroy();
        header("location:index.php");
    }
    
    // destroy previous session timeout and create new one
    unset($_SESSION['timeout']);
    $_SESSION['timeout'] = $currentTime + $expired;
    include 'header.php';?>
<head>
<title>Sub Category Products | <?=$settings['app_name']?> - Dashboard</title>
</head>
<?php
include_once('includes/custom-functions.php');
$fn = new custom_functions;
    if(isset($_GET['id'])){
		$ID = $db->escapeString($fn->xss_clean($_GET['id']));
    }else{
    	$ID = "";
    }
    // get image file from table
    $sql_query = "SELECT p.*,v.*,(SELECT short_code FROM unit u where v.measurement_unit_id=u.id) as measurement_name,(SELECT short_code FROM unit u where v.stock_unit_id=u.id) as stock_name FROM products p JOIN product_variant v ON  p.id=v.product_id WHERE p.subcategory_id = $ID";
    $db->sql($sql_query);
    $res = $db->getResult();
    ?>
<?php
    if(empty($res))
    {?>
<div class="content-wrapper">
    <section class="content-header">
        <h1>
            No Products Available
            <small><a  href='subcategories.php'><i class='fa fa-angle-double-left'></i>&nbsp;&nbsp;&nbsp;Back to Sub Categories</a></small>
        </h1>
    </section>
</div>
<?php }
    else{
    ?>
<div class="content-wrapper">
    <section class="content">
        <!-- Main row -->
        <div class="row">
            <!-- Left col -->
            <div class="col-xs-12">
                <div class="box">
                    <div class="box-header">
                        <?php
                            $db->select('subcategory','name',null,'id='.$ID);
                            $subcategory_name = $db->getResult();
                        ?>
                        <h3 class="box-title">Subcategory : <?php echo $subcategory_name[0]['name'];?></h3>
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
                                <th>Price(<?=$settings['currency'];?>)</th>
                                <th>Discounted Price(<?=$settings['currency'];?>)</th>
                                <th>Action</th>
                            </tr>
                            <?php 
                                // get all data using while loop
                                $count=1;
                                	// delete all menu image files from directory
                                	 foreach($res as $row){
                                 ?>
                            <tr>
                                <td><?php echo $count; ?></td>
                                <td><?php echo $row['name'];?></td>
                                <td width="10%"><img src="<?php echo $row['image']; ?>" width="60" height="40"/></td>
                                <td><?php echo $row['measurement']." ".$row['measurement_name'];?></td>
                                <td><?php echo $row['serve_for'];?></td>
                                <td><?php echo $row['stock']." ".$row['stock_name'];?></td>
                                <td><?php echo $row['price'];?></td>
                                <td><?php echo $row['discounted_price'];?></td>

                                <td><a href="view-product-variants.php?id=<?php echo $row['product_id'];?>"><i class="fa fa-eye"></i>View</a> <a href="edit-product.php?id=<?php echo $row['product_id'];?>"><i class="fa fa-edit"></i>Edit</a> <a href="delete-product.php?id=<?php echo $row['product_id'];?>"><i class="fa fa-trash"></i>Delete</a></td>
                            </tr>
                            <?php $count++; } ?>
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
    <!-- /.content -->
</div>
<?php }?>
<?php $db->disconnect(); ?>
<?php include 'footer.php'; ?>