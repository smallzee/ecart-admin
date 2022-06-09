<!DOCTYPE html>
<html>
<?php
include('../includes/crud.php');
$db = new Database();
$db->connect();
include('../includes/functions.php');
?>

<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<title>FAQ | eCart App</title>
	<meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
	<!-- Bootstrap 3.3.5 -->
	<link rel="stylesheet" href="../bootstrap/css/bootstrap.min.css">
	<!-- Font Awesome -->
	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.4.0/css/font-awesome.min.css">
	<!-- Ionicons -->
	<link rel="stylesheet" href="https://code.ionicframework.com/ionicons/2.0.1/css/ionicons.min.css">
	<!-- Theme style -->
	<link rel="stylesheet" href="../dist/css/AdminLTE.min.css">
	<link rel="stylesheet" href="../dist/css/skins/_all-skins.min.css">
	<link rel="stylesheet" href="style.css">
	<style>
		.back1 {
			/* min-height: 100px; */
			/* max-height: 400px; */
			width: 100%;
			padding: 0px;
			margin-right: auto;
			margin-left: auto;
			padding-left: 0px;
			padding-right: 0px;
		}
	</style>
</head>

<body>

	<section class="content1">
		<img class="back1" src="../images/faq.png">
	</section><!-- /.content -->

	<?php
	if (isset($_POST['btnAdd'])) {
		$query = $_POST['query'];
		$status = $_POST['status'];
		$error = array();

		if (!empty($query)) {

			// create random image file name
			$function = new functions;

			// insert new data to menu table
			$sql_query = "INSERT INTO faq (question, status)
						VALUES('$query', '$status')";
			// 		echo $sql_query;

			// Bind your variables to replace the ?s

			// Execute query
			// 	$db->sql($sql_query);
			// store result 
			// 	$result = $db->getResult();


			if ($db->sql($sql_query)) {
				$error['add_query'] = " <span class='label label-success'>Successfully added query</span>";
			} else {
				$error['add_query'] = " <span class='label label-danger'>Failed add query</span>";
			}
		}
	}

	// create object of functions class
	$function = new functions;

	// create array variable to store data from database
	$data = array();

	if (isset($_GET['keyword'])) {
		// check value of keyword variable
		$keyword = $function->sanitize($_GET['keyword']);
	} else {
		$keyword = "";
	}

	if (empty($keyword)) {
		$sql_query = "SELECT id, question, answer
				FROM faq where answer != '' ORDER BY id DESC";
	} else {
		$sql_query = "SELECT id, question, answer
					FROM faq where question LIKE '%" . $keyword . "5' 
					ORDER BY id DESC";
	}

	// Execute query
	$db->sql($sql_query);
	// store result 
	$res = $db->getResult();
	// get total records
	$total_records = $db->numRows($res);


	// check page parameter
	if (isset($_GET['page'])) {
		$page = $_GET['page'];
	} else {
		$page = 1;
	}

	// number of data that will be display per page		
	$offset = 10;

	//lets calculate the LIMIT for SQL, and save it $from
	if ($page) {
		$from 	= ($page * $offset) - $offset;
	} else {
		//if nothing was given in page request, lets load the first page
		$from = 0;
	}

	if (empty($keyword)) {
		$sql_query = "SELECT id, question, answer
					FROM faq
					ORDER BY id DESC LIMIT " . $from . ", " . $offset . "";
	} else {
		$sql_query = "SELECT id, question, answer
					FROM faq
					WHERE question LIKE '%" . $keyword . "%' 
					ORDER BY id DESC LIMIT " . $from . ", " . $offset . "";
	}

	// 			echo $sql_query;
	// Execute query
	$db->sql($sql_query);
	// store result
	$res_faq = $db->getResult();
	$total_records_paging = $total_records;


	// if no data on database show "No Reservation is Available"
	if ($total_records_paging == 0) {

	?>
		<section class="content-header">
			<h1>No Questions</h1>
			<div class="box-footer">
				<form method="post" enctype="multipart/form-data">
					<div class="input-group">
						<input class="form-control" name="query" placeholder="Add a Query...">
						<?php echo isset($error['query']) ? $error['query'] : ''; ?>
						<input type="hidden" class="form-control" name="status" value="1" />
						<div class="input-group-btn">
							<button onclick="myFunction()" class="btn btn-success" type="submit" name="btnAdd"><i class="fa fa-plus"></i></button>
							<script>
								function myFunction() {
									windiw.location.reload(true);
								}
							</script>
						</div>
					</div>
				</form>
			</div>
		</section>
		<hr />
	<?php
		// otherwise, show data
	} else {
		$row_number = $from + 1;
	?>


		<!-- ============================================================= -->

		<section id="faq" class="col-md-12">
			<!-- <h2 class="page-header"><a href="#" style="color:#014c8d;">FAQs</a></h2> -->
			<?php 
			foreach ($res_faq as $row) { ?>
				<?PHP if (!empty($row['question']) && !empty($row['answer'])) { ?>

					<h3 style="color:#f47c2e;"><?php echo $row['question']; ?></h3>
					<p class="lead"><?php echo $row['answer']; ?></p>
			<?php }
			} ?>
			<?php echo isset($error['add_query']) ? $error['add_query'] : ''; ?>


			<?php
			// for pagination purpose
			$function->doPages($offset, 'faq.php', '', $total_records, $keyword); ?>

		<?php } ?>
		</section>


		<!-- ============================================================= -->


		<!-- /.content -->
		<!-- /.content-wrapper -->


		<!-- ./wrapper -->

		<!-- jQuery 2.1.4 -->
		<script src="../plugins/jQuery/jQuery-2.1.4.min.js"></script>
		<!-- Bootstrap 3.3.5 -->
		<script src="../bootstrap/js/bootstrap.min.js"></script>
		<!-- FastClick -->
		<script src="../plugins/fastclick/fastclick.min.js"></script>
		<!-- AdminLTE App -->
		<script src="../dist/js/app.min.js"></script>
		<!-- SlimScroll 1.3.0 -->
		<script src="../plugins/slimScroll/jquery.slimscroll.min.js"></script>
		<!-- <script src="https://google-code-prettify.googlecode.com/svn/loader/run_prettify.js"></script> -->
		<!-- <script src="docs.js"></script> -->
</body>

</html>