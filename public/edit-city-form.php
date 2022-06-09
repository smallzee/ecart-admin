<?php
include_once('includes/functions.php');
include_once('includes/custom-functions.php');
$fn = new custom_functions;

?>
<?php
	$ID = (isset($_GET['id'])) ? $db->escapeString($fn->xss_clean($_GET['id'])) : "";
// create array variable to store category data
$category_data = array();
if (isset($_POST['btnEdit'])) {

	if (ALLOW_MODIFICATION == 0 && !defined(ALLOW_MODIFICATION)) {
		echo '<label class="alert alert-danger">This operation is not allowed in demo panel!.</label>';
		return false;
	}
	if ($permissions['locations']['update'] == 1) {

		$city_name = $db->escapeString($fn->xss_clean($_POST['city_name']));
		$error = array();
		if (empty($city_name)) {
			$error['city_name'] = " <span class='label label-danger'>Required!</span>";
		}
		if (!empty($city_name) && empty($error)) {
			$sql_query = "UPDATE city SET name = '" . $city_name . "' WHERE id =" . $ID;
			$db->sql($sql_query);
			$update_result = $db->getResult();
			if (!empty($update_result)) {
				$update_result = 0;
			} else {
				$update_result = 1;
			}
			if ($update_result == 1) {
				$error['update_city'] = " <section class='content-header'>
												<span class='label label-success'>City updated Successfully</span>
												<h4><small><a  href='city.php'><i class='fa fa-angle-double-left'></i>&nbsp;&nbsp;&nbsp;Back to Citiies</a></small></h4>
												
												</section>";
			} else {
				$error['update_city'] = " <span class='label label-danger'>Failed update city</span>";
			}
		}
	} else {
		$error['update_city'] = " <span class='label label-danger'>You have no permission to update city</span>";
	}
}

// create array variable to store previous data
$data = array();
$sql_query = "SELECT * FROM city WHERE id =" . $ID;
$db->sql($sql_query);
$res = $db->getResult();

if (isset($_POST['btnCancel'])) { ?>
	<script>
		window.location.href = "city.php";
	</script>
<?php }; ?>
<section class="content-header">
	<h1>
		Edit city</h1>
	<small><?php echo isset($error['update_city']) ? $error['update_city'] : ''; ?></small>
	<ol class="breadcrumb">
		<li><a href="home.php"><i class="fa fa-home"></i> Home</a></li>
	</ol>
</section>
<section class="content">
	<!-- Main row -->
	<div class="row">
		<div class="col-md-6">
			<?php if ($permissions['locations']['update'] == 0) { ?>
				<div class="alert alert-danger">You have no permission to update city</div>
			<?php } ?>
			<!-- general form elements -->
			<div class="box box-primary">
				<div class="box-header with-border">
					<h3 class="box-title">Edit City</h3>
				</div><!-- /.box-header -->
				<!-- form start -->
				<form id="edit_city_form" method="post" enctype="multipart/form-data">
					<div class="box-body">
						<div class="form-group">
							<label for="exampleInputEmail1">City Name</label><?php echo isset($error['city_name']) ? $error['city_name'] : ''; ?>
							<input type="text" class="form-control" name="city_name" value="<?php echo $res[0]['name']; ?>">
						</div>
					</div><!-- /.box-body -->

					<div class="box-footer">
						<button type="submit" class="btn btn-primary" name="btnEdit">Update</button>
						<button type="submit" class="btn btn-danger" name="btnCancel">Cancel</button>
					</div>
				</form>
			</div><!-- /.box -->
		</div>
	</div>
</section>

<div class="separator"> </div>

<?php $db->disconnect(); ?>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.17.0/jquery.validate.min.js"></script>
<script>
	$('#edit_city_form').validate({
		rules: {
			city_name: "required"
		}
	});
</script>