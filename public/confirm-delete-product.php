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

		$product_id = $fn->get_product_id_by_variant_id($ID);

		$sql_query = "DELETE FROM cart WHERE product_id = " . $product_id . " AND product_variant_id = " . $ID;
		$db->sql($sql_query);
		$sql_query = "DELETE FROM product_variant WHERE product_id=" . $product_id;
		$db->sql($sql_query);

		$sql = "SELECT count(id) as total from product_variant where product_id=" . $product_id;
		$db->sql($sql);
		$total = $db->getResult();
		// get image file from menu table
		if ($total[0]['total'] == 0) {
			$sql_query = "SELECT image FROM products WHERE id =" . $product_id;
			$db->sql($sql_query);
			$res = $db->getResult();
			unlink($res[0]['image']);

			$sql_query = "SELECT other_images FROM products WHERE id =" . $product_id;
			$db->sql($sql_query);
			$res = $db->getResult();
			if (!empty($res[0]['other_images'])) {
				$other_images = json_decode($res[0]['other_images']);
				foreach ($other_images as $other_image) {
					unlink($other_image);
				}
			}

			$sql_query = "DELETE FROM products WHERE id =" . $product_id;
			$db->sql($sql_query);

			$sql_query = "DELETE FROM favorites WHERE product_id = " . $product_id;
			$db->sql($sql_query);
		}
		header("location: products.php");
	}

	if (isset($_POST['btnNo'])) {
		header("location: products.php");
	}
	if (isset($_POST['btncancel'])) {
		header("location: products.php");
	}

	?>
	<?php
	if ($permissions['products']['delete'] == 1) { ?>
		<h1>Confirm Action</h1>
		<hr />
		<form method="post">
			<p>Are you sure want to delete this Product? All related data will also deleted.</p>
			<input type="submit" class="btn btn-primary" value="Delete" name="btnDelete" />
			<input type="submit" class="btn btn-danger" value="Cancel" name="btnNo" />
		</form>
		<div class="separator"> </div>
	<?php } else { ?>
		<div class="alert alert-danger topmargin-sm" style="margin-top: 20px;">You have no permission to delete product.</div>
		<form method="post">
			<input type="submit" class="btn btn-danger" value="Back" name="btncancel" />
		</form>

	<?php } ?>
</div>

<?php $db->disconnect(); ?>