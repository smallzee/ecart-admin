<?php
include_once('includes/functions.php');
include_once('includes/custom-functions.php');
$fn = new custom_functions;
?>
<?php
if (isset($_POST['btnAdd'])) {
	if (ALLOW_MODIFICATION == 0 && !defined(ALLOW_MODIFICATION)) {
		echo '<label class="alert alert-danger">This operation is not allowed in demo panel!.</label>';
		return false;
	}
	if ($permissions['locations']['create'] == 1) {
		$city_name = $db->escapeString($fn->xss_clean($_POST['city_name']));

		// create array variable to handle error
		$error = array();

		if (empty($city_name)) {
			$error['city_name'] = " <span class='label label-danger'>Required!</span>";
		}
		if (!empty($city_name)) {
			$sql_query = "INSERT INTO city (name)VALUES('$city_name')";
			$db->sql($sql_query);
			$result = $db->getResult();

			if (!empty($result)) {
				$result = 0;
			} else {
				$result = 1;
			}

			if ($result == 1) {
				$error['add_city'] = "<section class='content-header'>
												<span class='label label-success'>City Added Successfully</span>
												<h4><small><a  href='city.php'><i class='fa fa-angle-double-left'></i>&nbsp;&nbsp;&nbsp;Back to Cities</a></small></h4>
												</section>";
			} else {
				$error['add_city'] = " <span class='label label-danger'>Failed add city</span>";
			}
		}
	} else {
		$error['add_city'] = "<section class='content-header'><span class='label label-danger'>You have no permission to create city</span></section>";
	}
}

if (isset($_POST['btnCancel'])) {
	header("location:city-table.php");
}

?>
<section class="content-header">
	<h1>Add city</h1>
	<?php echo isset($error['add_city']) ? $error['add_city'] : ''; ?>
	<ol class="breadcrumb">
		<li><a href="home.php"><i class="fa fa-home"></i> Home</a></li>
	</ol>
	<hr />
</section>
<section class="content">
	<div class="row">
		<div class="col-md-6">
			<?php if ($permissions['locations']['create'] == 0) { ?>
				<div class="alert alert-danger">You have no permission to create city</div>
			<?php } ?>
			<!-- general form elements -->
			<div class="box box-primary">
				<div class="box-header with-border">
					<h3 class="box-title">Add City</h3>
				</div><!-- /.box-header -->
				<!-- form start -->
				<form method="post" enctype="multipart/form-data">
					<div class="box-body">
						<div class="form-group">
							<label for="exampleInputEmail1">City Name</label><?php echo isset($error['city_name']) ? $error['city_name'] : ''; ?>
							<input type="text" class="form-control" name="city_name">
						</div>
					</div><!-- /.box-body -->

					<div class="box-footer">
						<button type="submit" class="btn btn-primary" name="btnAdd">Add</button>
						<input type="reset" class="btn-warning btn" value="Clear" />
					</div>
				</form>
			</div><!-- /.box -->
		</div>
	</div>
</section>

<div class="separator"> </div>

<?php $db->disconnect(); ?>