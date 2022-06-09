<div id="content" class="container col-md-12">
	<?php 
	include_once('../includes/custom-functions.php');
	$fn = new custom_functions;
		if(isset($_POST['btnDelete'])){
			if(isset($_GET['id'])){
				$ID = $db->escapeString($fn->xss_clean($_GET['id']));
			}else{
				$ID = "";
			}
			if(ALLOW_MODIFICATION==0 && !defined (ALLOW_MODIFICATION)){
				echo '<label class="alert alert-danger">This operation is not allowed in demo panel!.</label>';
				return false;
			}
			
			// delete data from pemesanan table
			$sql_query = "DELETE FROM orders 
					WHERE ID =".$ID;	
				// Bind your variables to replace the ?s
				// Execute query
				$db->sql($sql_query);
				// store result 
				$delete_result = $db->getResult();
				if(!empty($delete_result)){
					$delete_result=0;
				}else{
					$delete_result=1;
				}
			
			// if delete data success back to pemesanan page
			if($delete_result==1){
				header("location: orders.php");
			}
		}
		if(isset($_POST['btnNo'])){
			header("location: orders.php");
		}


	?>
	<h1>Confirm Action</h1>
	<hr />
	<form method="post">
		<p>Are you sure want to delete this order?</p>
		<input type="submit" class="btn btn-primary" value="Delete" name="btnDelete"/>
		<input type="submit" class="btn btn-danger" value="Cancel" name="btnNo"/>
	</form>
	<div class="separator"> </div>
</div>
			
<?php $db->disconnect(); ?>