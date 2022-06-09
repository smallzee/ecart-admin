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


		// delete data from pemesanan table
		$sql_query = "DELETE FROM orders WHERE ID =" . $ID;
		$db->sql($sql_query);
		$delete_result = $db->getResult();
		$sql = "DELETE FROM order_items WHERE order_id =" . $ID;
        $db->sql($sql);
        
        $sql_i = "SELECT * FROM `invoice` where order_id=" . $ID;
        $db->sql($sql_i);
        $invoice_res = $db->getResult();
        if(!empty($invoice_res)){
            $sql_invoice = "DELETE FROM invoice WHERE order_id =" . $ID;
            $db->sql($sql_invoice);
        }
		if (!empty($delete_result)) {
			$delete_result = 0;
		} else {
			$delete_result = 1;
		}
		// if delete data success back to pemesanan page
		if ($db->sql($sql_query)) {
			header("location: orders.php");
		}
	}
	if (isset($_POST['btnNo'])) {
		header("location: orders.php");
	}
	if (isset($_POST['btncancel'])) {
		header("location: orders.php");
	}

	?>
	<?php if ($permissions['orders']['delete'] == 1) { ?>
		<h1>Confirm Action</h1>
		<hr />
		<form method="post">
			<p>Are you sure want to delete this order?</p>
			<input type="submit" class="btn btn-primary" value="Delete" name="btnDelete" />
			<input type="submit" class="btn btn-danger" value="Cancel" name="btnNo" />
		</form>
		<div class="separator"> </div>
	<?php } else { ?>
		<div class="alert alert-danger topmargin-sm">Sorry! you have no permission to delete orders.</div>
		<form method="post">
			<input type="submit" class="btn btn-danger" value="Back" name="btncancel" />
		</form>
	<?php } ?>
</div>

<?php $db->disconnect(); ?>