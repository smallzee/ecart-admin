<div id="content" class="container col-md-12">
	<?php
	include_once('includes/custom-functions.php');
	$fn = new custom_functions;
	if (isset($_POST['btnDelete'])) {
		if (ALLOW_MODIFICATION == 0 && !defined(ALLOW_MODIFICATION)) {
			echo '<label class="alert alert-danger">This operation is not allowed in demo panel!.</label>';
			return false;
		}

		$ID = (isset($_GET['id'])) ? $db->escapeString($fn->xss_clean($_GET['id'])) : "";
		$sql_query = "SELECT image FROM subcategory WHERE id =" . $ID;
		$db->sql($sql_query);
		$res = $db->getResult();
		// delete image file from directory
		$delete = unlink($res[0]['image']);

		// delete data from menu table
		$sql_query = "DELETE FROM subcategory WHERE id =" . $ID;
		$db->sql($sql_query);
		$delete_subcategory_result = $db->getResult();
		if (!empty($delete_subcategory_result)) {
			$delete_subcategory_result = 0;
		}
		$delete_subcategory_result = 1;

		// get image file from table
		$sql_query = "SELECT image,other_images FROM products WHERE subcategory_id =" . $ID;
		$db->sql($sql_query);
		$res = $db->getResult();
		// delete all menu image files from directory
		foreach ($res as $row) {
			unlink($res[0]['image']);
		}

		$sql_query = "DELETE FROM products WHERE subcategory_id =" . $ID;
		$db->sql($sql_query);
		$delete_product_result = $db->getResult();
		if (!empty($delete_product_result)) {
			$delete_product_result = 0;
		}
		$delete_product_result = 1;
		if ($delete_subcategory_result == 1 && $delete_product_result = 1) {
			header("location: subcategories.php");
		}
	}

	if (isset($_POST['btnNo'])) {
		header("location: subcategories.php");
	}
	if (isset($_POST['btncancel'])) {
		header("location: subcategories.php");
	}

	?>
	<?php
	if ($permissions['subcategories']['delete'] == 1) { ?>
		<h1>Confirm Action</h1>
		<hr />
		<form method="post">
			<p>Are you sure want to delete this Subcategory?All the Products will also be Deleted.</p>
			<input type="submit" class="btn btn-primary" value="Delete" name="btnDelete" />
			<input type="submit" class="btn btn-danger" value="Cancel" name="btnNo" />
		</form>
		<div class="separator"> </div>
	<?php } else { ?>
		<div class="alert alert-danger topmargin-sm" style="margin-top: 20px;">You have no permission to delete subcategory.</div>
		<form method="post">
			<input type="submit" class="btn btn-danger" value="Back" name="btncancel" />
		</form>
	<?php }  ?>
</div>

<?php $db->disconnect(); ?>