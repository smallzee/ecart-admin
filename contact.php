<?php include "header.php"; ?>
<!--header end -->
<!--Breadcrumb start-->
<html>

<head>
	<title>Contact | <?= $settings['app_name'] ?> - Dashboard</title>
</head>

<body>
	<div class="ed_pagetitle" data-stellar-background-ratio="0.5" data-stellar-vertical-offset="0" style="background-image: url(images/content/brdcrm_bg.png);">
		<div class="container">
			<div class="row">
				<div class="col-lg-6 col-md-4 col-sm-6">
					<div class="page_title">
						<h2>અમારો સંપર્ક </h2>
					</div>
				</div>
				<div class="col-lg-6 col-md-8 col-sm-6">
					<ul class="breadcrumb">
						<li><a href="index.php">મુખ્ય પાનું</a></li>
						<li><i class="fa fa-chevron-left"></i></li>
						<li><a href="contact.php">અમારો સંપર્ક </a></li>
					</ul>
				</div>
			</div>
		</div>
	</div>
	<!--Breadcrumb end-->
	<!--Section fourteen Contact form start-->
	<div class="ed_transprentbg ed_toppadder80 ed_bottompadder80">
		<div class="container">
			<div class="row">
				<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
					<div class="ed_heading_top">
						<h3>સંપર્ક ફોર્મ</h3>
					</div>
				</div>
				<form method="post">
					<div class="ed_contact_form ed_toppadder60">
						<div class="col-lg-6 col-md-6 col-sm-12">
							<div class="form-group">
								<input type="text" id="uname" name="uname" class="form-control" placeholder="તમારું નામ">
							</div>
							<div class="form-group">
								<input type="email" id="umail" name="umail" class="form-control" placeholder="તમારું ઇમેઇલ">
							</div>
							<div class="form-group">
								<input type="text" id="sub" name="sub" class="form-control" placeholder="વિષય">
							</div>
						</div>
						<div class="col-lg-6 col-md-6 col-sm-12">
							<div class="form-group">
								<textarea id="msg" name="msg" class="form-control" rows="6" placeholder="સંદેશો "></textarea>
							</div>
							<button type="submit" id="ed_submit" name="submit" class="btn ed_btn ed_orange pull-right">મોકલો </button>
							<p id="err"></p>
						</div>
					</div>
				</form>
				<?php
				include_once('includes/custom-functions.php');
				$fn = new custom_functions;
				if (isset($_POST['submit'])) {
					$email_to = "jaydeepjgiri@yahoo.com";
					$firstname = $db->escapeString($fn->xss_clean($_POST["uname"]));
					$email_from = $db->escapeString($fn->xss_clean($_POST["umail"]));
					$message = $db->escapeString($fn->xss_clean($_POST["msg"]));
					$email_subject = $db->escapeString($fn->xss_clean($_POST["sub"]));
					$headers = "From: " . $email_from . "\n";
					$headers .= "Reply-To: " . $email_from . "\n";
					$message = "Name: " . $firstname . "\r\nMessage: " . $message;
					ini_set("sendmail_from", $email_from);
					$sent = mail($email_to, $email_subject, $message, $headers, "-f" . $email_from);
					if ($sent) {
						echo "<script>alert('Your message successfully sent');top.location='contact.php';</script>";
					} else {
						echo "There has been an error sending your message. Please try later.";
					}
				}


				?>
			</div>
		</div>
	</div>
	<!--Section fourteen Contact form start-->
	<!--Section fifteen Contact form start-->
	<div class="ed_event_single_contact_address ed_toppadder70 ed_bottompadder70">
		<div class="container">
			<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
				<div class="ed_heading_top ed_bottompadder70">
					<h3>Contact & Find</h3>
				</div>
			</div>
			<div class="col-lg-6 col-md-6 col-sm-6">
				<div class="row">
					<div class="ed_event_single_address_info ed_toppadder50 ed_bottompadder50">
						<h4 class="ed_bottompadder30">contact info</h4>
						<p class="ed_bottompadder40 ed_toppadder10">You can always reach us via following contact details. We will give our best to reach you as possible.</p>
						<p>Phone: <span>1-220-090-080</span></p>
						<p>Email: <a href="#">info@infinitietech.com</a></p>
						<p>Website: <a href="#">https://www.infinitietech.com</a></p>
					</div>
				</div>
			</div>
			<div class="col-lg-6 col-md-6 col-sm-6">
				<div class="row">
					<div class="ed_event_single_address_map">
						<div id="map"></div>
					</div>
				</div>
			</div>
		</div>
	</div>
	<!--Section fifteen Contact form start-->
	<!--Newsletter Section six start-->
	<?php include "footer.php"; ?>
	<!--main js file end-->
</body>

</html>