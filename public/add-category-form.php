<?php
include_once('includes/functions.php');
$function = new functions;
include_once('includes/custom-functions.php');
$fn = new custom_functions;

?>
<?php
if (isset($_POST['btnAdd'])) {
	if (ALLOW_MODIFICATION == 0 && !defined(ALLOW_MODIFICATION)) {
		echo '<label class="alert alert-danger">This operation is not allowed in demo panel!.</label>';
		return false;
	}
	if ($permissions['categories']['create'] == 1) {

		$category_name = $db->escapeString($fn->xss_clean($_POST['category_name']));
		$category_subtitle = $db->escapeString($fn->xss_clean($_POST['category_subtitle']));

		// get image info
		$menu_image = $db->escapeString($fn->xss_clean($_FILES['category_image']['name']));
		$image_error = $db->escapeString($fn->xss_clean($_FILES['category_image']['error']));
		$image_type = $db->escapeString($fn->xss_clean($_FILES['category_image']['type']));

		// create array variable to handle error
		$error = array();

		if (empty($category_name)) {
			$error['category_name'] = " <span class='label label-danger'>Required!</span>";
		}
		if (empty($category_subtitle)) {
			$error['category_subtitle'] = " <span class='label label-danger'>Required!</span>";
		}

		// common image file extensions
		$allowedExts = array("gif", "jpeg", "jpg", "png");

		// get image file extension
		error_reporting(E_ERROR | E_PARSE);
		$extension = end(explode(".", $_FILES["category_image"]["name"]));

		if ($image_error > 0) {
			$error['category_image'] = " <span class='label label-danger'>Not Uploaded!!</span>";
		} else {
            $result = $fn->validate_image($_FILES["category_image"]);
            if ($result) {
                $error['category_image'] = " <span class='label label-danger'>Image type must jpg, jpeg, gif, or png!</span>";
            }
			// $mimetype = mime_content_type($_FILES["category_image"]["tmp_name"]);
			// if (!in_array($mimetype, array('image/jpg', 'image/jpeg', 'image/gif', 'image/png'))) {
			// 	$error['category_image'] = " <span class='label label-danger'>Image type must jpg, jpeg, gif, or png!</span>";
			// }
		}

		if (!empty($category_name) && !empty($category_subtitle) && empty($error['category_image'])) {

			// create random image file name
			$string = '0123456789';
			$file = preg_replace("/\s+/", "_", $_FILES['category_image']['name']);
			$menu_image = $function->get_random_string($string, 4) . "-" . date("Y-m-d") . "." . $extension;

			// upload new image
			$upload = move_uploaded_file($_FILES['category_image']['tmp_name'], 'upload/images/' . $menu_image);

			// insert new data to menu table
			$upload_image = 'upload/images/' . $menu_image;
			$sql_query = "INSERT INTO category (name,subtitle, image,web_image)VALUES('$category_name', '$category_subtitle', '$upload_image','')";
			$db->sql($sql_query);
			$result = $db->getResult();
			if (!empty($result)) {
				$result = 0;
			} else {
				$result = 1;
			}

			if ($result == 1) {
				$error['add_category'] = " <section class='content-header'><span class='label label-success'>Category Added Successfully</span></section>";
			} else {
				$error['add_category'] = " <span class='label label-danger'>Failed add category</span>";
			}
		}
	} else {
		$error['check_permission'] = " <section class='content-header'><span class='label label-danger'>You have no permission to create category</span></section>";
	}
}
?>
<section class="content-header">
    <h1>Add Category <small><a href='categories.php'> <i class='fa fa-angle-double-left'></i>&nbsp;&nbsp;&nbsp;Back to Categories</a></small></h1>

    <?php echo isset($error['add_category']) ? $error['add_category'] : ''; ?>
    <ol class="breadcrumb">
        <li><a href="home.php"><i class="fa fa-home"></i> Home</a></li>
    </ol>
    <hr />
</section>
<section class="content">
    <div class="row">
        <div class="col-md-6">
            <?php if ($permissions['categories']['create'] == 0) { ?>
                <div class="alert alert-danger">You have no permission to create category.</div>
            <?php } ?>
            <!-- general form elements -->
            <div class="box box-primary">
                <div class="box-header with-border">
                    <h3 class="box-title">Add Category</h3>

                </div><!-- /.box-header -->
                <!-- form start -->
                <form method="post" enctype="multipart/form-data">
                    <div class="box-body">
                        <div class="form-group">
                            <label for="exampleInputEmail1">Category Name</label><?php echo isset($error['category_name']) ? $error['category_name'] : ''; ?>
                            <input type="text" class="form-control" name="category_name" required>
                        </div>
                        <div class="form-group">
                            <label for="exampleInputEmail1">Category Subtitle</label><?php echo isset($error['category_subtitle']) ? $error['category_subtitle'] : ''; ?>
                            <input type="text" class="form-control" name="category_subtitle" required>
                        </div>

                        <div class="form-group">
                            <label for="exampleInputFile">Image&nbsp;&nbsp;&nbsp;*Please choose square image of larger than 350px*350px & smaller than 550px*550px.</label><?php echo isset($error['category_image']) ? $error['category_image'] : ''; ?>
                            <input type="file" name="category_image" id="category_image" required />
                        </div>
                    </div><!-- /.box-body -->

                    <div class="box-footer">
                        <button type="submit" class="btn btn-primary" name="btnAdd">Add</button>
                        <input type="reset" class="btn-warning btn" value="Clear" />

                    </div>

                </form>

            </div><!-- /.box -->
            <?php echo isset($error['check_permission']) ? $error['check_permission'] : ''; ?>
        </div>
    </div>
</section>

<div class="separator"> </div>

<?php $db->disconnect(); ?>