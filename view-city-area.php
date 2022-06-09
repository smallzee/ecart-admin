<?php

session_start();

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
include 'header.php'; ?>

<head>
	<title>View City Area | <?= $settings['app_name'] ?> - Dashboard</title>
</head>
<?php
include_once('includes/custom-functions.php');
$fn = new custom_functions;

if (isset($_GET['id'])) {
	$ID = $db->escapeString($fn->xss_clean($_GET['id']));
} else {
	$ID = "";
}
// get image file from table
// get image file from table
$db->select('area', '*', null, "city_id = $ID and name!='Choose Your Area'");
$res = $db->getResult();
// create array variable to store menu image
?>
<?php
if ($db->numRows($result) == 0) { ?>
	<div class="content-wrapper">
		<section class="content-header">
			<h1>
				No Areas Available
				<small><a href='city.php'><i class='fa fa-angle-double-left'></i>&nbsp;&nbsp;&nbsp;Back to Cities</a></small>
			</h1>
		</section>
	</div>
<?php } else {
?>

	<div class="content-wrapper">
		<section class="content">
			<!-- Main row -->

			<div class="row">
				<!-- Left col -->
				<div class="col-xs-12">
					<?php if ($permissions['locations']['read'] == 1) { ?>
						<div class="box">
							<div class="box-header">
								<h3 class="box-title">City_ID : <?php echo $ID; ?></h3>
								<div class="box-tools">
								</div>
							</div><!-- /.box-header -->
							<div class="box-body table-responsive">
								<table class="table table-hover">
									<tr>
										<th>No.</th>
										<th>Area Name</th>
										<th>Action</th>
									</tr>
									<?php
									$count = 1;
									// delete all menu image files from directory
									foreach ($res as $row) {
									?>
										<tr>
											<td><?php echo $count; ?></td>
											<td><?php echo $row['name']; ?></td>
											<td><a href="edit-area.php?id=<?php echo $row['id']; ?>"><i class="fa fa-edit"></i>Edit</a></td>
										</tr>
									<?php $count++;
									} ?>
								</table>
							</div><!-- /.box-body -->
						</div><!-- /.box -->
					<?php } else { ?>
						<div class="alert alert-danger">You have no permission to view areas</div>
					<?php } ?>
					<a href='city.php'><i class='fa fa-angle-double-left'></i>&nbsp;&nbsp;&nbsp;Back to cities</a>
				</div>
				<!-- right col (We are only adding the ID to make the widgets sortable)-->
			</div><!-- /.row (main row) -->

		</section><!-- /.content -->
	</div>
<?php } ?>
<?php include 'footer.php'; ?>