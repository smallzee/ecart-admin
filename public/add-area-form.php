<?php
include_once('includes/functions.php');
include_once('includes/custom-functions.php');
$fn = new custom_functions;
?>
<?php
$sql_query = "SELECT id, name FROM city where name!='Choose Your City'ORDER BY id ASC";
$db->sql($sql_query);
$res_city = $db->getResult();
if (isset($_POST['btnAdd'])) {
    if (ALLOW_MODIFICATION == 0 && !defined(ALLOW_MODIFICATION)) {
        echo '<label class="alert alert-danger">This operation is not allowed in demo panel!.</label>';
        return false;
    }
    if ($permissions['locations']['create'] == 1) {
        $area_name = $db->escapeString($fn->xss_clean($_POST['area_name']));
        $city_ID = $db->escapeString($fn->xss_clean($_POST['city_ID']));
        $delivery_charges = $db->escapeString($fn->xss_clean($_POST['delivery_charges']));
        $minimum_free_delivery_order_amount = $db->escapeString($fn->xss_clean($_POST['minimum_free_delivery_order_amount']));
        $sql_query = "SELECT * FROM area WHERE city_id=" . $city_ID;
        $db->sql($sql_query);
        $res_area = $db->getResult();
        $TOTAL = $db->numRows($res_area);
        $error = array();

        if (empty($area_name)) {
            $error['area_name'] = " <span class='label label-danger'>Required!</span>";
        }
        if (empty($delivery_charges)) {
            $error['delivery_charges'] = " <span class='label label-danger'>Required!</span>";
        }
        if (empty($minimum_free_delivery_order_amount)) {
            $error['minimum_free_delivery_order_amount'] = " <span class='label label-danger'>Required!</span>";
        }

        if (empty($city_ID) || $city_ID != "" || $city_ID == 0 ) {
            $error['city_ID'] = " <span class='label label-danger'>Required!</span>";
        }
        if ($TOTAL == 0) {

            if (!empty($area_name) && !empty($city_ID) && !empty($delivery_charges) && !empty($minimum_free_delivery_order_amount)) {
                $sql_query = "INSERT INTO area (name, city_id,delivery_charges,minimum_free_delivery_order_amount)	VALUES('$area_name', '$city_ID',$delivery_charges,$minimum_free_delivery_order_amount)";
                $db->sql($sql_query);
                $result = $db->getResult();
                if (!empty($result)) {
                    $result = 0;
                } else {
                    $result = 1;
                }
                if ($result == 1) {
                    $error['add_area'] = "<section class='content-header'><span class='label label-success'>Area Added Successfully</span><h4><small><a  href='areas.php'><i class='fa fa-angle-double-left'></i>&nbsp;&nbsp;&nbsp;Back to Areas</a></small></h4></section>";
                } else {
                    $error['add_area'] = " <span class='label label-danger'>Failed</span>";
                }
            }
        } else {
            if (!empty($area_name) && !empty($city_ID)) {
                $sql_query = "INSERT INTO area (name, city_id,delivery_charges,minimum_free_delivery_order_amount)	VALUES('$area_name', '$city_ID',$delivery_charges,$minimum_free_delivery_order_amount)";
                $db->sql($sql_query);
                $result = $db->getResult();
                if (!empty($result)) {
                    $result = 0;
                } else {
                    $result = 1;
                }

                if ($result == 1) {
                    $error['add_area'] = "<section class='content-header'><span class='label label-success'>Area Added Successfully</span><h4><small><a  href='areas.php'><i class='fa fa-angle-double-left'></i>&nbsp;&nbsp;&nbsp;Back to Areas</a></small></h4></section>";
                } else {
                    $error['add_area'] = " <span class='label label-danger'>Failed</span>";
                }
            }
        }
    } else {
        $error['add_area'] = "<section class='content-header'><span class='label label-danger'>You have no permission to create area</span></section>";
    }
}
?>
<section class="content-header">
    <h1>Add Area <small><a href='areas.php'><i class='fa fa-angle-double-left'></i>&nbsp;&nbsp;&nbsp;Back</a></small></h1>

    <?php echo isset($error['add_area']) ? $error['add_area'] : ''; ?>
    <ol class="breadcrumb">
        <li><a href="home.php"><i class="fa fa-home"></i> Home</a></li>
    </ol>
    <hr />
</section>
<section class="content">
    <div class="row">
        <div class="col-md-6">
            <?php if ($permissions['locations']['create'] == 0) { ?>
                <div class="alert alert-danger">You have no permission to create area</div>
            <?php } ?>
            <!-- general form elements -->
            <div class="box box-primary">
                <div class="box-header with-border">
                    <h3 class="box-title">Add Area</h3>
                </div><!-- /.box-header -->
                <!-- form start -->
                <form method="post" id="area_form" enctype="multipart/form-data">
                    <div class="box-body">
                        <div class="form-group">
                            <label for="exampleInputEmail1">City :</label><?php echo isset($error['city_ID']) ? $error['city_ID'] : ''; ?>
                            <select name="city_ID" id="city_ID" class="form-control" required>
                                <option value="">Select Your City</option>
                                <?php
                                if ($permissions['locations']['read'] == 1) {
                                    foreach ($res_city as $row) { ?>
                                        <option value="<?php echo $row['id']; ?>"><?php echo $row['name']; ?></option>
                                <?php }
                                } ?>
                            </select>
                            <br />
                        </div>
                        <div class="form-group">
                            <label for="exampleInputEmail1">Area Name</label><?php echo isset($error['area_name']) ? $error['area_name'] : ''; ?>
                            <input type="text" class="form-control" name="area_name" id="area_name" required />
                        </div>
                        <div class="form-group">
                            <label for="exampleInputEmail1">Minimum Free Delivery Order Amount</label><?php echo isset($error['minimum_free_delivery_order_amount']) ? $error['minimum_free_delivery_order_amount'] : ''; ?>
                            <input type="number" step="any" min="0" class="form-control" name="minimum_free_delivery_order_amount" id="minimum_free_delivery_order_amount" required />
                        </div>
                        <div class="form-group">
                            <label for="exampleInputEmail1">Delivery Charges</label><?php echo isset($error['delivery_charges']) ? $error['delivery_charges'] : ''; ?>
                            <input type="number" step="any" min="0" class="form-control" name="delivery_charges" id="delivery_charges"  required />
                        </div>
                    </div><!-- /.box-body -->
                    <div class="box-footer">
                        <input type="submit" class="btn-primary btn" value="Add" name="btnAdd" />&nbsp;
                        <input type="reset" class="btn-danger btn" value="Clear" />
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
    $('#area_form').validate({
        debug: false,
        rules: {
            city_ID: "required",
            area_name: "required",
            minimum_free_delivery_order_amount: "required",
            delivery_charges: "required"
        }
    });
</script>