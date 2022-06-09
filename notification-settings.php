<?php 
    
session_start();
    
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
    include"header.php";?>
<html>
<head>
<title>Notification Settings | <?=$settings['app_name']?> - Dashboard</title>
</head>
</body>
      <!-- Content Wrapper. Contains page content -->
      <div class="content-wrapper">
        <?php 
        include_once('includes/custom-functions.php');
        $fn = new custom_functions;
            	$sql = "SELECT * FROM settings WHERE variable='fcm_server_key'";
                $db->sql($sql);
                $res = $db->getResult();
            	$message = '';
            	if(isset($_POST['btn_update'])){
                    if(ALLOW_MODIFICATION==0 && !defined (ALLOW_MODIFICATION)){
                        echo '<label class="alert alert-danger">This operation is not allowed in demo panel!.</label>';
                        return false;
                    }
                    if($permissions['settings']['update']==1){
            			
            			$fcm_server_key = $db->escapeString($fn->xss_clean($_POST['fcm_server_key']));
            			
            			$sql = "UPDATE `settings` SET `value`='".$fcm_server_key."' WHERE `variable` = 'fcm_server_key'";
                   
            			$db->sql($sql);
            			
            			$sql = "SELECT * FROM settings WHERE variable='fcm_server_key'";
            			$db->sql($sql);
            			$res = $db->getResult();
                      
            			$message .= "<div class='alert alert-success'> FCM Server Key Updated Successfully!</div>";
                        }else{
                        $message .= "<label class='alert alert-danger'>You have no permission to update settings</label>";
                    }
            	}
            ?>
            <section class="content-header">
                <h2>Notification Settings</h2>
            	<h4><?=$message?></h4>
                <ol class="breadcrumb">
                    <li><a href="home.php"><i class="fa fa-home"></i> Home</a></li>
                </ol>
                <hr />
            </section>
            <section class="content">
                <div class="row">
                    <div class="col-md-12">
                        <?php if($permissions['settings']['read']==1){
                        if($permissions['settings']['update']==0) { ?>
                            <div class="alert alert-danger">You have no permission to update settings</div>
                        <?php } ?>
                        <!-- general form elements -->
                        <div class="box box-primary">
                            <div class="box-header with-border">
                                <h3 class="box-title">Update Notification Settings</h3>
                            </div>
                            <!-- /.box-header -->
                            <!-- form start -->
                            <form  method="post" enctype="multipart/form-data">
                                <div class="box-body">
                                    
                                        <div class="form-group">
                                        <label for="fcm_server_key">FCM Server Key : </label>
                                        <textarea class="form-control" name="fcm_server_key" placeholder='FCM Server Key' rows="5"><?=$res[0]['value']?></textarea>
                                    </div>
                                </div>
                                <!-- /.box-body -->
                                <div class="box-footer">
                                    <input type="submit" class="btn-primary btn" value="Update" name="btn_update"/>
                                </div>
                            </form>
                            <?php } else { ?>
                                <div class="alert alert-danger">You have no permission to view settings</div>
                             <?php } ?>
                        </div>
                        <!-- /.box -->
                    </div>
                </div>
            </section>
            <div class="separator"> </div>
      </div><!-- /.content-wrapper -->
  </body>
</html>
 <?php include"footer.php";?>
