<?php
include_once('includes/functions.php');
include_once('includes/custom-functions.php');
$fn = new custom_functions;
?>
<?php
$ID = (isset($_GET['id'])) ? $db->escapeString($fn->xss_clean($_GET['id'])) : "";


// create array variable to store category data
$category_data = array();

if (isset($_POST['btnEdit'])) {
    if (ALLOW_MODIFICATION == 0 && !defined(ALLOW_MODIFICATION)) {
        echo '<label class="alert alert-danger">This operation is not allowed in demo panel!.</label>';
        return false;
    }
    if ($permissions['products']['update'] == 1) {

        $title = $db->escapeString($fn->xss_clean($_POST['title']));
        $percentage = $db->escapeString($fn->xss_clean($_POST['percentage']));
        $status = $db->escapeString($fn->xss_clean($_POST['pr_status']));

        // create array variable to handle error
        $error = array();

        if (empty($title)) {
            $error['title'] = " <span class='label label-danger'>Required!</span>";
        }
        if ($percentage == '') {
            $error['percentage'] = " <span class='label label-danger'>Required!</span>";
        }

        if (!empty($title) &&  $percentage != '') {

            $sql_query = "UPDATE taxes SET `title` = '$title', `percentage` = '$percentage', `status`=$status WHERE id =" . $ID;
            $db->sql($sql_query);
            $update_result = $db->getResult();

            if (!empty($update_result)) {
                $update_result = 0;
            } else {
                $update_result = 1;
            }

            // check update result
            if ($update_result == 1) {
                $error['update_tax'] = " <section class='content-header'>
												<span class='label label-success'>Tax updated Successfully</span>
												<h4><small><a  href='products-taxes.php'><i class='fa fa-angle-double-left'></i>&nbsp;&nbsp;&nbsp;Back to Taxes</a></small></h4>
												</section>";
            } else {
                $error['update_tax'] = " <span class='label label-danger'>Failed update tax</span>";
            }
        }
    } else {
        $error['check_permission'] = " <section class='content-header'><span class='label label-danger'>You have no permission to update tax</span></section>";
    }
}

// create array variable to store previous data
$data = array();

$sql_query = "SELECT * FROM taxes WHERE id =" . $ID;
$db->sql($sql_query);
$res = $db->getResult();

if (isset($_POST['btnCancel'])) { ?>
    <script>
        window.location.href = "products-taxes.php";
    </script>
<?php } ?>
<section class="content-header">
    <h1>
        Edit Tax</h1>
    <small><?php echo isset($error['update_tax']) ? $error['update_tax'] : ''; ?></small>
    <ol class="breadcrumb">
        <li><a href="home.php"><i class="fa fa-home"></i> Home</a></li>
    </ol>
</section>
<section class="content">
    <!-- Main row -->
    <div class="row">
        <div class="col-md-6">
            <?php if ($permissions['products']['update'] == 0) {
            ?>
                <div class="alert alert-danger topmargin-sm">You have no permission to update tax.</div>
            <?php } ?>
            <!-- general form elements -->
            <div class="box box-primary">
                <div class="box-header with-border">
                    <h3 class="box-title">Edit Tax</h3>
                </div><!-- /.box-header -->
                <!-- form start -->
                <form id="edit_tax_form" method="post" enctype="multipart/form-data">
                    <div class="box-body">
                        <div class="form-group">
                            <label for="title">Title</label><?php echo isset($error['title']) ? $error['title'] : ''; ?>
                            <input type="text" class="form-control" name="title" value="<?php echo $res[0]['title']; ?>">
                        </div>
                        <div class="form-group">
                            <label for="percentage">Percentage</label><?php echo isset($error['percentage']) ? $error['percentage'] : ''; ?>
                            <input type="number" step="any" class="form-control" name="percentage" value="<?php echo $res[0]['percentage']; ?>">
                        </div>
                        <div class="form-group">
                            <label class="control-label ">Status :</label>
                            <div id="product_status" class="btn-group">
                                <label class="btn btn-default" data-toggle-class="btn-primary" data-toggle-passive-class="btn-default">
                                    <input type="radio" name="pr_status" value="0"> Deactive
                                </label>
                                <label class="btn btn-primary" data-toggle-class="btn-primary" data-toggle-passive-class="btn-default">
                                    <input type="radio" name="pr_status" value="1"> Active
                                </label>
                            </div>
                        </div>
                    </div><!-- /.box-body -->

                    <div class="box-footer">
                        <button type="submit" class="btn btn-primary" name="btnEdit">Update</button>
                        <button type="submit" class="btn btn-danger" name="btnCancel">Cancel</button>
                    </div>
                </form>
            </div><!-- /.box -->
        </div>
    </div>
</section>

<div class="separator"> </div>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.17.0/jquery.validate.min.js"></script>
<script>
    $(document).ready(function() {
        var product_status = '<?= $res[0]['status'] ?>';
        $("input[name=pr_status][value=1]").prop('checked', true);
        if (product_status == 0)
            $("input[name=pr_status][value=0]").prop('checked', true);
    });


    $('#edit_tax_form').validate({
        rules: {
            title: "required",
            percentage: "required",
        }
    });
</script>
<?php $db->disconnect(); ?>