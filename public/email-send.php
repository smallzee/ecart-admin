<?php
include_once('includes/functions.php');
include_once('includes/custom-functions.php');
$fn = new custom_functions;
?>

<?php
if (isset($_POST['btnSEND'])) {
	$from = "info@satvikmall.com";
	$to = implode(", ", $db->escapeString($fn->xss_clean($_POST["emails"])));
	$subject = $db->escapeString($fn->xss_clean($_POST['subject']));
	$message = $db->escapeString($fn->xss_clean($_POST['message']));
	$headers = "From:" . $from;
	$headers .= "Reply-To: " . $from . "\n";
	$headers = "Cc:" . $to . " \r\n";
	$headers .= "MIME-Version: 1.0\r\n";
	$headers .= "Content-type: text/html\r\nX-Mailer: PHP/" . phpversion();
	ini_set("sendmail_from", $from);
	$ok = mail($to, $subject, $message, $headers, "-f" . $from);
	if ($ok) {
		echo '<script type="text/javascript">';
		echo 'alert("Email send successfully");';
		echo 'window.location.href = "email.php";';
		echo '</script>';
	} else {
		echo '<script type="text/javascript">';
		echo 'alert("There is some errors here, try again later..");';
		echo 'window.location.href = "email.php";';
		echo '</script>';
	}
}

?>
<section class="content-header">
	<h1>Discount</h1>
	<ol class="breadcrumb">
		<li><a href="../home.php"><i class="fa fa-home"></i> Home</a></li>
	</ol>
	<hr />
</section>
<section class="content">
	<div class="row">
		<div class="col-md-7">
			<div class="box box-info">
				<div class="box-header">
					<i class="fa fa-envelope"></i>
					<h3 class="box-title">Quick Email</h3>
					<!-- tools box -->
					<div class="pull-right box-tools">
						<button class="btn btn-info btn-sm" data-widget="remove" data-toggle="tooltip" title="Remove"><i class="fa fa-times"></i></button>
					</div><!-- /. tools -->
				</div>
				<div class="box-body">
					<form method="post">
						<div class="form-group">
							<select name="emails[]" style="width:97%; border-radius:none;" placeholder="Select Email Address" multiple="multiple">
								<?php
								$sql = "select email from users";
								$db->sql($sql);
								$res = $db->getResult();
								foreach ($res as $row) { ?>
									<option><?php echo $row['email']; ?></option>
								<?php } ?>
							</select>
						</div>
						<div class="form-group">
							<input type="text" class="form-control" style="border-radius:6px;" name="subject" placeholder="Subject">
						</div>
						<div>
							<textarea class="textarea" name="message" placeholder="Message" style="width: 100%; height: 125px; font-size: 14px; line-height: 18px; border-radius:6px; border: 1px solid #dddddd; padding: 10px;"></textarea>
						</div>
						<div class="box-footer clearfix">
							<button class="pull-right btn btn-default" name="btnSEND" type="submit">Send <i class="fa fa-arrow-circle-right"></i></button>
						</div>
					</form>
				</div>

			</div>
			<!-- general form elements -->

		</div><!-- /.box -->
	</div>
	</div>
</section>

<div class="separator"> </div>
<script src="dist/js/multiple-select.js"></script>
<script>
	$('select').multipleSelect()({
		placeholder: 'Select Email Address',
		filter: true,
		width: 1000,
		multiple: true,
		multipleWidth: 1000
	});
</script>
<?php $db->disconnect(); ?>