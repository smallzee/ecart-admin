<?php
include_once('includes/functions.php');
include_once('includes/custom-functions.php');
$fn = new custom_functions;


$ID = (isset($_GET['id'])) ? $db->escapeString($fn->xss_clean($_GET['id'])) : "";

$sql_query = "SELECT image FROM tbl_image WHERE id =" . $ID;
$db->sql($sql_query);
$res = $db->getResult();
if (isset($_POST['btnEdit'])) {
	if (ALLOW_MODIFICATION == 0 && !defined(ALLOW_MODIFICATION)) {
		echo '<label class="alert alert-danger">This operation is not allowed in demo panel!.</label>';
		return false;
	}

	$image = $db->escapeString($fn->xss_clean($_FILES['image']['name']));
	$image_error = $db->escapeString($fn->xss_clean($_FILES['image']['error']));
	$image_type = $db->escapeString($fn->xss_clean($_FILES['image']['type']));

	// create array variable to handle error
	$error = array();
	// common image file extensions
	$allowedExts = array("gif", "jpeg", "jpg", "png");

	// get image file extension
	error_reporting(E_ERROR | E_PARSE);
	$extension = end(explode(".", $_FILES["image"]["name"]));

	if (!empty($image)) {
		if (
			!(($image_type == "image/gif") ||
				($image_type == "image/jpeg") ||
				($image_type == "image/jpg") ||
				($image_type == "image/x-png") ||
				($image_type == "image/png") ||
				($image_type == "image/pjpeg")) &&
			!(in_array($extension, $allowedExts))
		) {
			$error['image'] = "*<span class='label label-danger'>Image type must jpg, jpeg, gif, or png!</span>";
		}
	}
	if (empty($error['image'])) {
		if (!empty($image)) {
			$string = '0123456789';
			$file = preg_replace("/\s+/", "_", $_FILES['image']['name']);
			$function = new functions;
			$image = $function->get_random_string($string, 4) . "-" . date("Y-m-d") . "." . $extension;

			// delete previous image
			$delete = unlink($res[0]['image']);
			// upload new image
			$upload = move_uploaded_file($_FILES['image']['tmp_name'], 'upload/notification/' . $image);

			// updating all data
			$sql_query = "UPDATE tbl_image	SET image = '" . $upload_image . "'WHERE id =" . $ID;

			$upload_image = 'upload/notification/' . $image;
			$db->sql($sql_query);
			$update_result = $db->getResult();
		} else {
			// create random image file name
			$string = '0123456789';
			$file = preg_replace("/\s+/", "_", $_FILES['image']['name']);
			$function = new functions;
			$image = $function->get_random_string($string, 4) . "-" . date("Y-m-d") . "." . $extension;

			// delete previous image
			$delete = unlink($res[0]['image']);

			// upload new image
			$upload = move_uploaded_file($_FILES['image']['tmp_name'], 'upload/notification/' . $image);

			// updating all data
			$sql_query = "UPDATE tbl_imageSET image = '" . $upload_image . "'WHERE id =" . $ID;
			$upload_image = 'upload/notification/' . $image;
			$db->sql($sql_query);
			$update_result = $db->getResult();
			if (!empty($update_result)) {
				$update_result = 0;
			} else {
				$update_result = 1;
			}
		}
		// check update result
		if ($update_result == 1) {
			$error['update_data'] = "<section class='content-header'>
												<span class='label label-success'>Image updated Successfully</span>
												<h4><small><a  href='notification.php'><i class='fa fa-angle-double-left'></i>&nbsp;&nbsp;&nbsp;Back to Notification</a></small></h4>
												</section>";
		} else {
			$error['update_data'] = " <span class='label label-danger'>failed update</span>";
		}
	}
}

// create array variable to store previous data
$data = array();

$sql_query = "SELECT * FROM tbl_image WHERE id =" . $ID;
$db->sql($sql_query);
$res = $db->getResult();
?>
<section class="content-header">
	<h1>
		Edit Image</h1>
	<small><?php echo isset($error['update_data']) ? $error['update_data'] : ''; ?></small>
	<ol class="breadcrumb">
		<li><a href="home.php"><i class="fa fa-home"></i> Home</a></li>
	</ol>
</section>
<section class="content">
	<!-- Main row -->
	<div class="row">
		<div class="col-md-6">
			<!-- general form elements -->
			<div class="box box-primary">
				<div class="box-header with-border">
					<h3 class="box-title">Edit Image</h3>
				</div><!-- /.box-header -->
				<!-- form start -->
				<form method="post" enctype="multipart/form-data">
					<div class="box-body">

						<div class="form-group">
							<label for="exampleInputFile">Image</label><?php echo isset($error['image']) ? $error['image'] : ''; ?>
							<input type="file" name="image" id="image" /><br />
							<img src="<?php echo $res[0]['image']; ?>" width="210" height="160" />
						</div>

					</div><!-- /.box-body -->

					<div class="box-footer">
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