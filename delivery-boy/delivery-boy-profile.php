<?php
// start session

session_start();

// set time for session timeout
$currentTime = time() + 25200;
$expired = 3600;

// if session not set go to login page
if (!isset($_SESSION['delivery_boy_id']) && !isset($_SESSION['name'])) {
	header("location:index.php");
}

// if current time is more than session timeout back to login page
if ($currentTime > $_SESSION['timeout']) {
	session_destroy();
	header("location:index.php");
}

// destroy previous session timeout and create new one
unset($_SESSION['timeout']);
$_SESSION['timeout'] = $currentTime + $expired;

include "header.php"; ?>
<html>

<head>
	<title>Delivery Boy Profile | <?= $settings['app_name'] ?> - Dashboard</title>
</head>

<body>
	<!-- Content Wrapper. Contains page content -->
	<div class="content-wrapper">
		<?php $id = $_SESSION['delivery_boy_id'];
		$sql_query = "SELECT * FROM delivery_boys 
	WHERE id ='" . $id . "'";
		// create array variable to store previous data
		$data = array();
		// Execute query
		$db->sql($sql_query);
		// store result 
		$res = $db->getResult();
		$previous_password = $res[0]['password'];
		?>

		<section class="content-header">
			<h1>Delivery Boy</h1>
			<ol class="breadcrumb">
				<li>
					<a href="home.php"> <i class="fa fa-home"></i> Home</a>
				</li>
			</ol>
			<?php echo isset($error['update_user']) ? $error['update_user'] : ''; ?>
			<hr />
		</section>
		<section class="content">
			<!-- Main row -->

			<div class="row">
				<div class="col-md-6">
					<!-- general form elements -->
					<div class="box box-primary">
						<div class="box-header with-border">
							<h3 class="box-title">Edit Delivery Boy details</h3>
						</div><!-- /.box-header -->
						<!-- form start -->
						<?php 
							$style = $path = $dr_img = $nic_img = "";
							if(empty($res[0]['driving_license']) && empty($res[0]['national_identity_card'])){
								$style = "style='display:none;'";
							}else{
								$path = DOMAIN_URL.'upload/delivery-boy/';
								$dr_img = (!empty($res[0]['driving_license'])) ? $res[0]['driving_license'] : "No Image" ;
								$nic_img = (!empty($res[0]['national_identity_card'])) ? $res[0]['national_identity_card'] : "No Image" ;
							}
							
						?>
						<form id='update_form' method="post" action="db-operation.php">
							<input type='hidden' name="delivery_boy_id" id="delivery_boy_id" value='<?= $res[0]['id']; ?>' />
							<input type='hidden' name="update_delivery_boy" id="update_delivery_boy" value='1' />
							<input type='hidden' name="dr_image1" id="dr_image" value='<?php echo $dr_img; ?>' />
                            <input type='hidden' name="nic_image" id="nic_image" value='<?php echo $nic_img; ?>' />
							<div class="box-body">
								<div class="form-group">
									<label for="">Name :</label>
									<input type="text" class="form-control" name="update_name" id="update_name" value="<?php echo $res[0]['name']; ?>" />
								</div>
								<div class="form-group">
									<label for="">Mobile :</label>
									<input type="number" class="form-control" name="mobile" value="<?php echo $res[0]['mobile']; ?>" readonly />
								</div>
								<div class="form-group">
									<label class="" for="">Address</label>
									<textarea name="update_address" id="update_address" style=" min-width:500px; max-width:100%;min-height:100px;height:100%;width:100%;"><?= $res[0]['address']; ?></textarea>
								</div>
								<div class="form-group">
									<label for="">Old Password :</label><?php echo isset($error['old_password']) ? $error['old_password'] : ''; ?><small>( Leave it blank for no change )</small>
									<input type="password" class="form-control" name="old_password" id="old_password" />
								</div>
								<div class="form-group">
									<label for="">New Password :</label>
									<input type="password" class="form-control" name="update_password" id="update_password" />
								</div>
								<div class="form-group">
									<label for="">Re Type New Password :</label>
									<input type="password" class="form-control" name="confirm_password" id="confirm_password" />
								</div>
								<div class="form-group">
									<a data-lightbox='product' <?php echo $style;?> id="dr_container" href='<?= $path.$dr_img?>'><img id="dr_img" src='<?= $path.$dr_img?>' height='50' /></a><br>
								</div>
								<div class="form-group">
									<label for="exampleInputFile">Driving License</label><?php //echo isset($error['category_image']) ? $error['category_image'] : ''; ?>
									<input type="file" name="update_driving_license" id="update_driving_license" /><br>
								</div>
								<div class="form-group">
									<a data-lightbox='product' <?php echo $style;?> id="nic_container" href='<?= $path.$nic_img?>'><img id="nic_img" src='<?= $path.$nic_img?>' height='50' /></a><br>
								</div>
								<div class="form-group">
									<label for="exampleInputFile">National Identity Card</label><?php //echo isset($error['category_image']) ? $error['category_image'] : ''; ?>
									<input type="file" name="update_national_identity_card" id="update_national_identity_card" /><br>
								</div>
								<div class="form-group">
									<label for="">Date Of Birth</label>
									<input type="date" class="form-control" name="update_dob" id="update_dob" value="<?php echo $res[0]['dob']; ?>" required>
								</div>
								<div class="form-group">
									<label for="">Bank Name</label>
									<input type="text" class="form-control" name="update_bank_name" id="update_bank_name" value="<?php echo $res[0]['bank_name']; ?>" required>
								</div>
								<div class="form-group">
									<label for="">Account Number</label>
									<input type="text" class="form-control" name="update_account_number" id="update_account_number" value="<?php echo $res[0]['bank_account_number']; ?>" required>
								</div>
								<div class="form-group">
									<label for="">Bank Account Name</label>
									<input type="text" class="form-control" name="update_account_name" id="update_account_name" value="<?php echo $res[0]['account_name']; ?>" required>
								</div>
								<div class="form-group">
									<label for="">Bank's IFSC Code</label>
									<input type="text" class="form-control" name="update_ifsc_code" id="update_ifsc_code" value="<?php echo $res[0]['ifsc_code']; ?>" required>
								</div>
								<div class="form-group">
									<label for="">Other Payment Information</label>
									<textarea name="update_other_payment_info" id="update_other_payment_info" rows='3' class="form-control"><?php echo $res[0]['other_payment_information']; ?></textarea>
								</div>
								<div class="box-footer">
									<input type="submit" class="btn-primary btn" value="Change" id="btnChange" />
								</div>
								<div class="form-group">
									<div class="row">
										<div class="col-md-offset-3 col-md-8" style="display:none;" id="update_result"></div>
									</div>
								</div>
							</div><!-- /.box -->
						</form>
					</div>
				</div>
		</section>
		<div class="separator"> </div>
	</div><!-- /.content-wrapper -->
</body>

</html>
<?php include "footer.php"; ?>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.17.0/jquery.validate.min.js"></script>
<script>
	$('#update_form').validate({
		rules: {
			update_name: "required",
			update_address: "required",
			confirm_password: {
				equalTo: '#update_password'
			},
		}
	});
</script>
<script>
	$('#update_form').on('submit', function(e) {
		e.preventDefault();
		var formData = new FormData(this);
		if ($("#update_form").validate().form()) {
			<?php 
				 if(ALLOW_MODIFICATION==0 && !defined (ALLOW_MODIFICATION)){
					echo '<label class="alert alert-danger">This operation is not allowed in demo panel!.</label>';
					return false;
				}	
			?>
			//if(confirm('Are you sure?Want to Update Delivery Boy')){
			$.ajax({
				type: 'POST',
				url: $(this).attr('action'),
				data: formData,
				beforeSend: function() {
					$('#btnChange').html('Please wait..');
				},
				cache: false,
				contentType: false,
				processData: false,
				success: function(result) {
					$('#update_result').html(result);
					$('#update_result').show().delay(6000).fadeOut();
					$('#btnChange').html('Change');
				}
			});
			//}
		}
	});
</script>