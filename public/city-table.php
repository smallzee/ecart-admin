<?php
include_once('includes/functions.php');
?>

<!-- Content Wrapper. Contains page content -->
<!-- Content Header (Page header) -->
<?php
// create object of functions class
$function = new functions;

// create array variable to store data from database
$data = array();

if (isset($_GET['keyword']) && !empty($_GET['keyword'])) {
	// check value of keyword variable
	$keyword = $function->sanitize($_GET['keyword']);
} else {
	$keyword = "";
}

if (empty($keyword)) {
	$sql_query = "SELECT count(*)
				as total_records FROM city WHERE name!='Choose Your City'
				ORDER BY id DESC";
} else {
	$sql_query = "SELECT count(*)
				as total_records FROM city
				WHERE name LIKE '%" . $keyword . "%' and name!='Choose Your City'
				ORDER BY id DESC";
}
$db->sql($sql_query);
$res = $db->getResult();
foreach ($res as $row) {
	$total_records = $row['total_records'];
}

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
	$sql_query = "SELECT id, name
				FROM city where name!='Choose Your City'
				ORDER BY id DESC LIMIT " . $from . "," . $offset . "";
} else {
	$sql_query = "SELECT id, name 
				FROM city
				WHERE name LIKE '%" . $keyword . "%' and name!='Choose Your City'
				ORDER BY id DESC LIMIT " . $from . "," . $offset . "";
}

// Execute query
$db->sql($sql_query);
// store result 
$res = $db->getResult();

// for paging purpose
$total_records_paging = $total_records;

// if no data on database show "No Reservation is Available"
if ($permissions['locations']['read'] == 1) {
	if ($total_records_paging == 0) {

?>
		<section class="content-header">
			<h1>City Not Available</h1>
			<ol class="breadcrumb">
				<li><a href="home.php"><i class="fa fa-home"></i> Home</a></li>
			</ol>
			<ol class="breadcrumb">
				<a href="add-city.php">
					<button class="btn btn-block btn-default"><i class="fa fa-plus-square"></i>Add New City</button>
				</a>
			</ol>
			<hr />
		<?php
		// otherwise, show data
	} else {
		$row_number = $from + 1;
		?>
			<section class="content-header">
				<h1>
					Cities
					<small></small>
				</h1>
				<ol class="breadcrumb">
					<a class="btn btn-block btn-default" href="add-city.php"><i class="fa fa-plus-square"></i> Add New City</a>
				</ol>
			</section>

			<!-- Main content -->

			<section class="content">
				<!-- Main row -->

				<div class="row">
					<!-- Left col -->
					<div class="col-xs-12">
						<div class="box">
							<div class="box-header">
								<h3 class="box-title">Cities</h3>
								<div class="box-tools">
									<form method="get">
										<div class="input-group" style="width: 150px;">

											<input type="text" name="keyword" class="form-control input-sm pull-right" placeholder="Search">
											<div class="input-group-btn">
												<button type="submit" class="btn btn-sm btn-default"><i class="fa fa-search"></i></button>
											</div>

										</div>
									</form>
								</div>
							</div><!-- /.box-header -->
							<div class="box-body table-responsive">
								<table class="table table-hover">
									<tr>
										<th>No.</th>
										<th>Name</th>
										<th>Action</th>
									</tr>
									<?php
									$count = 1;

									foreach ($res as $row) { ?>
										<tr>
											<td><?= $count; ?></td>
											<td><?php echo $row['name']; ?></td>
											<td>
												<a onclick="myFunction()" href="view-city-area.php?id=<?php echo $row['id']; ?>  "><i class="fa fa-folder-open-o"></i>View Areas</a>

												<script>
													function myFunction() {
														location.reload();
													}
												</script>
												<a href="edit-city.php?id=<?php echo $row['id']; ?>"><i class="fa fa-edit"></i>Edit</a>&nbsp;&nbsp;&nbsp;&nbsp;
												<a href="delete-city.php?id=<?php echo $row['id']; ?>"><i class="fa fa-trash-o"></i>Delete</a>&nbsp;&nbsp;&nbsp;&nbsp;

											</td>
										</tr>
								<?php $count++;
									}
								}
							} else { ?>
								<div class="alert alert-danger topmargin-sm" style="margin-top: 20px;">You have no permission to view cities</div>
							<?php } ?>
								</table>
							</div><!-- /.box-body -->
						</div><!-- /.box -->
					</div>
					<div class="col-sx-12">
						<h4>
							<?php
							// for pagination purpose
							$function->doPages($offset, 'city.php', '', $total_records, $keyword); ?>
						</h4>
					</div>
					<div class="separator"> </div>
					<!-- right col (We are only adding the ID to make the widgets sortable)-->
				</div><!-- /.row (main row) -->

			</section><!-- /.content -->