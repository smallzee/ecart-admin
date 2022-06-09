<?php
include_once('includes/functions.php');
include_once('includes/custom-functions.php');
$fn = new custom_functions;
?>
<?php
$ID = (isset($_GET['id'])) ? $db->escapeString($fn->xss_clean($_GET['id'])) : "";

$sql_query = "SELECT id, name FROM city ORDER BY id ASC";
$db->sql($sql_query);
$res_city = $db->getResult();
if (isset($_POST['btnEdit'])) {
	if (ALLOW_MODIFICATION == 0 && !defined(ALLOW_MODIFICATION)) {
		echo '<label class="alert alert-danger">This operation is not allowed in demo panel!.</label>';
		return false;
	}
	if ($permissions['locations']['update'] == 1) {
		$area_name = $db->escapeString($fn->xss_clean($_POST['area_name']));
		$city_ID = $db->escapeString($fn->xss_clean($_POST['city_ID']));
		$delivery_charges = $db->escapeString($fn->xss_clean($_POST['delivery_charges']));
		$minimum_free_delivery_order_amount = $db->escapeString($fn->xss_clean($_POST['minimum_free_delivery_order_amount']));
		// create array variable to handle error
		$error = array();

		if (empty($area_name)) {
			$error['area_name'] = " <span class='label label-danger'>Required!</span>";
		}

		if (empty($city_ID)) {
			$error['city_ID'] = " <span class='label label-danger'>Required!</span>";
		}
		if (empty($delivery_charges)) {
			$error['delivery_charges'] = " <span class='label label-danger'>Required!</span>";
		}
		if (empty($minimum_free_delivery_order_amount)) {
			$error['minimum_free_delivery_order_amount'] = " <span class='label label-danger'>Required!</span>";
		}
		if (!empty($area_name) && !empty($city_ID) && !empty($delivery_charges) && !empty($minimum_free_delivery_order_amount)) {
			$sql_query = "UPDATE area SET name = '$area_name' , city_id = $city_ID, `minimum_free_delivery_order_amount`=$minimum_free_delivery_order_amount,`delivery_charges`=$delivery_charges WHERE id = $ID";
			$db->sql($sql_query);
			$update_result = $db->getResult();
			if (!empty($update_result)) {
				$update_result = 0;
			} else {
				$update_result = 1;
			}
			if ($update_result == 1) {
				$error['update_data'] = "<section class='content-header'><span class='label label-success'>Area updated Successfully</span></section>";
			} else {
				$error['update_data'] = " <span class='label label-danger'>failed update</span>";
			}
		}
	} else {
		$error['update_data'] = "<section class='content-header'><span class='label label-danger'>You have no permission to update area</span></section>";
	}
}

// create array variable to store previous data

$sql_query = "SELECT * FROM area WHERE id =" . $ID;
$db->sql($sql_query);
$res_area = $db->getResult();

?>
<section class="content-header">
	<h1>Edit Area <small><a href='areas.php'><i class='fa fa-angle-double-left'></i>&nbsp;&nbsp;Back</a></small></h1>
	<small><?php echo isset($error['update_data']) ? $error['update_data'] : ''; ?></small>
	<ol class="breadcrumb">
		<li><a href="home.php"><i class="fa fa-home"></i> Home</a></li>
	</ol>
</section>
<section class="content">
	<div class="row">
		<div class="col-md-6">
			<?php if ($permissions['locations']['update'] == 0) { ?>
				<div class="alert alert-danger">You have no permission to update area</div>
			<?php } ?>
			<div class="box box-primary">
				<div class="box-header with-border">
					<h3 class="box-title">Edit Area</h3>
				</div><!-- /.box-header -->
				<!-- form start -->
				<form method="post" id="edit_area_form" enctype="multipart/form-data">
					<div class="box-body">
						<div class="form-group">
							<label for="exampleInputEmail1">Area Name</label><?php echo isset($error['area_name']) ? $error['area_name'] : ''; ?>

							<input type="text" name="area_name" class="form-control" value="<?php echo $res_area[0]['name']; ?>" />
						</div>
						<div class="form-group">
							<label for="exampleInputEmail1">City :</label><?php echo isset($error['city_ID']) ? $error['city_ID'] : ''; ?>

							<select name="city_ID" class="form-control">
								<?php foreach ($res_city as $row) {

									if ($row['id'] == $res_area[0]['city_id']) {

								?>
										<option value="<?php echo $row['id']; ?>" selected="<?php echo $res_area[0]['city_id']; ?>"><?php echo $row['name']; ?></option>
										<?php } else {
										if ($permissions['locations']['read'] == 1) { ?>
											<option value="<?php echo $row['id']; ?>"><?php echo $row['name']; ?></option>
								<?php }
									}
								} ?>
							</select>
						</div>
						<div class="form-group">
							<label for="exampleInputEmail1">Minimum Free Delivery Order Amount</label><?php echo isset($error['minimum_free_delivery_order_amount']) ? $error['minimum_free_delivery_order_amount'] : ''; ?>
							<input type="number" step="any" min="0" class="form-control" name="minimum_free_delivery_order_amount" required value="<?php echo $res_area[0]['minimum_free_delivery_order_amount']; ?>" />
						</div>
						<div class="form-group">
							<label for="exampleInputEmail1">Delivery Charges</label><?php echo isset($error['delivery_charges']) ? $error['delivery_charges'] : ''; ?>
							<input type="number" step="any" min="0" class="form-control" name="delivery_charges" required value="<?php echo $res_area[0]['delivery_charges']; ?>" />
						</div>
					</div>


			</div><!-- /.box-body -->

			<div>
				<input type="submit" class="btn-primary btn" value="Update" name="btnEdit" />
			</div>
			</form>
		</div><!-- /.box -->
	</div>
	</div>
</section>

<div class="separator"> </div>
<?php
$db->disconnect(); ?>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.17.0/jquery.validate.min.js"></script>
<script>
	$('#edit_area_form').validate({
		rules: {
			area_name: "required"
		}
	});
</script>