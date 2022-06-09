<?php 
      include('../includes/crud.php');
      $db=new Database();
      $db->connect();
      $sql = "SELECT * FROM settings";
      $db->sql($sql);
      $res = $db->getResult();
      $settings['app_name'] = $res[4]['value'];?>
<!DOCTYPE html>
<html>
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Offers | <?=$settings['app_name']?></title>
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

    <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
        <script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
        <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->
	
  </head>
  <body style="-webkit-user-select: none; -moz-user-select: -moz-none; -ms-user-select: none; user-select: none;">
      <!-- Left side column. contains the logo and sidebar -->
      <!-- Content Wrapper. Contains page content -->
    
        <!-- Content Header (Page header) -->
        

        <!-- Main content -->
        

<!-- ============================================================= -->

	  <?php

	$sql_query = "SELECT Value 
				FROM settings 
				WHERE Variable = 'Tax'";
				
		
			// Execute query
			$db->sql($sql_query);
			// store result 
			$res=$db->getResult();
		
?>
<section class="content1">
<img class="back" src="../images/sale.jpg">
</section><!-- /.content -->

<!-- ============================================================= -->
 <section id="faq" class="col-md-12">
    <h2 class="page-header" ><a href="#" style="color:#014c8d;">Voucher value chart</a></h2>          
<div class="row">
	<div class="col-xs-12">
	    <div class="box">
            <!-- /.box-header -->
            <div class="box-body table-responsive">
              <table class="table table-hover">
                <tr>
                  <th style="color:#f47c2e;">Shopping</th>
                  <th style="color:#f47c2e;">Voucher value</th>
                  
                </tr>
                <tr>
                  <td style="color:#014c8d;">Rs.300 to Rs.500</td>
                  <td style="color:#014c8d;">Rs.5</td>
                </tr>
                <tr>
                  <td style="color:#014c8d;">Rs.500 to Rs.1000</td>
                  <td style="color:#014c8d;">Rs.10</td>
                </tr>
                <tr>
                  <td style="color:#014c8d;">Rs.1000 to Rs.1200</td>
                  <td style="color:#014c8d;">Rs.15</td>
                </tr>
                <tr>
                  <td style="color:#014c8d;">Rs.1200 to Rs.1500</td>
                  <td style="color:#014c8d;">Rs.20</td>
                </tr>
				<tr>
                  <td style="color:#014c8d;">Rs.1500 to Rs.2000</td>
                  <td style="color:#014c8d;">Rs.30</td>
                </tr>
				<tr>
                  <td style="color:#014c8d;">Rs.2000 to above Rs.2000</td>
                  <td style="color:#014c8d;">Rs.50</td>
                </tr>
				
              </table>
            </div><!-- /.box-body -->
          </div><!-- /.box -->
        </div>
    </div>
</section>
<section id="faq" class="col-md-12">
  <h2 class="page-header" ><a href="#" style="color:#014c8d;">Current Discount Offer </a></h2>
  <h4 style="color:#f47c2e;"><i class="fa fa-share margin-r-5"></i>Flat <?php echo $res[0]['Value'];?>% off at <?=$settings['app_name']?> Shopping App.</h4><br>
</section>

<section id="faq" class="col-md-12">
  <h2 class="page-header" ><a href="#" style="color:#014c8d;">Rules for Coupon Voucher</a></h2>
  <h5 style="color:#f47c2e;"><i class="fa fa-share margin-r-5"></i>Any Voucher will valid till 6 month.</h5><br>
  <h5 style="color:#f47c2e;"><i class="fa fa-share margin-r-5"></i>You can use your voucher only when your voucher value is Rs.100 or above Rs.100</h5><br>
  <h5 style="color:#f47c2e;"><i class="fa fa-share margin-r-5"></i>When your voucher value is reach at Rs.100 or above Rs.100 then after that you have to deposit that voucher at counter and then you can do a free shopping of Rs.100</h5><br>
	<h5 style="color:#f47c2e;"><i class="fa fa-share margin-r-5"></i>For use of voucher you have to follow above rules</h5><br>
<!-- /.box-footer -->
                   

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
    <script src="https://google-code-prettify.googlecode.com/svn/loader/run_prettify.js"></script>
    <script src="docs.js"></script>
  </body>
</html>
