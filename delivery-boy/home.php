<?php session_start();
    
    include_once ('../includes/custom-functions.php');
    include_once ('../includes/functions.php');
    $function = new custom_functions();
    
    // set time for session timeout
    $currentTime = time() + 25200;
    $expired = 3600;
    // if session not set go to login page
    if(!isset($_SESSION['delivery_boy_id']) && !isset($_SESSION['name'])){
        header("location:index.php");
    }else{
        $id = $_SESSION['delivery_boy_id'];
    }
    // if current time is more than session timeout back to login page
    if ($currentTime > $_SESSION['timeout']) {
        session_destroy();
        header("location:index.php");
    }
    // destroy previous session timeout and create new one
    unset($_SESSION['timeout']);
    $_SESSION['timeout'] = $currentTime + $expired;
    
    include "header.php";?>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
        <title><?=$settings['app_name']?> - Dashboard</title>
	</head>
    <body>
       
        <!-- Content Wrapper. Contains page content -->
        <div class="content-wrapper">
            <!-- Content Header (Page header) -->
            <section class="content-header">
                <h1>Home</h1>
                <ol class="breadcrumb">
                    <li>
                        <a href="home.php"> <i class="fa fa-home"></i> Home</a>
                    </li>
                </ol>
            </section>
            <section class="content">
                <div class="row">
                    <div class="col-lg-4 col-xs-6">
                        <div class="small-box bg-aqua">
                            <div class="inner">
                                <h3><?=$function->rows_count('orders','id','delivery_boy_id='.$id);?></h3>
                                <p>Orders</p>
                            </div>
                            <div class="icon"><i class="fa fa-shopping-cart"></i></div>
                            <a href="orders.php" class="small-box-footer">More info <i class="fa fa-arrow-circle-right"></i></a>
                        </div>
                    </div>
                    
                    <div class="col-lg-4 col-xs-6">
                        <div class="small-box bg-yellow">
                            <div class="inner">
                                <h3><?= $function->get_balance($id);?></h3>
                                <p>Balance</p></p>
                            </div>
                            <div class="icon"><i class="fa fa-money"></i></div>
                            <a href="fund-transfers.php" class="small-box-footer">More info <i class="fa fa-arrow-circle-right"></i></a>
                        </div>
                    </div>
                    
                    <div class="col-lg-4 col-xs-6">
                        <div class="small-box bg-yellow">
                            <div class="inner">
                                <h3><?= $function->get_bonus($id);?></h3>
                                <p>Bonus%</p></p>
                            </div>
                            <div class="icon"><i class="fa fa-cubes"></i></div>
                            <a href="fund-transfers.php" class="small-box-footer">More info <i class="fa fa-arrow-circle-right"></i></a>
                        </div>
                    </div>
                    
                </div>
				<div class="row">
					<div class="col-md-12">
						<div class="box box-info">
							<div class="box-header with-border">
								<h3 class="box-title">Latest Orders</h3>
								<div class="box-tools pull-right">
									<button class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i></button>
									<button class="btn btn-box-tool" data-widget="remove"><i class="fa fa-times"></i></button>
								</div>
				<form method="POST" id="filter_form" name="filter_form">
    
                <div class="form-group pull-right">
                    <!--<h3 class="box-title">Filter by status</h4>-->
                        <select id="filter_order" name="filter_order" placeholder="Select Status" required class="form-control" style="width: 300px;">
                            <option value="">All Orders</option>
                            <option value='received'>Received</option>
                            <option value='processed'>Processed</option>
                            <option value='shipped'>Shipped</option>
                            <option value='delivered'>Delivered</option>
                            <option value='cancelled'>Cancelled</option>
                        </select>
                        
                    <!-- <input type="submit" name="filter_btn" id="filter_btn" value="Filter" class="btn btn-primary btn-md"> -->
                </div>
                </form>
							</div>
							<div class="box-body">
								<div id="toolbar">
									<form method="post">
										<select class='form-control' id="category_id" name="category_id" placeholder="Select Category" required style="display: none;">
											<?php
												$Query="select name, id from category";
												$db->sql($Query);
                                                $result=$db->getResult();
												if($result)
												{
												?>
											<option value="">All Products</option>
                                            <?php foreach($result as $row){?>
                                                 <option value='<?=$row['id']?>'><?=$row['name']?></option>
                                                <?php }} 
                                                    ?>
											
										</select>
									</form>
								</div>
								<div class="table-responsive">
									<table class="table no-margin" id='orders_table' data-toggle="table" 
										data-url="get-bootstrap-table-data.php?table=orders"
										data-page-list="[5, 10, 20, 50, 100, 200]"
										data-show-refresh="true" data-show-columns="true"
										data-side-pagination="server" data-pagination="true"
										data-search="true" data-trim-on-search="false"
										data-sort-name="id" data-sort-order="desc"
										data-toolbar="#toolbar" data-query-params="queryParams"
										>
										<thead>
											<tr>
												<th data-field="id" data-sortable='true'>O.ID</th>
												<th data-field="user_id" data-sortable='true' data-visible="false">User ID</th>
												 <th data-field="qty" data-sortable='true' data-visible="false">Qty</th>
                                                <th data-field="name" data-sortable='true'>U.Name</th>
												<th data-field="mobile" data-sortable='true' data-visible="true">Mob.</th>
                                                <th data-field="order_note" data-sortable='true' data-visible="false">Order Note</th>
												<th data-field="items" data-sortable='true' data-visible="false">Items</th>
												<th data-field="total" data-sortable='true' data-visible="true">Total(<?=$settings['currency']?>)</th>
												<th data-field="delivery_charge" data-sortable='true'>D.Chrg</th>
												<th data-field="tax" data-sortable='false'>Tax <?=$settings['currency']?>(%)</th>
												<th data-field="discount" data-sortable='true' data-visible="true">Disc.<?=$settings['currency']?>(%)</th>
												<th data-field="promo_code" data-sortable='true' data-visible="false">Promo Code</th>
												<th data-field="promo_discount" data-sortable='true' data-visible="true">Promo Disc.(<?=$settings['currency']?>)</th>
												<th data-field="wallet_balance" data-sortable='true' data-visible="true">Wallet Used(<?=$settings['currency']?>)</th>
												<th data-field="final_total" data-sortable='true'>F.Total(<?=$settings['currency']?>)</th>
												<th data-field="deliver_by" data-sortable='true' data-visible='false'>Deliver By</th>
												<th data-field="payment_method" data-sortable='true' data-visible="true">P.Method</th>
												<th data-field="address" data-sortable='true' data-visible="false">Address</th>
												<th data-field="delivery_time" data-sortable='true' data-visible='false'>D.Time</th>
												<th data-field="status" data-sortable='true' data-visible='false'>Status</th>
												<th data-field="active_status" data-sortable='true' data-visible='true'>A.Status</th>
												<th data-field="date_added" data-sortable='true' data-visible="false">O.Date</th>
												<th data-field="operate">Action</th>
                                               
												
											</tr>
										</thead>
									</table>
								</div>
							</div>
							<div class="box-footer clearfix">
								<a href="orders.php" class="btn btn-sm btn-default btn-flat pull-right">View All Orders</a>
							</div>
						</div>
					</div>
				</div>
			</section>
        </div>
<script>
	$('#filter_order').on('change',function(){
    $('#orders_table').bootstrapTable('refresh');
    });
</script>
<script>
function queryParams(p){
	return {
		"filter_order": $('#filter_order').val(),
		limit:p.limit,
		sort:p.sort,
		order:p.order,
		offset:p.offset,
		search:p.search
	};
}
</script>
<?php include "footer.php";?>
  </body>
</html>