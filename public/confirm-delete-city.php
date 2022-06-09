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

		// get image file from table
		$sql_query = "SELECT name FROM city WHERE id =" . $ID;
		$db->sql($sql_query);
		$res = $db->getResult();
		$sql_query = "DELETE FROM city WHERE id =" . $ID;
		$db->sql($sql_query);
		$delete_city_result = $db->getResult();
		if (!empty($delete_city_result)) {
			$delete_city_result = 0;
		} else {
			$delete_city_result = 1;
		}
		// get image file from table
		$sql_query = "SELECT name FROM area WHERE city_id =" . $ID;
		$db->sql($sql_query);
		$res = $db->getResult();
		// delete data from menu table
		$sql_query = "DELETE FROM area WHERE city_id =" . $ID;
		$db->sql($sql_query);
		$delete_area_result = $db->getResult();
		if (!empty($delete_area_result)) {
			$delete_area_result = 0;
		} else {
			$delete_area_result = 1;
		}
		// if delete data success back to reservation page
		if ($delete_city_result == 1 && $delete_area_result == 1) {
			header("location: city.php");
		}
	}

	if (isset($_POST['btnNo'])) {
		header("location: city.php");
	}
	if (isset($_POST['btncancel'])) {
		header("location: city.php");
	}

	?>
	<?php if ($permissions['locations']['delete'] == 1) { ?>
		<h1>Confirm Action</h1>
		<hr />
		<form method="post">
			<p>Are you sure want to delete this city?Related city area will also deleted.</p>
			<input type="submit" class="btn btn-primary" value="Delete" name="btnDelete" />
			<input type="submit" class="btn btn-danger" value="Cancel" name="btnNo" />
		</form>
		<div class="separator"> </div>
	<?php } else { ?>
		<div class="alert alert-danger topmargin-sm">You have no permission to delete city.</div>
		<form method="post">
			<input type="submit" class="btn btn-danger" value="Back" name="btncancel" />
		</form>
	<?php } ?>
</div>
<?php $db->disconnect(); ?>