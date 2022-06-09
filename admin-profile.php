<?php
session_start();
include_once('includes/custom-functions.php');
$fn = new custom_functions;
// start session


// set time for session timeout
$currentTime = time() + 25200;
$expired = 3600;

// if session not set go to login page
if (!isset($_SESSION['user'])) {
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

?>

<?php include "header.php"; ?>
<html>

<head>
	<title>Admin Profile | <?= $settings['app_name'] ?> - Dashboard</title>
</head>
</body>
<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
	<?php $username = $_SESSION['user'];

	$sql_query = "SELECT password, email FROM admin WHERE username ='" . $username . "'";
	// create array variable to store previous data
	$data = array();
	$db->sql($sql_query);
	$res = $db->getResult();
	$previous_password = $res[0]['password'];
	$previous_email = $res[0]['email'];

	if (isset($_POST['btnChange'])) {
		if (ALLOW_MODIFICATION == 0 && !defined(ALLOW_MODIFICATION)) {
			echo '<label class="alert alert-danger">This operation is not allowed in demo panel!.</label>';
			return false;
		}
		$email = $db->escapeString($fn->xss_clean($_POST['email']));
		$update_username = $db->escapeString($fn->xss_clean($_POST['username']));
		$old_password = md5($db->escapeString($fn->xss_clean($_POST['old_password'])));
		$new_password = md5($db->escapeString($fn->xss_clean($_POST['new_password'])));
		$confirm_password = md5($db->escapeString($fn->xss_clean($_POST['confirm_password'])));
		// create array variable to handle error
		$error = array();
		// check password
		if (!empty($_POST['old_password']) || !empty($_POST['new_password']) || !empty($_POST['confirm_password'])) {

			if (!empty($_POST['old_password'])) {

				if ($old_password == $previous_password) {
					if ($new_password == $confirm_password) {
						// update password in user table
						if (!empty($_POST['new_password'])) {
							$sql_query = "UPDATE admin 
								SET `password` = '" . $new_password . "',`username`='" . $update_username . "'
								WHERE `username` ='" . $username . "'";
						} else {
							$sql_query = "UPDATE admin 
								SET `username`='" . $update_username . "', `email`='" . $email . "'
								WHERE `username` ='" . $username . "'";
						}
						$db->sql($sql_query);
						$update_result = $db->getResult();
						if ($username != $update_username || !empty($_POST['new_password'])) {
	?>
							<script>
								window.location = "logout.php";
							</script>

				<?php
						}
					} else {
						$error['confirm_password'] = " <span class='label label-danger'>New password don't match!</span>";
					}
				} else {
					$error['old_password'] = " <span class='label label-danger'>Current password wrong!</span>";
				}
			}
		}

		if (empty($email)) {
			$error['email'] = " <span class='label label-danger'>Email required!</span>";
		} else {
			$valid_mail = "/^[_\.0-9a-zA-Z-]+@([0-9a-zA-Z][0-9a-zA-Z-]+\.)+[a-zA-Z]{2,6}$/i";
			if (!preg_match($valid_mail, $email)) {
				$error['email'] = " <span class='label label-danger'>Wrong email format!</span>";
				$email = "";
			} else {
				// update password in user table
				$sql_query = "UPDATE admin SET email = '" . $email . "' WHERE username ='" . $username . "'";
				$db->sql($sql_query);
				$update_result = $db->getResult();
			}
		}
		// check update result
		if (empty($error)) {
			if ($previous_email != $email) {
				$error['update_user'] = " <h4><div class='alert alert-success'>Email updated successfully!</div></h4>"; ?>
				<script>
					window.location = "admin-profile.php";
				</script>
	<?php
			} else {
				$error['update_user'] = " <h4><div class='alert alert-info'>You have made no changes!</div></h4>";
			}
		} else {
			$error['update_user'] = " <h4><div class='alert alert-danger'>
					Failed! Couldn't update password! Try Again
					</div></h4>";
		}
	}

	?>

	<section class="content-header">
		<h1>Administrator</h1>
		<ol class="breadcrumb">
			<li>
				<a href="home.php"> <i class="fa fa-home"></i> Home</a>
			</li>
		</ol>
		<div class="msg"><?php echo isset($error['update_user']) ? $error['update_user'] : ''; ?> </div>
		<hr />
	</section>
	<section class="content">
		<!-- Main row -->
		<div class="row">
			<div class="col-md-6">
				<!-- general form elements -->
				<div class="box box-primary">
					<div class="box-header with-border">
						<h3 class="box-title">Edit Administrator details</h3>
					</div><!-- /.box-header -->
					<!-- form start -->
					<form id='change_password_form' method="post" enctype="multipart/form-data">
						<div class="box-body">
							<div class="form-group">
								<span class="label label-primary">If you change username or password you will need to login again.</span>
							</div>
							<div class="form-group">
								<label for="exampleInputEmail1">Username : </label>
								<input type="text" class="form-control" name="username" id="disabledInput" value="<?php echo $username; ?>" />
							</div>
							<div class="form-group">
								<label for="exampleInputEmail1">Email :</label>
								<div class="msg"><?php echo isset($error['email']) ? $error['email'] : ''; ?></div>
								<input type="email" class="form-control" name="email" value="<?php echo $email; ?>" />
							</div>
							<div class="form-group">
								<label for="exampleInputEmail1">Current Password :</label>
								<div class="msg"><?php echo isset($error['old_password']) ? $error['old_password'] : ''; ?></div>
								<input type="password" class="form-control" name="old_password" />
							</div>
							<div class="form-group">
								<label for="exampleInputEmail1">New Password :</label>
								<div class="msg"><?php echo isset($error['new_password']) ? $error['new_password'] : ''; ?></div>
								<input type="password" class="form-control" name="new_password" id="new_password" />
							</div>
							<div class="form-group">
								<label for="exampleInputEmail1">Re Type New Password :</label>
								<div class="msg"><?php echo isset($error['confirm_password']) ? $error['confirm_password'] : ''; ?></div>
								<input type="password" class="form-control" name="confirm_password" />
							</div>
							<div class="box-footer">
								<input type="submit" class="btn-primary btn" value="Change" name="btnChange" />
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
	$('#change_password_form').validate({
		rules: {
			username: "required",
			old_password: "required",
			email: "required",
			new_password: {
				minlength: 6
			},
			confirm_password: {
				minlength: 6,
				equalTo: '#new_password'
			},
		}
	});
	var data = $('.msg').html();
	if (data != '') {
		$('.msg').show().delay(3000).fadeOut();
	}
</script>