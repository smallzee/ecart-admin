<?php
	session_start();
	header("Expires: on, 01 Jan 1970 00:00:00 GMT");
	header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
	header("Cache-Control: no-store, no-cache, must-revalidate");
	header("Cache-Control: post-check=0, pre-check=0", false);
	header("Pragma: no-cache");
	// start session
	
	// set time for session timeout
	$currentTime = time() + 25200;
	$expired = 3600;
	
	// if session not set go to login page
	if(!isset($_SESSION['user'])){
		header("location:index.php");
	}
	
	// if current time is more than session timeout back to login page
	if($currentTime > $_SESSION['timeout']){
		session_destroy();
		header("location:index.php");
	}
	
	// destroy previous session timeout and create new one
	unset($_SESSION['timeout']);
	$_SESSION['timeout'] = $currentTime + $expired;
	
?>

<?php include"header.php";?>
<html>
<head>
<title>Sold Out Products | <?=$settings['app_name']?> - Dashboard</title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
</head>
</body>
      <!-- Content Wrapper. Contains page content -->
      <div class="content-wrapper">
        <?php include('public/sold-out-products-table.php'); ?>
      </div><!-- /.content-wrapper -->
  </body>
</html>
<?php include"footer.php";?>