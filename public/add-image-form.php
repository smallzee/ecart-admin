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
	$image = $db->escapeString($fn->xss_clean($_FILES['image']['name']));
	$image_error = $db->escapeString($fn->xss_clean($_FILES['image']['error']));
	$image_type = $db->escapeString($fn->xss_clean($_FILES['image']['type']));
	$error = array();
	$allowedExts = array("gif", "jpeg", "jpg", "png");
	error_reporting(E_ERROR | E_PARSE);
	$extension = end(explode(".", $_FILES["image"]["name"]));

	if ($image_error > 0) {
		$error['image'] = " <span class='label label-danger'>Not uploaded!</span>";
	} else if (
		!(($image_type == "image/gif") ||
			($image_type == "image/jpeg") ||
			($image_type == "image/jpg") ||
			($image_type == "image/x-png") ||
			($image_type == "image/png") ||
			($image_type == "image/pjpeg")) &&
		!(in_array($extension, $allowedExts))
	) {

		$error['image'] = " <span class='label label-danger'>Image type must jpg, jpeg, gif, or png!</span>";
	}

	if (empty($error['image'])) {
		$string = '0123456789';
		$file = preg_replace("/\s+/", "_", $_FILES['image']['name']);
		$function = new functions;
		$image = $function->get_random_string($string, 4) . "-" . date("Y-m-d") . "." . $extension;

		// upload new image
		$upload = move_uploaded_file($_FILES['image']['tmp_name'], 'upload/notification/' . $image);

		// insert new data to menu table
		$sql_query = "INSERT INTO tbl_image (image) VALUES('$upload_image')";

		$upload_image = 'upload/notification/' . $image;
		$db->sql($sql_query);
		$result = $db->getResult();
		if ($result) {
			$error['add_menu'] = "<section class='content-header'>
												<span class='label label-success'>Image Added Successfully</span>
												<h4><small><a  href='notification.php'><i class='fa fa-angle-double-left'></i>&nbsp;&nbsp;&nbsp;Back to notifications</a></small></h4>
												</section>";
		} else {
			$error['add_menu'] = " <span class='label label-danger'>Failed</span>";
		}
	}
}
?>
<section class="content-header">
	<h1>Add Image</h1>
	<?php echo isset($error['add_menu']) ? $error['add_menu'] : ''; ?>
	<ol class="breadcrumb">
		<li><a href="home.php"><i class="fa fa-home"></i> Home</a></li>
	</ol>
	<hr />
</section>
<section class="content">

	<div class="row">
		<div class="col-md-6">
			<!-- general form elements -->
			<div class="box box-primary">
				<div class="box-header with-border">
					<h3 class="box-title">Add Image</h3>
				</div><!-- /.box-header -->
				<!-- form start -->
				<form method="post" enctype="multipart/form-data">
					<div class="box-body">
						<div class="form-group">
							<label for="exampleInputFile">Image :</label><?php echo isset($error['image']) ? $error['image'] : ''; ?>
							<input type="file" name="image" id="image" required />
						</div>

					</div><!-- /.box-body -->

					<div class="box-footer">
						<input type="submit" class="btn-primary btn" value="Add" name="btnAdd" />&nbsp;
						<input type="reset" class="btn-danger btn" value="Clear" />

					</div>
				</form>
			</div><!-- /.box -->
		</div>
	</div>
</section>

<div class="separator"> </div>

<?php $db->disconnect(); ?>