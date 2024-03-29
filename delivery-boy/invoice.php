<?php
	// start session
	
	session_start();
	
	// set time for session timeout
	$currentTime = time() + 25200;
	$expired = 3600;
	
	// if session not set go to login page
	if(!isset($_SESSION['name'])){
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
<head>Print Invoice for the order</head>
<title>Invoice Report <?=$settings['app_name']?> - Dashboard</title>
<body>
    <?php
            if (isset($_GET['id'])) {
                $ID = $_GET['id'];
            
                $sql = "SELECT delivery_boy_id FROM orders WHERE id=".$ID;
                $db->sql($sql);
                $res=$db->getResult();
                if($res[0]['delivery_boy_id'] != $_SESSION['delivery_boy_id']){
                    echo "<script>alert('You are not allowed to view this invoice.');top.location='orders.php';</script>";
                    return false;
                }
                $sql_query = "SELECT status FROM delivery_boys WHERE id=".$_SESSION['delivery_boy_id'];
                $db->sql($sql_query);
                $result=$db->getResult();
                if($result[0]['status']==0){
                    echo "<script>alert('It seems your acount is not active please contact admin for more info!.');top.location='orders.php';</script>";
                    return false;
                }
            }
        ?>
      <!-- Content Wrapper. Contains page content -->
      <div class="content-wrapper">
        <?php include('public/invoice-print.php'); ?>
      </div><!-- /.content-wrapper -->
  </body>
</html>
<?php include"footer.php";?>
  		
    		