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
include "header.php"; ?>
<html>

<head>
    <title>Delivery boy Privacy-Policy | <?= $settings['app_name'] ?> - Dashboard</title>
</head>
</body>
<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <?php
    include_once('includes/custom-functions.php');
    $fn = new custom_functions;

    $sql = "SELECT value FROM settings where variable='delivery_boy_privacy_policy'";
    $db->sql($sql);
    $res_privacy = $db->getResult();
    $message = '';
    if (isset($_POST['btn_update'])) {
        if (ALLOW_MODIFICATION == 0 && !defined(ALLOW_MODIFICATION)) {
            echo '<label class="alert alert-danger">This operation is not allowed in demo panel!.</label>';
            return false;
        }
        if ($permissions['settings']['update'] == 1) {
            if (!empty($_POST['privacy_policy']) || !empty($_POST['terms_conditions'])) {

                $privacy_policy = $db->escapeString($fn->xss_clean($_POST['privacy_policy']));
                $terms_conditions = $db->escapeString($fn->xss_clean($_POST['terms_conditions']));

                if (empty($res_privacy)) {
                    $sql = "INSERT INTO settings (variable,value) VALUES ('delivery_boy_privacy_policy','" . $privacy_policy . "')";
                } else {
                    $sql = "UPDATE `settings` SET `value`='" . $privacy_policy . "' WHERE `variable` = 'delivery_boy_privacy_policy'";
                }
                $db->sql($sql);
                $sql = "SELECT value FROM settings where variable='delivery_boy_terms_conditions'";
                $db->sql($sql);
                $res_terms = $db->getResult();

                if (empty($res_terms)) {
                    $sql = "INSERT INTO settings (variable,value) VALUES ('delivery_boy_terms_conditions','" . $terms_conditions . "')";
                } else {
                    $sql = "UPDATE `settings` SET `value`='" . $terms_conditions . "' WHERE `variable` = 'delivery_boy_terms_conditions'";
                }
                $db->sql($sql);


                $message .= "<div class='alert alert-success'> Information Updated Successfully!</div>";
            }
        } else {
            $message .= "<label class='alert alert-danger'>You have no permission to update settings</label>";
        }
    }
    ?>
    <section class="content-header">
        <h1>Delivery boy Privacy Policy And Terms & Conditions</h1>
        <h4><?= $message ?></h4>
        <ol class="breadcrumb">
            <li><a href="home.php"><i class="fa fa-home"></i> Home</a></li>
        </ol>
        <hr />
    </section>
    <section class="content">
        <div class="row">
            <div class="col-md-4" style="margin-bottom:10px;"></div>
            <div class="col-md-4" style="margin-bottom:10px;">
                <?php if ($permissions['settings']['read'] == 1) { ?>
                    <a href='delivery-boy-play-store-privacy-policy.php' target='_blank' class='btn btn-primary btn-sm'>Delivery boy Privacy Policy Page for Play Store</a>
                <?php } ?>
            </div>
            <div class="col-md-4" style="margin-bottom:10px;">
                <?php if ($permissions['settings']['read'] == 1) { ?>
                    <a href='delivery-boy-play-store-terms-conditions.php' target='_blank' class='btn btn-primary btn-sm'>Delivery boy Terms Conditions Page for Play Store</a>
                <?php } ?>
            </div>
        </div>

        <div class="row">

            <div class="col-md-12">
                <?php if ($permissions['settings']['read'] == 1) {
                    if ($permissions['settings']['update'] == 0) { ?>
                        <div class="alert alert-danger">You have no permission to update settings</div>
                    <?php } ?>

                    <!-- general form elements -->
                    <div class="box box-primary">
                        <div class="box-header with-border">
                            <h3 class="box-title">Update Privacy Policy</h3>
                        </div>
                        <form method="post" enctype="multipart/form-data">
                            <?php
                            $sql = "SELECT value FROM settings where variable='delivery_boy_privacy_policy'";
                            $db->sql($sql);
                            $res_privacy = $db->getResult();
                            ?>
                            <div class="box-body">
                                <div class="form-group">
                                    <label for="app_name">Privacy Policy:</label>
                                    <textarea rows="10" cols="10" class="form-control" name="privacy_policy" id="privacy_policy" required><?= !empty($res_privacy) ? $res_privacy[0]['value'] : ''; ?></textarea>
                                </div>
                                <div class="box-header with-border">
                                    <h3 class="box-title">Update Terms Conditions</h3>
                                </div>
                                <?php
                                $sql = "SELECT value FROM settings where variable='delivery_boy_terms_conditions'";
                                $db->sql($sql);
                                $res_terms = $db->getResult();
                                ?>
                                <div class="box-body">
                                    <div class="form-group">
                                        <label for="app_name">Terms & Conditions:</label>
                                        <textarea rows="10" cols="10" class="form-control" name="terms_conditions" id="terms_conditions" required><?= !empty($res_terms) ? $res_terms[0]['value'] : ''; ?></textarea>
                                    </div>
                                </div>

                            </div>
                            <!-- /.box-body -->
                            <div class="box-footer">
                                <input type="submit" class="btn-primary btn" value="Update" name="btn_update" />
                            </div>
                        </form>
                    <?php } else { ?>
                        <div class="alert alert-danger">You have no permission to view settings</div>
                    <?php } ?>

                    </div>
                    <!-- /.box -->
            </div>
        </div>
</div>
</section>
<div class="separator"> </div>
</div><!-- /.content-wrapper -->
</body>

</html>
<?php include "footer.php"; ?>
<script type="text/javascript" src="css/js/ckeditor/ckeditor.js"></script>
<script type="text/javascript">
    CKEDITOR.replace('privacy_policy');
    CKEDITOR.replace('terms_conditions');
</script>