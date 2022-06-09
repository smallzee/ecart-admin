<?php session_start();
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
$settings = json_decode($res[5]['value'], 1);
$logo = $fn->get_settings('logo');

?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <link rel="icon" type="image/ico" href="<?= DOMAIN_URL . 'dist/img/' . $logo ?>">
    <title>Reset Password - <?= $settings['app_name'] ?></title>
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
            <h3>Reset Password</h3>
        </div>
        <div class="box box-info col-md-12">
            <div class="box-header with-border">
            </div><!-- /.box-header -->
            <!-- form start -->
            <form action="public/reset-password.php" method="post" id="reset_password_form">
                <div class="box-body">
                    <input type="hidden" name="reset_password" value="1">
                    <div class="form-group">
                        <label for="">Password :</label>
                        <input type="text" name="password" id="password" class="form-control" value="" required>
                    </div>
                    <div class="form-group">
                        <label for="">Confirm Password :</label>
                        <input type="text" name="confirm_password" id="confirm_password" class="form-control" value="" required>
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
    $('#reset_password_form').validate({
        rules: {
            password: "required",
            confirm_password: {
                required: true,
                equalTo: "#password"
            }
        }
    });
</script>
<script>
    $('#reset_password_form').on('submit', function(e) {
        e.preventDefault();
        <?php
            if(ALLOW_MODIFICATION==0 && !defined (ALLOW_MODIFICATION)){
				echo '<label class="alert alert-danger">This operation is not allowed in demo panel!.</label>';
				return false;
			}
        ?>
        $.urlParam = function(name){
    	var results = new RegExp('[\?&]' + name + '=([^&#]*)').exec(window.location.href);
    	return results[1] || 0;
    }
        var code = $.urlParam('code');
        var data =new FormData(this);
        data.append('code',<?php echo "'".$_GET['code']."'";?>);
        if ($("#reset_password_form").validate().form()) {
            $.ajax({
                type: 'POST',
                url: $(this).attr('action'),
                data: data,
                beforeSend: function() {
                    $('#submit_btn').html('Please wait..').attr('disabled',true);
                },
                cache: false,
                contentType: false,
                processData: false,
                dataType:'json',
                success: function(result) {
                    if(result['error'] == false){
                        $('#result').html(result['message']);
                        $('#result').show().delay(6000).fadeOut();
                        $('#submit_btn').html('Submit').attr('disabled',false);
                        window.setTimeout(function() {
                            window.location.href = 'index.php';
                        }, 6000);
                    }
                    else{
                        $('#result').html(result['message']);
                        $('#result').show().delay(6000).fadeOut();
                        $('#submit_btn').html('Submit').attr('disabled',false);
                    }
                    
                }
            });
        }
    });
</script>
</body>

</html>