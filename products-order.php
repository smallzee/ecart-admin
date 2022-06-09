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

include_once('includes/crud.php');
include_once('includes/functions.php');
include_once('includes/custom-functions.php');
$fn = new custom_functions;
$permissions = $fn->get_permissions($_SESSION['id']);

$db = new Database();
$db->connect();

if (isset($_POST['update_products_order']) && $_POST['update_products_order'] == 1) {
    if (ALLOW_MODIFICATION == 0 && !defined(ALLOW_MODIFICATION)) {
        echo '<label class="alert alert-danger">This operation is not allowed in demo panel!.</label>';
        return false;
    }
    if ($permissions['products_order']['update'] == 1) {
        $row_order =  $db->escapeString($fn->xss_clean($_POST["row_order"]));
        $id_ary = explode(",", $row_order);
        for ($i = 0; $i < count($id_ary); $i++) {
            $sql = "UPDATE `products` SET row_order='" . $i . "' WHERE id=" . $id_ary[$i];
            $db->sql($sql);
            $res = $db->getResult();
        }
        echo "<p class='alert alert-success'>Product order updated!</p>";
        return false;
    } else {
        echo "<p class='alert alert-danger'>You have no permission to update products order</p>";
        return false;
    }
}
?>

<?php include "header.php"; ?>
<html>

<head>
    <title>Products | <?= $settings['app_name'] ?> - Dashboard</title>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <style>
        #sortable-row li {
            margin-bottom: 4px;
            padding: 10px;
            background-color: #fff;
            cursor: move;
        }

        #sortable-row li.ui-state-highlight {
            height: 1.0em;
            background-color: #F0F0F0;
            border: #ccc 2px dotted;
        }

        #sortable-row-2 li {
            margin-bottom: 4px;
            padding: 10px;
            background-color: #fff;
            cursor: move;
        }

        #sortable-row-2 li.ui-state-highlight {
            height: 1.0em;
            background-color: #F0F0F0;
            border: #ccc 2px dotted;
        }
    </style>
</head>
</body>
<?php
$sql = "SELECT * FROM `category` ORDER BY `id` DESC";
$db->sql($sql);
$categories = $db->getResult();
$sql = "SELECT * FROM `subcategory` ORDER BY `id` DESC";
$db->sql($sql);
$subcategories = $db->getResult();

if (isset($_GET['category_id']) && isset($_GET['subcategory_id'])) {
    $category_id = $db->escapeString($_GET['category_id']);
    $sql = "SELECT * FROM `products` where `category_id` = '" . $_GET['category_id'] . "' AND `subcategory_id`='" . $_GET['subcategory_id'] . "'ORDER BY `row_order` ASC";
    $db->sql($sql);
    $res = $db->getResult();
}
if ((isset($_GET['category_id']) && ($_GET['category_id'] !== '')) && isset($_GET['subcategory_id']) && ($_GET['subcategory_id'] == '')) {


    $category_id = $db->escapeString($_GET['category_id']);
    $sql = "SELECT * FROM `products` where `category_id` = '" . $category_id . "' ORDER BY `row_order` ASC";
    $db->sql($sql);
    $res = $db->getResult();
}
if ((isset($_GET['category_id']) && ($_GET['category_id'] == '')) && isset($_GET['subcategory_id']) && ($_GET['subcategory_id'] == '')) {
    $sql = "SELECT * FROM `products` ORDER BY `row_order` ASC";
    $db->sql($sql);
    $res = $db->getResult();
}
if ((isset($_GET['category_id']) && ($_GET['category_id'] == '')) && isset($_GET['subcategory_id']) && ($_GET['subcategory_id'] !== '')) {
    $subcategory_id = $_GET['subcategory_id'];
    $sql = "SELECT * FROM `products` where `subcategory_id` = '" . $subcategory_id . "' ORDER BY `row_order` ASC";
    $db->sql($sql);
    $res = $db->getResult();
}
if ((!isset($_GET['category_id']) && ($_GET['category_id'] == '')) && !(isset($_GET['subcategory_id']) && ($_GET['subcategory_id'] == ''))) {
    $sql = "SELECT * FROM `products`  ORDER BY `row_order` ASC";
    $db->sql($sql);
    $res = $db->getResult();
}


?>
<!-- Content Wrapper. Contains page content -->

<div class="content-wrapper">
    <div class="container">
        <?php if ($permissions['products_order']['read'] == 1) { ?>
            <h2>Products Order</h2>
            <hr>
            <div class='row'>
                <div class='col-md-6'>
                    <label class="control-label">Category</label>
                    <select name="category_id" id="category_id" class="form-control">
                        <option value=''>All</option>
                        <?php if ($permissions['categories']['read'] == 1) { ?>
                            <?php foreach ($categories as $category) { ?>
                                <option value='<?= $category['id'] ?>'><?= $category['name'] ?></option>
                        <?php }
                        } ?>
                    </select>
                </div>
                <div class='col-md-6'>

                    <label class="control-label">Sub Category</label>
                    <select name="subcategory_id" id="subcategory_id" class="form-control">
                        <option value=''>All</option>
                        <?php
                        if (isset($_GET['category_id']) and !empty($_GET['category_id'])) {
                            $category_id = $db->escapeString($_GET['category_id']);

                            $sql = "SELECT * FROM `subcategory` where `category_id` = '" . $category_id . "' ORDER BY `id` ASC";
                            $db->sql($sql);
                            $res_inner = $db->getResult();
                            if ($permissions['subcategories']['read'] == 1) {
                                foreach ($res_inner as $subcategory) { ?>
                                    <option value='<?= $subcategory['id'] ?>'><?= $subcategory['name'] ?></option>

                            <?php }
                            }
                        } else { ?>
                            <?php
                            if ($permissions['subcategories']['read'] == 1) {
                                foreach ($subcategories as $subcategory) { ?>
                                    <option value='<?= $subcategory['id'] ?>'><?= $subcategory['name'] ?></option>

                        <?php }
                            }
                        } ?>
                    </select>
                </div>
                <br>
                <br>
                <br>
                <br>
                <div class='col-md-12 col-sm-12 col-xs-12 text-center'>
                    <input type="submit" class="btn btn-primary" name="btn_search" id="btn_search" value="search">
                    </select>
                </div>
                <br><br>
            </div>
            <br>
            <br>
            <div class='row'>
                <div class="col-md-6 col-sm-12 col-xs-12 refresh">
                    <?php if ($permissions['products_order']['update'] == 0) { ?>
                        <div class="alert alert-danger topmargin-sm">You have no permission to update products order.</div>
                    <?php } ?>

                    <form id="products_form" method="POST" action="products-order.php" data-parsley-validate class="form-horizontal form-label-left">
                        <input type="hidden" id="update_products_order" name="update_products_order" required value='1' />
                        <div class="form-group" style="overflow-y:scroll;height:400px;">
                            <input type="hidden" name="row_order" id="row_order" required readonly />
                            <ol id="sortable-row">
                                <?php foreach ($res as $product) { ?>
                                    <li id=<?php echo $product["id"]; ?>>
                                        <?php if (!empty($product["image"])) {
                                            echo "<big>" . $product["row_order"] . ".</big> &nbsp;<img src='$product[image]' height=30 > " . $product["name"];
                                        } else {
                                            echo "<big>" . $product["row_order"] . ".</big> &nbsp;<img src='images/logo.png' height=30 > " . $product["name"];
                                        } ?>
                                    </li>
                                <?php } ?>
                            </ol>
                        </div>
                        <div class="ln_solid"></div>
                        <div class="form-group">
                            <div class="col-md-6 col-sm-6 col-xs-12">
                                <button type="submit" id="submit_btn" class="btn btn-success">Save Order</button>
                            </div>
                        </div>
                        <div class="row">
                            <div id="result"></div>
                        </div>
                    </form>
                </div>
            </div>
        <?php } else { ?>
            <div class="alert alert-danger">You have no permission to view products order.</div>
        <?php } ?>
    </div>
</div><!-- /.content-wrapper -->
</body>

</html>
<?php include "footer.php"; ?>
<!-- jQuery -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.16.0/jquery.validate.min.js"></script>
<script>
    $(function() {
        $("#sortable-row").sortable({
            placeholder: "ui-state-highlight"
        });
        $("#sortable-row-2").sortable({
            placeholder: "ui-state-highlight"
        });
    });
</script>
<script>
    $('#products_form').on('submit', function(e) {
        e.preventDefault();
        var selectedLanguage = new Array();
        $('ol#sortable-row li').each(function() {
            selectedLanguage.push($(this).attr("id"));
        });
        $("#row_order").val(selectedLanguage);
        var formData = new FormData(this);
        if ($("#products_form").validate().form()) {
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
                    $('#result').show().delay(5000).fadeOut();
                    $('#submit_btn').html('Save Order');
                }
            });
        }
    });

    /* select the category */
    category_id = '<?= isset($_GET['category_id']) ? $db->escapeString($_GET['category_id']) : '' ?>';
    subcategory_id = '<?= isset($_GET['subcategory_id']) ? $db->escapeString($_GET['subcategory_id']) : '' ?>';
    $('#category_id').val(category_id).trigger("change", [category_id, subcategory_id]);
    $('#subcategory_id').val(subcategory_id);
      $('#btn_search').on('click', function() {
        redirect_to_url('products-order.php?category_id=' + $('#category_id').val() + '&subcategory_id=' + $('#subcategory_id').val());
    });
    function redirect_to_url(url) {
        window.location.href = url;
    }
    $('#category_id').on('change', function(e, category_id, subcategory_id) {
        var category_id = $('#category_id').val();
        $.ajax({
            url: "public/db-operation.php",
            data: "category_id=" + category_id + "&category=1",
            method: "POST",
            success: function(data) {
                $('#subcategory_id').html(data);
                $('#subcategory_id').val(subcategory_id);
            }
        });
    });
</script>