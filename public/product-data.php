<?php
include_once('includes/custom-functions.php');
$fn = new custom_functions;
if (isset($_GET['id'])) {
  $ID = $db->escapeString($fn->xss_clean($_GET['id']));
} else {
  $ID = "";
}
// create array variable to store data from database
$data = array();
$sql_query = "SELECT *,p.id as  product_id,p.indicator,(SELECT name FROM subcategory s WHERE s.id=p.subcategory_id) as subcategory_name,(SELECT short_code FROM unit u where u.id=v.measurement_unit_id) as measurement_unit_name,(SELECT short_code FROM unit u where u.id=v.stock_unit_id)as stock_unit_name  FROM products p join product_variant v on p.id=v.product_id where v.id=" . $ID;
$db->sql($sql_query);
$res = $db->getResult();
foreach ($res as $row)
  $data = $row;
?>
<?php
if ($permissions['products']['read'] == 1) {
?>
  <section class="content-header">
    <h1>Products <small><?php echo $data['name']; ?></small></h1>
    <ol class="breadcrumb">
      <li><a href="home.php"><i class="fa fa-home"></i> Home</a></li>
    </ol>
  </section>
  <section class="content">
    <div class="row">
      <div class="col-md-6">
        <div class="box">
          <div class="box-header with-border">
            <h3 class="box-title">Product Detail</h3>
          </div><!-- /.box-header -->
          <div class="box-body">
            <?php
            if ($data['indicator'] == 0) {
              $indicator = "<span class='label label-info'>None</span>";
            }
            if ($data['indicator'] == 1) {
              $indicator = "<span class='label label-success'>Veg</span>";
            }
            if ($data['indicator'] == 2) {
              $indicator = "<span class='label label-danger'>Non-Veg</span>";
            }
            $return_status = $row['return_status'] == 1 ? "<span class='label label-success'>Allowed</span>" : "<span class='label label-danger'>Not Allowed</span>";

            $till_status = '<label class="label label-danger">Not Specified</label>';

            if ($row['till_status'] == 'received') {
              $till_status = '<label class="label label-primary">Received</label>';
            }
            if ($row['till_status'] == 'processed') {
              $till_status = '<label class="label label-info">Processed</label>';
            }
            if ($row['till_status'] == 'shipped') {
              $till_status = '<label class="label label-warning">Shipped</label>';
            }
            if ($row['till_status'] == 'delivered') {
              $till_status = '<label class="label label-success">Delivered</label>';
            }
            $cancelable_status = $row['cancelable_status'] == 1 ? "<span class='label label-success'>Allowed</span>" : "<span class='label label-danger'>Not Allowed</span>";
            ?>
            <table class="table table-bordered">
              <tr>
                <th style="width: 10px">ID</th>
                <td><?php echo $data['product_id']; ?></td>
              </tr>
              <tr>
                <th style="width: 10px">Name</th>
                <td><?php echo $data['name']; ?></td>
              </tr>
              <tr>
                <th style="width: 10px">Tax</th>
                <?php
                $t_id = $data['tax_id'];
                $db->sql("SET NAMES 'utf8'");
                $sql = "SELECT `title`,`percentage` FROM `taxes` where id= $t_id ORDER BY id DESC";
                $db->sql($sql);
                $tax_title = $db->getResult();
                ?>
                <td><?= (!empty($tax_title)) ? $tax_title[0]['title'] . " " . $tax_title[0]['percentage'] . "%" : " 0% " ?></td>
              </tr>
              <tr>
                <th style="width: 10px">Stock</th>
                <td><?php echo $data['stock'] . ' ' . $data['stock_unit_name']; ?></td>
              </tr>
              <tr>
                <th style="width: 10px">Status</th>
                <td><?php echo $data['serve_for'] == 'Sold Out' ? "<span class='label label-danger'>Sold Out</label>" : "<span class='label label-success'>Available</label>"; ?></td>
              </tr>
              <tr>
                <th style="width: 10px">measurement (kg, ltr, gm)</th>
                <td><?php echo $data['measurement'] . " " . $data['measurement_unit_name']; ?></td>
              </tr>
              <tr>
                <th style="width: 10px">Price(<?= $settings['currency'] ?>)</th>
                <td><?php echo $data['price']; ?></td>
              </tr>
              <tr>
                <th style="width: 10px">Discounted Price(<?= $settings['currency'] ?>)</th>
                <td><?php echo $data['discounted_price']; ?></td>
              </tr>
              <tr>
                <th style="width: 10px">Indicator</th>
                <td><?php echo $indicator; ?></td>
              </tr>
              <tr>
                <th style="width: 10px">Manufacturer</th>
                <td><?php echo $data['manufacturer']; ?></td>
              </tr>
              <tr>
                <th style="width: 10px">Made In</th>
                <td><?php echo $data['made_in']; ?></td>
              </tr>
              <tr>
                <th style="width: 10px">Return</th>
                <td><?php echo $return_status; ?></td>
              </tr>
              <tr>
                <th style="width: 10px">Cancellation</th>
                <td><?php echo $cancelable_status; ?></td>
              </tr>
              <tr>
                <th style="width: 10px">Till Status</th>
                <td><?php echo $till_status; ?></td>
              </tr>
              <tr>
                <th style="width: 10px">Sub Category</th>
                <td><?php echo $data['subcategory_name']; ?></td>
              </tr>
              <tr>
                <th style="width: 10px">Main Image</th>
                <td><img src="<?php echo $data['image']; ?>"  height="150" /></td>
              </tr>
              <tr>
                <th style="width: 10px">Other Images</th>
                <td><?php $other_images = json_decode($data['other_images']);
                    if (!empty($other_images)) {
                      foreach ($other_images as $image) { ?>
                      <img src="<?= $image; ?>" height="150" />
                  <?php }
                    } else {
                      echo "<h4>No other images found</h4>";
                    } ?>
                </td>
              </tr>
              <tr>
                <th style="width: 10px">description</th>
                <td><?php echo $data['description']; ?></td>
              </tr>
            </table>
          </div><!-- /.box-body -->
          <div class="box-footer clearfix">
            <a href="edit-product.php?id=<?php echo $data['product_id']; ?>"><button class="btn btn-primary">Edit</button></a>
            <a href="delete-product.php?id=<?php echo $data['product_id']; ?>"><button class="btn btn-danger">Delete</button></a>
          </div>
        </div><!-- /.box -->
      </div>
    </div>
  </section>
<?php } else { ?>
  <section class="content-header">
    <h1>Products <small><a href='home.php'> <i class='fa fa-angle-double-left'></i>&nbsp;&nbsp;&nbsp;Home</a></small></h1>
    <ol class="breadcrumb">
      <li><a href="home.php"><i class="fa fa-home"></i> Home</a></li>
    </ol>
  </section>
  <div class="alert alert-danger topmargin-sm">You have no permission to view product.</div>

<?php }
$db->disconnect(); ?>