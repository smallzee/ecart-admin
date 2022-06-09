<?php
include_once('includes/functions.php');
include_once('includes/custom-functions.php');
$fn = new custom_functions;
?>
<?php
$ID = (isset($_GET['id'])) ? $db->escapeString($fn->xss_clean($_GET['id'])) : "";
$category_data = array();

$sql_query = "SELECT image FROM category WHERE id =" . $ID;
$db->sql($sql_query);
$res = $db->getResult();
if (isset($_POST['btnEdit'])) {
    if (ALLOW_MODIFICATION == 0 && !defined(ALLOW_MODIFICATION)) {
        echo '<label class="alert alert-danger">This operation is not allowed in demo panel!.</label>';
        return false;
    }
    if ($permissions['categories']['update'] == 1) {

        $name = $db->escapeString($fn->xss_clean($_POST['name']));
        $subtitle = $db->escapeString($fn->xss_clean($_POST['subtitle']));

        $menu_image = $db->escapeString($fn->xss_clean($_FILES['image']['name']));
        $image_error = $db->escapeString($fn->xss_clean($_FILES['image']['error']));
        $image_type = $db->escapeString($fn->xss_clean($_FILES['image']['type']));
        $error = array();

        if (empty($name)) {
            $error['name'] = " <span class='label label-danger'>Required!</span>";
        }
        if (empty($subtitle)) {
            $error['subtitle'] = " <span class='label label-danger'>Required!</span>";
        }

        // get image file extension
        error_reporting(E_ERROR | E_PARSE);
        $extension = end(explode(".", $_FILES["image"]["name"]));

        if (!empty($menu_image)) {
            // $mimetype = mime_content_type($_FILES["image"]["tmp_name"]);
            // if (!in_array($mimetype, array('image/jpg', 'image/jpeg', 'image/gif', 'image/png'))) {
            // 	$error['image'] = " <span class='label label-danger'>Image type must jpg, jpeg, gif, or png!</span>";
            // }
            $result = $fn->validate_image($_FILES["image"]);
            if ($result) {
                $error['image'] = " <span class='label label-danger'>Image type must jpg, jpeg, gif, or png!</span>";
            }
        }

        if (!empty($name) && !empty($subtitle) && empty($error['image'])) {

            if (!empty($menu_image)) {

                $string = '0123456789';
                $file = preg_replace("/\s+/", "_", $_FILES['image']['name']);
                $function = new functions;
                $image = $function->get_random_string($string, 4) . "-" . date("Y-m-d") . "." . $extension;

                // delete previous image
                $delete = unlink($res[0]['image']);

                // upload new image
                $upload = move_uploaded_file($_FILES['image']['tmp_name'], 'upload/images/' . $image);
                $upload_image = 'upload/images/' . $image;
                $sql_query = "UPDATE category SET name = ' $name',  subtitle = '$subtitle',image = '$upload_image' WHERE id =  $ID";
                if ($db->sql($sql_query)) {
                    $db->sql($sql_query);
                    $update_result = $db->getResult();
                }
            } else {

                $sql_query = "UPDATE category SET name = '" . $name . "', subtitle = '" . $subtitle . "', image = '" . $res[0]['image'] . "'WHERE id =" . $ID;
                $db->sql($sql_query);
                $update_result = $db->getResult();
            }

            if (!empty($update_result)) {
                $update_result = 0;
            } else {
                $update_result = 1;
            }

            // check update result
            if ($update_result == 1) {
                $error['update_category'] = " <section class='content-header'><span class='label label-success'>Category updated Successfully</span></section>";
            } else {
                $error['update_category'] = " <span class='label label-danger'>Failed update category</span>";
            }
        }
    } else {
        $error['check_permission'] = " <section class='content-header'><span class='label label-danger'>You have no permission to update category</span></section>";
    }
}

// create array variable to store previous data
$data = array();

$sql_query = "SELECT * FROM category WHERE id =" . $ID;
$db->sql($sql_query);
$res = $db->getResult();

if (isset($_POST['btnCancel'])) { ?>
    <script>
        window.location.href = "categories.php";
    </script>
<?php } ?>
<section class="content-header">
    <h1>
        Edit Category<small><a href='categories.php'><i class='fa fa-angle-double-left'></i>&nbsp;&nbsp;&nbsp;Back to Categories</a></small></h1>
    <small><?php echo isset($error['update_category']) ? $error['update_category'] : ''; ?></small>
    <ol class="breadcrumb">
        <li><a href="home.php"><i class="fa fa-home"></i> Home</a></li>
    </ol>
</section>
<section class="content">
    <!-- Main row -->

    <div class="row">
        <div class="col-md-6">
            <?php if ($permissions['categories']['update'] == 0) { ?>
                <div class="alert alert-danger topmargin-sm">You have no permission to update category.</div>
            <?php } ?>
            <!-- general form elements -->
            <div class="box box-primary">
                <div class="box-header with-border">
                    <h3 class="box-title">Edit Category</h3>
                </div><!-- /.box-header -->
                <!-- form start -->
                <form id="edit_category_form" method="post" enctype="multipart/form-data">
                    <div class="box-body">
                        <div class="form-group">
                            <label for="exampleInputEmail1">Category Name</label><?php echo isset($error['name']) ? $error['name'] : ''; ?>
                            <input type="text" class="form-control" name="name" value="<?php echo $res[0]['name']; ?>">
                        </div>
                        <div class="form-group">
                            <label for="exampleInputEmail1">Category Subtitle</label><?php echo isset($error['subtitle']) ? $error['subtitle'] : ''; ?>
                            <input type="text" class="form-control" name="subtitle" value="<?php echo $res[0]['subtitle']; ?>">
                        </div>
                        <div class="form-group">
                            <label for="exampleInputFile">Image&nbsp;&nbsp;&nbsp;*Please choose square image of larger than 350px*350px & smaller than 550px*550px.</label><?php echo isset($error['image']) ? $error['image'] : ''; ?>
                            <input type="file" name="image" id="image" title="Please choose square image of larger than 350px*350px & smaller than 550px*550px." value="<img src='<?php echo $data['image']; ?>'/>">
                            <p class="help-block"><img src="<?php echo $res[0]['image']; ?>" width="280" height="190" /></p>
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
<?php $db->disconnect(); ?>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.17.0/jquery.validate.min.js"></script>
<script>
    $('#edit_category_form').validate({
        rules: {
            name: "required",
            subtitle: "required",
        }
    });
</script>
<script>
    //  var changeCheckbox = document.querySelector('#product_rating_btn');
    //     var init = new Switchery(changeCheckbox);
    //     changeCheckbox.onchange = function() {
    //         if ($(this).is(':checked')) {
    //             $('#product_rating').val(1);
    //         } else {
    //             $('#product_rating').val(0);
    //         }
    //     };
</script>