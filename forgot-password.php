<?php 
session_start();
    ob_start(); 
    include_once('includes/crud.php');
    $db = new Database;
    include_once('includes/custom-functions.php');
    $fn = new custom_functions();
    $db->connect();
    date_default_timezone_set('Asia/Kolkata');
    $sql = "SELECT * FROM settings";
    $db->sql($sql);
    $res = $db->getResult();
    $settings = json_decode($res[5]['value'],1);
    $logo = $fn->get_settings('logo');
    
    ?>
<!DOCTYPE html>
<html>
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
	<link rel="icon" type="image/ico" href="<?= DOMAIN_URL . 'dist/img/'.$logo?>">
	<title>Forgot Password - <?=$settings['app_name']?></title>
    <!-- Tell the browser to be responsive to screen width -->
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
    <!-- Bootstrap 3.3.5 -->
    <link rel="stylesheet" href="bootstrap/css/bootstrap.min.css">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.4.0/css/font-awesome.min.css">
    <!-- Ionicons -->
    <link rel="stylesheet" href="https://code.ionicframework.com/ionicons/2.0.1/css/ionicons.min.css">
    <!-- Theme style -->
    <link rel="stylesheet" href="dist/css/AdminLTE.min.css">
    <!-- AdminLTE Skins. Choose a skin from the css/skins
         folder instead of downloading all of them to reduce the load. -->
    <link rel="stylesheet" href="dist/css/skins/_all-skins.min.css">
    <!-- iCheck -->
    <link rel="stylesheet" href="plugins/iCheck/flat/blue.css">
    <!-- Morris chart -->
    <link rel="stylesheet" href="plugins/morris/morris.css">
    <!-- jvectormap -->
    <link rel="stylesheet" href="plugins/jvectormap/jquery-jvectormap-1.2.2.css">
    <!-- Date Picker -->
    <link rel="stylesheet" href="plugins/datepicker/datepicker3.css">
    <!-- Daterange picker -->
    <link rel="stylesheet" href="plugins/daterangepicker/daterangepicker-bs3.css">
    <!-- bootstrap wysihtml5 - text editor -->
    <link rel="stylesheet" href="plugins/bootstrap-wysihtml5/bootstrap3-wysihtml5.min.css">

    <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
        <script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
        <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->
  </head>
</body>
      <!-- Content Wrapper. Contains page content -->
      <?php $sql_logo = "select value from `settings` where variable='Logo' OR variable='logo'";
$db->sql($sql_logo);
$res_logo = $db->getResult();
?>
<div class="col-md-4 col-md-offset-4 " style="margin-top:150px;">
    <!-- general form elements -->
    <div class='row'>
        <div class="col-md-12 text-center">
            <img src="<?= DOMAIN_URL . 'dist/img/' . $res_logo[0]['value'] ?>" height="110">
            <h3>Forgot Password</h3>
        </div>
        <div class="box box-info col-md-12">
            <div class="box-header with-border">
            </div><!-- /.box-header -->
            <!-- form start -->
            <form action="public/send-forgot-password-mail.php" method="post" id="forgot_password_form">
                <div class="box-body">
                    <input type="hidden" name="send_forgot_password_mail" value="1">
                    <div class="form-group">
                        <label for="">Email :</label>
                        <input type="email" name="email" class="form-control" value="" placeholder="We will sent you reset password link on this email" required>
                    </div>
                    <div class="box-footer">
                        <button type="submit" name="submit_btn" id="submit_btn" class="btn btn-info pull-left">Submit</button>
                    </div>
                    <div class="form-group">
                        <div id="result"></div>
                    </div>
            </form>
        </div><!-- /.box -->
    </div>
</div>
</div>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.16.0/jquery.validate.min.js"></script>
<script>
    $('#forgot_password_form').validate({
        rules: {
            email: "required"
        }
    });
</script>
<script>
    $('#forgot_password_form').on('submit', function(e) {
        e.preventDefault();
        <?php
            if(ALLOW_MODIFICATION==0 && !defined (ALLOW_MODIFICATION)){
				echo '<label class="alert alert-danger">This operation is not allowed in demo panel!.</label>';
				return false;
			}
        ?>
        var formData = new FormData(this);
        if ($("#forgot_password_form").validate().form()) {
                $.ajax({
                    type: 'POST',
                    url: $(this).attr('action'),
                    data: formData,
                    beforeSend: function() {
                        $('#submit_btn').html('Please wait..');
                    },
                    cache: false,
                    contentType: false,
                    processData: false,
                    success: function(result) {
                        $('#result').html(result);
                        $('#result').show().delay(6000).fadeOut();
                        $('#submit_btn').html('Submit');
                        $('#forgot_password_form')[0].reset();
                    }
                });
        }
    });
</script>
  </body>
</html>