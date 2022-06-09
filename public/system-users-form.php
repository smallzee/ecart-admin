<?php 

include_once('includes/crud.php');
$db = new Database();
$db->connect();
$db->sql("SET NAMES 'utf8'");

include('includes/variables.php');
include_once('includes/custom-functions.php');

$fn = new custom_functions;
$config = $fn->get_configurations();
$permissions = $fn->get_permissions($_SESSION['id']);
?>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.16.0/jquery.validate.min.js"></script>
<style>
table {
font-family: arial, sans-serif;
border-collapse: collapse;
width: 100%;
}

td, th {
border: 1px solid #dddddd;
text-align: left;
padding: 8px;
}

tr:nth-child(even) {
background-color: #dddddd;
}
</style>
<?php
if($_SESSION['role']=='editor'){
  echo "<p class='alert alert-danger topmargin-sm'>Access denied - You are not authorized to access this page.</p>";
  return false;
}
?>
<section class="content-header">
<h1>Create admin or editor /<small><a href="home.php"><i class="fa fa-home"></i> Home</a></small></h1>

</section>
<!-- Main content -->
<section class="content">
<!-- Main row -->
<div class="row">
    <div class="col-md-6">
          <!-- general form elements -->
          <div class="box box-primary">
            <div class="box-header with-border">
              <h3 class="box-title">Add Admin / Editor</h3>

            </div><!-- /.box-header -->
            <!-- form start -->
            <form  method="post" id="add_form" action="public/db-operation.php">
                <input type="hidden" id="add_system_user" name="add_system_user" value="1">
              <div class="box-body">
                <div class="form-group">
                  <label for="">Username</label>
                  <input type="text" class="form-control"  name="username">
                </div>
                          <div class="form-group">
                  <label for="">Email</label>
                  <input type="text" class="form-control"  name="email">
                </div>
                <div class="form-group">
                  <label for="">Password</label>
                  <input type="password" class="form-control"  name="password" id="password">
                </div>
                <div class="form-group">
                  <label for="">Confirm Password</label>
                  <input type="password" class="form-control"  name="confirm_password">
                </div>
                <div class="form-group">
                  <label for="">Role</label>
                  <select name="role" class="form-control">
                  	<option value="">---Select---</option>
                    <option value="admin">Admin</option>
                  	<option value="editor">Editor</option>
                  </select>
                </div>
                
              </div><!-- /.box-body -->


              <div class="box-footer">
                <button type="submit" class="btn btn-primary" id="submit_btn" name="btnAdd">Add</button>
                <input type="reset" class="btn-warning btn" value="Clear"/>
            
              </div>
              <div class="form-group">
                  
                  <div id="result" style="display: none;"></div>
                </div>
            
          </div><!-- /.box -->
          <?php
           if($_SESSION['role']!='editor'){?>
          <div class="box">
                <div class="box-header">
                    <h3 class="box-title">System Users</h3>
                </div>
                  <div class="box-body table-responsive">
                    <table class="table table-hover" data-toggle="table" id="system-users"
                        data-url="api-firebase/get-bootstrap-table-data.php?table=system-users"
                        data-page-list="[5, 10, 20, 50, 100, 200]"
                        data-show-refresh="true" data-show-columns="true"
                        data-side-pagination="server" data-pagination="true"
                        data-search="true" data-trim-on-search="false"
                        data-sort-name="id" data-sort-order="asc">
                        <thead>
                        <tr>
                            <th data-field="id" data-sortable="true">ID</th>
                            <th data-field="username" data-sortable="true">Username</th>
                            <th data-field="email" data-sortable="true">Email</th>
                            <th data-field="role" data-sortable="true">Role</th>
                            <th data-field="city_ids" data-sortable="true" data-visible="false">City Id(s)</th>
                            <th data-field="cities" data-sortable="true" data-visible="false">Cities</th>
                            <th data-field="created_by" data-sortable="true" data-visible="false">Created By</th>
                            <th data-field="created_by_id" data-sortable="true" data-visible="false">Created By Id</th>
                            <th data-field="date_created" data-visible="false">Date Created</th>
                            <th data-field="operate" data-events="actionEvents">Action</th>
                        </tr>
                        </thead>
                    </table>
                </div>
            </div>
          <?php } ?>
         </div>
         <div class="col-xs-6">
        <div class="box box-primary">
            <table>
        <tr>
          <th>Module/Permissions</th>
          <th>Create</th>
          <th>Read</th>
          <th>Update</th>
          <th>Delete</th>
        </tr>
        <tr>
          <td>Orders</td>
          <td><input type="checkbox" id="create-order-button" class="js-switch" checked>
                     <input type="hidden" id="is-create-order" name="is-create-order" value="1">
                 </td>
          <td><input type="checkbox" id="read-order-button" class="js-switch" checked>
                     <input type="hidden" id="is-read-order" name="is-read-order" value="1">
                 </td>
                 <td><input type="checkbox" id="update-order-button" class="js-switch" checked>
                     <input type="hidden" id="is-update-order" name="is-update-order" value="1">
                 </td>
                 <td><input type="checkbox" id="delete-order-button" class="js-switch" checked>
                     <input type="hidden" id="is-delete-order" name="is-delete-order" value="1">
                 </td>
              </tr>
              <tr>
          <td>Categories</td>
          <td><input type="checkbox" id="create-category-button" class="js-switch" checked>
                     <input type="hidden" id="is-create-category" name="is-create-category" value="1">
                 </td>
          <td><input type="checkbox" id="read-category-button" class="js-switch" checked>
                     <input type="hidden" id="is-read-category" name="is-read-category" value="1">
                 </td>
                 <td><input type="checkbox" id="update-category-button" class="js-switch" checked>
                     <input type="hidden" id="is-update-category" name="is-update-category" value="1">
                 </td>
                 <td><input type="checkbox" id="delete-category-button" class="js-switch" checked>
                     <input type="hidden" id="is-delete-category" name="is-delete-category" value="1">
                 </td>
              </tr>
              <tr>
          <td>Subcategories</td>
          <td><input type="checkbox" id="create-subcategory-button" class="js-switch" checked>
                     <input type="hidden" id="is-create-subcategory" name="is-create-subcategory" value="1">
                 </td>
          <td><input type="checkbox" id="read-subcategory-button" class="js-switch" checked>
                     <input type="hidden" id="is-read-subcategory" name="is-read-subcategory" value="1">
                 </td>
                 <td><input type="checkbox" id="update-subcategory-button" class="js-switch" checked>
                     <input type="hidden" id="is-update-subcategory" name="is-update-subcategory" value="1">
                 </td>
                 <td><input type="checkbox" id="delete-subcategory-button" class="js-switch" checked>
                     <input type="hidden" id="is-delete-subcategory" name="is-delete-subcategory" value="1">
                 </td>
                </tr>
              <tr>
          <td>Products</td>
          <td><input type="checkbox" id="create-product-button" class="js-switch" checked>
                     <input type="hidden" id="is-create-product" name="is-create-product" value="1">
                 </td>
          <td><input type="checkbox" id="read-product-button" class="js-switch" checked>
                     <input type="hidden" id="is-read-product" name="is-read-product" value="1">
                 </td>
                 <td><input type="checkbox" id="update-product-button" class="js-switch" checked>
                     <input type="hidden" id="is-update-product" name="is-update-product" value="1">
                 </td>
                 <td><input type="checkbox" id="delete-product-button" class="js-switch" checked>
                     <input type="hidden" id="is-delete-product" name="is-delete-product" value="1">
                 </td>
                </tr>
              <tr>
                <td>Products Order</td>
                <td>-</td>
                <td><input type="checkbox" id="read-products-order-button" class="js-switch" checked>
                     <input type="hidden" id="is-read-products-order" name="is-read-products-order" value="1">
                 </td>
                 <td><input type="checkbox" id="update-products-order-button" class="js-switch" checked>
                     <input type="hidden" id="is-update-products-order" name="is-update-products-order" value="1">
                 </td>
                 <td>-</td>
                </tr>
              <tr>
          <td>Home Slider Images</td>
          <td><input type="checkbox" id="create-home-slider-button" class="js-switch" checked>
                     <input type="hidden" id="is-create-home-slider" name="is-create-home-slider" value="1">
                 </td>
          <td><input type="checkbox" id="read-home-slider-button" class="js-switch" checked>
                     <input type="hidden" id="is-read-home-slider" name="is-read-home-slider" value="1">
                 </td>
                 <td>-</td>
                 <td><input type="checkbox" id="delete-home-slider-button" class="js-switch" checked>
                     <input type="hidden" id="is-delete-home-slider" name="is-delete-home-slider" value="1">
                 </td>
              </tr>
              <tr>
                <td>New Offer Images</td>
                <td><input type="checkbox" id="create-new-offer-button" class="js-switch" checked>
                     <input type="hidden" id="is-create-new-offer" name="is-create-new-offer" value="1">
                 </td>
                <td><input type="checkbox" id="read-new-offer-button" class="js-switch" checked>
                     <input type="hidden" id="is-read-new-offer" name="is-read-new-offer" value="1">
                 </td>
                 <td>-</td>
                 <td><input type="checkbox" id="delete-new-offer-button" class="js-switch" checked>
                     <input type="hidden" id="is-delete-new-offer" name="is-delete-new-offer" value="1">
                 </td>
              </tr>
              <tr>
                <td>Promo Codes</td>
                <td><input type="checkbox" id="create-promo-button" class="js-switch" checked>
                     <input type="hidden" id="is-create-promo" name="is-create-promo" value="1">
                 </td>
                <td><input type="checkbox" id="read-promo-button" class="js-switch" checked>
                     <input type="hidden" id="is-read-promo" name="is-read-promo" value="1">
                 </td>
                 <td><input type="checkbox" id="update-promo-button" class="js-switch" checked>
                     <input type="hidden" id="is-update-promo" name="is-update-promo" value="1">
                 </td>
                 <td><input type="checkbox" id="delete-promo-button" class="js-switch" checked>
                     <input type="hidden" id="is-delete-promo" name="is-delete-promo" value="1">
                 </td>
                </tr>
              <tr>
                <td>Featured Section</td>
                <td><input type="checkbox" id="create-featured-button" class="js-switch" checked>
                     <input type="hidden" id="is-create-featured" name="is-create-featured" value="1">
                 </td>
                <td><input type="checkbox" id="read-featured-button" class="js-switch" checked>
                     <input type="hidden" id="is-read-featured" name="is-read-featured" value="1">
                 </td>
                 <td><input type="checkbox" id="update-featured-button" class="js-switch" checked>
                     <input type="hidden" id="is-update-featured" name="is-update-featured" value="1">
                 </td>
                 <td><input type="checkbox" id="delete-featured-button" class="js-switch" checked>
                     <input type="hidden" id="is-delete-featured" name="is-delete-featured" value="1">
                 </td>
               </tr>
               <tr>
                <td>Customers</td>
                <td>-
                 </td>
                <td><input type="checkbox" id="read-customers-button" class="js-switch" checked>
                     <input type="hidden" id="is-read-customers" name="is-read-customers" value="1">
                 </td>
                 <td>-
                 </td>
                 <td>-
                 </td>
               </tr>
               <tr>
                <td>Payment Requests</td>
                <td>-
                 </td>
                <td><input type="checkbox" id="read-payment-button" class="js-switch" checked>
                     <input type="hidden" id="is-read-payment" name="is-read-payment" value="1">
                 </td>
                 <td><input type="checkbox" id="update-payment-button" class="js-switch" checked>
                     <input type="hidden" id="is-update-payment" name="is-update-payment" value="1">
                 </td>
                 <td>-
                 </td>
               </tr>
               <tr>
                <td>Return Requests</td>
                <td>-
                <td><input type="checkbox" id="read-return-button" class="js-switch" checked>
                     <input type="hidden" id="is-read-return" name="is-read-return" value="1">
                 </td>
                 <td><input type="checkbox" id="update-return-button" class="js-switch" checked>
                     <input type="hidden" id="is-update-return" name="is-update-return" value="1">
                 </td>
                 <td><input type="checkbox" id="delete-return-button" class="js-switch" checked>
                     <input type="hidden" id="is-delete-return" name="is-delete-return" value="1">
                 </td>
               </tr>
               <tr>
                <td>Delivery Boys</td>
                <td><input type="checkbox" id="create-delivery-button" class="js-switch" checked>
                     <input type="hidden" id="is-create-delivery" name="is-create-delivery" value="1">
                 </td>
                <td><input type="checkbox" id="read-delivery-button" class="js-switch" checked>
                     <input type="hidden" id="is-read-delivery" name="is-read-delivery" value="1">
                 </td>
                 <td><input type="checkbox" id="update-delivery-button" class="js-switch" checked>
                     <input type="hidden" id="is-update-delivery" name="is-update-delivery" value="1">
                 </td>
                 <td><input type="checkbox" id="delete-delivery-button" class="js-switch" checked>
                     <input type="hidden" id="is-delete-delivery" name="is-delete-delivery" value="1">
                 </td>
               </tr>
               <tr>
                <td>Fund Transfers(Delivery Boys)</td>
                <td>-
                 </td>
                 <td><input type="checkbox" id="read-fund-button" class="js-switch" checked>
                     <input type="hidden" id="is-read-fund" name="is-read-transaction" value="1">
                 </td>
                 <td>-
                 </td>
                 <td>-
                 </td>
               </tr>
               <tr>
                <td>Notifications</td>
                <td><input type="checkbox" id="create-notification-button" class="js-switch" checked>
                     <input type="hidden" id="is-create-notification" name="is-create-notification" value="1">
                 </td>
                 <td><input type="checkbox" id="read-notification-button" class="js-switch" checked>
                     <input type="hidden" id="is-read-notification" name="is-read-notification" value="1">
                 </td>
                 <td>-</td>
                 <td><input type="checkbox" id="delete-notification-button" class="js-switch" checked>
                     <input type="hidden" id="is-delete-notification" name="is-delete-notification" value="1">
                 </td>
               </tr>
               <tr>
                <td>Transactions</td>
                <td>-
                 </td>
                 <td><input type="checkbox" id="read-transaction-button" class="js-switch" checked>
                     <input type="hidden" id="is-read-transaction" name="is-read-transaction" value="1">
                 </td>
                 <td>-
                 </td>
                 <td>-
                 </td>
               </tr>
               <tr>
                <td>System settings</td>
                <td>-
                 </td>
                  <td><input type="checkbox" id="read-settings-button" class="js-switch" checked>
                     <input type="hidden" id="is-read-settings" name="is-read-settings" value="1">
                 </td>
                 <td><input type="checkbox" id="update-settings-button" class="js-switch" checked>
                     <input type="hidden" id="is-update-settings" name="is-update-settings" value="1">
                 </td>
                 <td>-
                 </td>
               </tr>
               <tr>
                <td>Locations</td>
                <td><input type="checkbox" id="create-location-button" class="js-switch" checked>
                     <input type="hidden" id="is-create-location" name="is-create-location" value="1">
                 </td>
                <td><input type="checkbox" id="read-location-button" class="js-switch" checked>
                     <input type="hidden" id="is-read-location" name="is-read-location" value="1">
                 </td>
                 <td><input type="checkbox" id="update-location-button" class="js-switch" checked>
                     <input type="hidden" id="is-update-location" name="is-update-location" value="1">
                 </td>
                 <td><input type="checkbox" id="delete-location-button" class="js-switch" checked>
                     <input type="hidden" id="is-delete-location" name="is-delete-location" value="1">
                 </td>
               </tr>
               <tr>
                <td>Reports</td>
                <td><input type="checkbox" id="create-report-button" class="js-switch" checked>
                     <input type="hidden" id="is-create-report" name="is-create-report" value="1">
                 </td>
                <td><input type="checkbox" id="read-report-button" class="js-switch" checked>
                     <input type="hidden" id="is-read-report" name="is-read-report" value="1">
                 </td>
                 <td>-
                 </td>
                 <td>-
                 </td>
               </tr>
               <tr>
                <td>Faqs</td>
                <td><input type="checkbox" id="create-faq-button" class="js-switch" checked>
                     <input type="hidden" id="is-create-faq" name="is-create-faq" value="1">
                 </td>
                <td><input type="checkbox" id="read-faq-button" class="js-switch" checked>
                     <input type="hidden" id="is-read-faq" name="is-read-faq" value="1">
                 </td>
                 <td><input type="checkbox" id="update-faq-button" class="js-switch" checked>
                     <input type="hidden" id="is-update-faq" name="is-update-faq" value="1">
                 </td>
                <td><input type="checkbox" id="delete-faq-button" class="js-switch" checked>
                     <input type="hidden" id="is-delete-faq" name="is-delete-faq" value="1">
                 </td>
               </tr>

      </table>
            
        </div>
        </form>
    </div>
    <!-- Left col -->
    
    <div class="separator"> </div>
</div>
<div class="modal fade" id='editSystemUserModal' tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel">
                <div class="modal-dialog modal-md" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                            <h4 class="modal-title" id="myModalLabel">Update Permissions</h4>
                        </div>
                        
                        <div class="modal-body">
                            <div class="box-body">
                            <form id="update_form"  method="POST" action ="public/db-operation.php" data-parsley-validate class="form-horizontal form-label-left">
                                <input type='hidden' name="system_user_id" id="system_user_id" value=''/>
                                <input type='hidden' name="update_system_user" id="update_system_user" value='1'/>
                                <div class="box box-primary">
            <table>
        <tr>
          <th>Module/Permissions</th>
          <th>Create</th>
          <th>Read</th>
          <th>Update</th>
          <th>Delete</th>
        </tr>
        <tr>
          <td>Orders</td>
          <td><input type="checkbox" id="permission-create-order-button" class="js-switch">
                     <input type="hidden" id="permission-is-create-order" name="permission-is-create-order" value="">
                 </td>
          <td><input type="checkbox" id="permission-read-order-button" class="js-switch">
                     <input type="hidden" id="permission-is-read-order" name="permission-is-read-order" value="">
                 </td>
                 <td><input type="checkbox" id="permission-update-order-button" class="js-switch">
                     <input type="hidden" id="permission-is-update-order" name="permission-is-update-order" value="">
                 </td>
                 <td><input type="checkbox" id="permission-delete-order-button" class="js-switch">
                     <input type="hidden" id="permission-is-delete-order" name="permission-is-delete-order" value="">
                 </td>
              </tr>
              <tr>
          <td>Categories</td>
          <td><input type="checkbox" id="permission-create-category-button" class="js-switch">
                     <input type="hidden" id="permission-is-create-category" name="permission-is-create-category" value="">
                 </td>
          <td><input type="checkbox" id="permission-read-category-button" class="js-switch">
                     <input type="hidden" id="permission-is-read-category" name="permission-is-read-category" value="">
                 </td>
                 <td><input type="checkbox" id="permission-update-category-button" class="js-switch">
                     <input type="hidden" id="permission-is-update-category" name="permission-is-update-category" value="">
                 </td>
                 <td><input type="checkbox" id="permission-delete-category-button" class="js-switch">
                     <input type="hidden" id="permission-is-delete-category" name="permission-is-delete-category" value="">
                 </td>
              </tr>
              <tr>
          <td>Subcategories</td>
          <td><input type="checkbox" id="permission-create-subcategory-button" class="js-switch">
                     <input type="hidden" id="permission-is-create-subcategory" name="permission-is-create-subcategory" value="">
                 </td>
          <td><input type="checkbox" id="permission-read-subcategory-button" class="js-switch">
                     <input type="hidden" id="permission-is-read-subcategory" name="permission-is-read-subcategory" value="">
                 </td>
                 <td><input type="checkbox" id="permission-update-subcategory-button" class="js-switch">
                     <input type="hidden" id="permission-is-update-subcategory" name="permission-is-update-subcategory" value="">
                 </td>
                 <td><input type="checkbox" id="permission-delete-subcategory-button" class="js-switch">
                     <input type="hidden" id="permission-is-delete-subcategory" name="permission-is-delete-subcategory" value="">
                 </td>
                </tr>
              <tr>
          <td>Products</td>
          <td><input type="checkbox" id="permission-create-product-button" class="js-switch">
                     <input type="hidden" id="permission-is-create-product" name="permission-is-create-product" value="">
                 </td>
          <td><input type="checkbox" id="permission-read-product-button" class="js-switch">
                     <input type="hidden" id="permission-is-read-product" name="permission-is-read-product" value="">
                 </td>
                 <td><input type="checkbox" id="permission-update-product-button" class="js-switch">
                     <input type="hidden" id="permission-is-update-product" name="permission-is-update-product" value="">
                 </td>
                 <td><input type="checkbox" id="permission-delete-product-button" class="js-switch">
                     <input type="hidden" id="permission-is-delete-product" name="permission-is-delete-product" value="">
                 </td>
                </tr>
              <tr>
                <td>Products Order</td>
                <td>-</td>
                <td><input type="checkbox" id="permission-read-products-order-button" class="js-switch">
                     <input type="hidden" id="permission-is-read-products-order" name="permission-is-read-products-order" value="">
                 </td>
                 <td><input type="checkbox" id="permission-update-products-order-button" class="js-switch">
                     <input type="hidden" id="permission-is-update-products-order" name="permission-is-update-products-order" value="">
                 </td>
                 <td>-</td>
                </tr>
              <tr>
          <td>Home Slider Images</td>
          <td><input type="checkbox" id="permission-create-home-slider-button" class="js-switch">
                     <input type="hidden" id="permission-is-create-home-slider" name="permission-is-create-home-slider" value="">
                 </td>
          <td><input type="checkbox" id="permission-read-home-slider-button" class="js-switch">
                     <input type="hidden" id="permission-is-read-home-slider" name="permission-is-read-home-slider" value="">
                 </td>
                 <td>-</td>
                 <td><input type="checkbox" id="permission-delete-home-slider-button" class="js-switch">
                     <input type="hidden" id="permission-is-delete-home-slider" name="permission-is-delete-home-slider" value="">
                 </td>
              </tr>
              <tr>
                <td>New Offer Images</td>
                <td><input type="checkbox" id="permission-create-new-offer-button" class="js-switch">
                     <input type="hidden" id="permission-is-create-new-offer" name="permission-is-create-new-offer" value="">
                 </td>
                <td><input type="checkbox" id="permission-read-new-offer-button" class="js-switch">
                     <input type="hidden" id="permission-is-read-new-offer" name="permission-is-read-new-offer" value="">
                 </td>
                 <td>-</td>
                 <td><input type="checkbox" id="permission-delete-new-offer-button" class="js-switch">
                     <input type="hidden" id="permission-is-delete-new-offer" name="permission-is-delete-new-offer" value="">
                 </td>
              </tr>
              <tr>
                <td>Promo Codes</td>
                <td><input type="checkbox" id="permission-create-promo-button" class="js-switch">
                     <input type="hidden" id="permission-is-create-promo" name="permission-is-create-promo" value="">
                 </td>
                <td><input type="checkbox" id="permission-read-promo-button" class="js-switch">
                     <input type="hidden" id="permission-is-read-promo" name="permission-is-read-promo" value="">
                 </td>
                 <td><input type="checkbox" id="permission-update-promo-button" class="js-switch">
                     <input type="hidden" id="permission-is-update-promo" name="permission-is-update-promo" value="">
                 </td>
                 <td><input type="checkbox" id="permission-delete-promo-button" class="js-switch">
                     <input type="hidden" id="permission-is-delete-promo" name="permission-is-delete-promo" value="">
                 </td>
                </tr>
              <tr>
                <td>Featured Section</td>
                <td><input type="checkbox" id="permission-create-featured-button" class="js-switch">
                     <input type="hidden" id="permission-is-create-featured" name="permission-is-create-featured" value="">
                 </td>
                <td><input type="checkbox" id="permission-read-featured-button" class="js-switch">
                     <input type="hidden" id="permission-is-read-featured" name="permission-is-read-featured" value="">
                 </td>
                 <td><input type="checkbox" id="permission-update-featured-button" class="js-switch">
                     <input type="hidden" id="permission-is-update-featured" name="permission-is-update-featured" value="">
                 </td>
                 <td><input type="checkbox" id="permission-delete-featured-button" class="js-switch">
                     <input type="hidden" id="permission-is-delete-featured" name="permission-is-delete-featured" value="">
                 </td>
               </tr>
               <tr>
                <td>Customers</td>
                <td>-
                 </td>
                <td><input type="checkbox" id="permission-read-customers-button" class="js-switch">
                     <input type="hidden" id="permission-is-read-customers" name="permission-is-read-customers" value="">
                 </td>
                 <td>-
                 </td>
                 <td>-
                 </td>
               </tr>
               <tr>
                <td>Payment Requests</td>
                <td>-
                 </td>
                <td><input type="checkbox" id="permission-read-payment-button" class="js-switch">
                     <input type="hidden" id="permission-is-read-payment" name="permission-is-read-payment" value="">
                 </td>
                 <td><input type="checkbox" id="permission-update-payment-button" class="js-switch">
                     <input type="hidden" id="permission-is-update-payment" name="permission-is-update-payment" value="">
                 </td>
                 <td>-
                 </td>
               </tr>
               <tr>
                <td>Return Requests</td>
                <td>-
                <td><input type="checkbox" id="permission-read-return-button" class="js-switch" checked>
                     <input type="hidden" id="permission-is-read-return" name="permission-is-read-return" value="">
                 </td>
                 <td><input type="checkbox" id="permission-update-return-button" class="js-switch" checked>
                     <input type="hidden" id="permission-is-update-return" name="permission-is-update-return" value="">
                 </td>
                 <td><input type="checkbox" id="permission-delete-return-button" class="js-switch" checked>
                     <input type="hidden" id="permission-is-delete-return" name="permission-is-delete-return" value="">
                 </td>
               </tr>
               <tr>
                <td>Delivery Boys</td>
                <td><input type="checkbox" id="permission-create-delivery-button" class="js-switch">
                     <input type="hidden" id="permission-is-create-delivery" name="permission-is-create-delivery" value="">
                 </td>
                <td><input type="checkbox" id="permission-read-delivery-button" class="js-switch">
                     <input type="hidden" id="permission-is-read-delivery" name="permission-is-read-delivery" value="">
                 </td>
                 <td><input type="checkbox" id="permission-update-delivery-button" class="js-switch">
                     <input type="hidden" id="permission-is-update-delivery" name="permission-is-update-delivery" value="">
                 </td>
                 <td><input type="checkbox" id="permission-delete-delivery-button" class="js-switch">
                     <input type="hidden" id="permission-is-delete-delivery" name="permission-is-delete-delivery" value="">
                 </td>
               </tr>
               <tr>
                <td>Fund Transfers(Delivery Boys)</td>
                <td>-
                 </td>
                 <td><input type="checkbox" id="permission-read-fund-button" class="js-switch">
                     <input type="hidden" id="permission-is-read-fund" name="permission-is-read-fund" value="">
                 </td>
                 <td>-
                 </td>
                 <td>-
                 </td>
               </tr>
               <tr>
                <td>Notifications</td>
                <td><input type="checkbox" id="permission-create-notification-button" class="js-switch">
                     <input type="hidden" id="permission-is-create-notification" name="permission-is-create-notification" value="">
                 </td>
                 <td><input type="checkbox" id="permission-read-notification-button" class="js-switch">
                     <input type="hidden" id="permission-is-read-notification" name="permission-is-read-notification" value="">
                 </td>
                 <td>-</td>
                 <td><input type="checkbox" id="permission-delete-notification-button" class="js-switch">
                     <input type="hidden" id="permission-is-delete-notification" name="permission-is-delete-notification" value="">
                 </td>
               </tr>
               <tr>
                <td>Transactions</td>
                <td>-
                 </td>
                 <td><input type="checkbox" id="permission-read-transaction-button" class="js-switch">
                     <input type="hidden" id="permission-is-read-transaction" name="permission-is-read-transaction" value="">
                 </td>
                 <td>-
                 </td>
                 <td>-
                 </td>
               </tr>
               <tr>
                <td>System settings</td>
                <td>-
                 </td>
                 <td><input type="checkbox" id="permission-read-settings-button" class="js-switch">
                     <input type="hidden" id="permission-is-read-settings" name="permission-is-read-settings" value="">
                 </td>
                 <td><input type="checkbox" id="permission-update-settings-button" class="js-switch">
                     <input type="hidden" id="permission-is-update-settings" name="permission-is-update-settings" value="">
                 </td>
                 <td>-
                 </td>
               </tr>
               <tr>
                <td>Locations</td>
                <td><input type="checkbox" id="permission-create-location-button" class="js-switch">
                     <input type="hidden" id="permission-is-create-location" name="permission-is-create-location" value="">
                 </td>
                <td><input type="checkbox" id="permission-read-location-button" class="js-switch">
                     <input type="hidden" id="permission-is-read-location" name="permission-is-read-location" value="">
                 </td>
                 <td><input type="checkbox" id="permission-update-location-button" class="js-switch">
                     <input type="hidden" id="permission-is-update-location" name="permission-is-update-location" value="">
                 </td>
                 <td><input type="checkbox" id="permission-delete-location-button" class="js-switch">
                     <input type="hidden" id="permission-is-delete-location" name="permission-is-delete-location" value="">
                 </td>
               </tr>
               <tr>
                <td>Reports</td>
                <td><input type="checkbox" id="permission-create-report-button" class="js-switch">
                     <input type="hidden" id="permission-is-create-report" name="permission-is-create-report" value="">
                 </td>
                <td><input type="checkbox" id="permission-read-report-button" class="js-switch">
                     <input type="hidden" id="permission-is-read-report" name="permission-is-read-report" value="">
                 </td>
                 <td>-
                 </td>
                 <td>-
                 </td>
               </tr>
               <tr>
                <td>Faqs</td>
                <td><input type="checkbox" id="permission-create-faq-button" class="js-switch">
                     <input type="hidden" id="permission-is-create-faq" name="permission-is-create-faq" value="">
                 </td>
                <td><input type="checkbox" id="permission-read-faq-button" class="js-switch">
                     <input type="hidden" id="permission-is-read-faq" name="permission-is-read-faq" value="">
                 </td>
                 <td><input type="checkbox" id="permission-update-faq-button" class="js-switch">
                     <input type="hidden" id="permission-is-update-faq" name="permission-is-update-faq" value="">
                 </td>
                <td><input type="checkbox" id="permission-delete-faq-button" class="js-switch">
                     <input type="hidden" id="permission-is-delete-faq" name="permission-is-delete-faq" value="">
                 </td>
               </tr>

      </table>
            
        </div>
                                <input type="hidden" id="id" name="id">
                                <div class="ln_solid"></div>
                                <div class="form-group">
                                    <div class="col-md-6 col-sm-6 col-xs-12 col-md-offset-3">
                                        <button type="submit" id="update_btn" class="btn btn-success">Update</button>
                                    </div>
                                </div>
                                <div class="form-group">
                      
                                    <div class="row"><div  class="col-md-offset-3 col-md-8" style ="display:none;" id="update_result"></div></div>
                                </div>
                            </form>
                        </div>
                            
                        </div>
                    </div>
                </div>
            </div>
</section>

            
    
            
<script>
  $('#add_form').validate({
    rules:{
    username:"required",
    email:"required",
    password:"required",
    role:"required",
    confirm_password : {
                required:true,
                equalTo : "#password"
            }
    }
  });
</script>

<script>
  $('#add_form').on('submit',function(e){
    e.preventDefault();
    var formData = new FormData(this);
  if( $("#add_form").validate().form() ){
        if(confirm('Are you sure?Want to Add.')){
        $.ajax({
        type:'POST',
        url: $(this).attr('action'),
        data:formData,
        beforeSend:function(){$('#submit_btn').html('Please wait..');},
        cache:false,
        contentType: false,
        processData: false,
        success:function(result){
            $('#result').html(result);
            $('#result').show().delay(6000).fadeOut();
            $('#submit_btn').html('Submit');
            $('#add_form')[0].reset();
            // $("#city_ids").val('').trigger('change');
            $('#system-users').bootstrapTable('refresh');    
        }
        });
        }
          }
    }); 
</script>

<script>
  $('#update_form').on('submit',function(e){
    e.preventDefault();
    var formData = new FormData(this);
        if(confirm('Are you sure?Want to update.')){
        $.ajax({
        type:'POST',
        url: $(this).attr('action'),
        data:formData,
        beforeSend:function(){$('#update_btn').html('Please wait..');},
        cache:false,
        contentType: false,
        processData: false,
        success:function(result){
            $('#update_result').html(result);
            $('#update_result').show().delay(6000).fadeOut();
            $('#update_btn').html('Submit');
            $('#system-users').bootstrapTable('refresh');
            setTimeout(function() {$('#editSystemUserModal').modal('hide');}, 3000);
        }
        });
          }
    }); 
</script>

<script>
    var changeCheckbox = document.querySelector('#create-order-button');
    var init = new Switchery(changeCheckbox);
    changeCheckbox.onchange = function() {
    if ($(this).is(':checked')) {
        $('#is-create-order').val(1);
    }else{
    		$('#is-create-order').val(0);
    	}
    };
    var changeCheckbox = document.querySelector('#read-order-button');
    var init = new Switchery(changeCheckbox);
    changeCheckbox.onchange = function() {
        if ($(this).is(':checked')) {
        $('#is-read-order').val(1);
    }else{
    		$('#is-read-order').val(0);
    	}
    };
    var changeCheckbox = document.querySelector('#update-order-button');
    var init = new Switchery(changeCheckbox);
    changeCheckbox.onchange = function() {
        if ($(this).is(':checked')) {
        	$('#is-update-order').val(1);
    	}else{
    		$('#is-update-order').val(0);
    	}
    };
    var changeCheckbox = document.querySelector('#delete-order-button');
    var init = new Switchery(changeCheckbox);
    changeCheckbox.onchange = function() {
         // alert(changeCheckbox.checked);
        if ($(this).is(':checked')) {
        	$('#is-delete-order').val(1);
    	}else{
    		$('#is-delete-order').val(0);
    	}
    };
</script>
<script>
    var changeCheckbox = document.querySelector('#create-category-button');
    var init = new Switchery(changeCheckbox);
    changeCheckbox.onchange = function() {
         // alert(changeCheckbox.checked);
        if ($(this).is(':checked')) {
        	$('#is-create-category').val(1);
    	}else{
    		$('#is-create-category').val(0);
    	}
    };
    var changeCheckbox = document.querySelector('#read-category-button');
    var init = new Switchery(changeCheckbox);
    changeCheckbox.onchange = function() {
         // alert(changeCheckbox.checked);
        if ($(this).is(':checked')) {
        	$('#is-read-category').val(1);
    	}else{
    		$('#is-read-category').val(0);
    	}
    };
    var changeCheckbox = document.querySelector('#update-category-button');
    var init = new Switchery(changeCheckbox);
    changeCheckbox.onchange = function() {
         // alert(changeCheckbox.checked);
        if ($(this).is(':checked')) {
        	$('#is-update-category').val(1);
    	}else{
    		$('#is-update-category').val(0);
    	}
    };
    var changeCheckbox = document.querySelector('#delete-category-button');
    var init = new Switchery(changeCheckbox);
    changeCheckbox.onchange = function() {
         // alert(changeCheckbox.checked);
        if ($(this).is(':checked')) {
        	$('#is-delete-category').val(1);
    	}else{
    		$('#is-delete-category').val(0);
    	}
    };
    // var switchStatus = false;
</script>
<script>
    var changeCheckbox = document.querySelector('#create-subcategory-button');
    var init = new Switchery(changeCheckbox);
    changeCheckbox.onchange = function() {
         // alert(changeCheckbox.checked);
        if ($(this).is(':checked')) {
        	$('#is-create-subcategory').val(1);
    	}else{
    		$('#is-create-subcategory').val(0);
    	}
    };
    var changeCheckbox = document.querySelector('#read-subcategory-button');
    var init = new Switchery(changeCheckbox);
    changeCheckbox.onchange = function() {
         // alert(changeCheckbox.checked);
       if ($(this).is(':checked')) {
        	$('#is-read-subcategory').val(1);
    	}else{
    		$('#is-read-subcategory').val(0);
    	}
    };
    var changeCheckbox = document.querySelector('#update-subcategory-button');
    var init = new Switchery(changeCheckbox);
    changeCheckbox.onchange = function() {
         // alert(changeCheckbox.checked);
        if ($(this).is(':checked')) {
        	$('#is-update-subcategory').val(1);
    	}else{
    		$('#is-update-subcategory').val(0);
    	}
    };
    var changeCheckbox = document.querySelector('#delete-subcategory-button');
    var init = new Switchery(changeCheckbox);
    changeCheckbox.onchange = function() {
         // alert(changeCheckbox.checked);
        if ($(this).is(':checked')) {
        	$('#is-delete-subcategory').val(1);
    	}else{
    		$('#is-delete-subcategory').val(0);
    	}
    };
    // var switchStatus = false;
</script>
<script>
    var changeCheckbox = document.querySelector('#create-product-button');
    var init = new Switchery(changeCheckbox);
    changeCheckbox.onchange = function() {
         // alert(changeCheckbox.checked);
        if ($(this).is(':checked')) {
        	$('#is-create-product').val(1);
    	}else{
    		$('#is-create-product').val(0);
    	}
    };
    var changeCheckbox = document.querySelector('#read-product-button');
    var init = new Switchery(changeCheckbox);
    changeCheckbox.onchange = function() {
         // alert(changeCheckbox.checked);
       if ($(this).is(':checked')) {
        	$('#is-read-product').val(1);
    	}else{
    		$('#is-read-product').val(0);
    	}
    };
    var changeCheckbox = document.querySelector('#update-product-button');
    var init = new Switchery(changeCheckbox);
    changeCheckbox.onchange = function() {
         // alert(changeCheckbox.checked);
        if ($(this).is(':checked')) {
        	$('#is-update-product').val(1);
    	}else{
    		$('#is-update-product').val(0);
    	}
    };
    var changeCheckbox = document.querySelector('#delete-product-button');
    var init = new Switchery(changeCheckbox);
    changeCheckbox.onchange = function() {
         // alert(changeCheckbox.checked);
        if ($(this).is(':checked')) {
        	$('#is-delete-product').val(1);
    	}else{
    		$('#is-delete-product').val(0);
    	}
    };
    // var switchStatus = false;
</script>
<script>

    var changeCheckbox = document.querySelector('#read-products-order-button');
    var init = new Switchery(changeCheckbox);
    changeCheckbox.onchange = function() {
         // alert(changeCheckbox.checked);
       if ($(this).is(':checked')) {
            $('#is-read-products-order').val(1);
        }else{
            $('#is-read-products-order').val(0);
        }
    };
    var changeCheckbox = document.querySelector('#update-products-order-button');
    var init = new Switchery(changeCheckbox);
    changeCheckbox.onchange = function() {
         // alert(changeCheckbox.checked);
        if ($(this).is(':checked')) {
            $('#is-update-products-order').val(1);
        }else{
            $('#is-update-products-order').val(0);
        }
    };

    // var switchStatus = false;
</script>
<script>
    var changeCheckbox = document.querySelector('#create-home-slider-button');
    var init = new Switchery(changeCheckbox);
    changeCheckbox.onchange = function() {
         // alert(changeCheckbox.checked);
        if ($(this).is(':checked')) {
        	$('#is-create-home-slider').val(1);
    	}else{
    		$('#is-create-home-slider').val(0);
    	}
    };
    var changeCheckbox = document.querySelector('#read-home-slider-button');
    var init = new Switchery(changeCheckbox);
    changeCheckbox.onchange = function() {
         // alert(changeCheckbox.checked);
       if ($(this).is(':checked')) {
        	$('#is-read-home-slider').val(1);
    	}else{
    		$('#is-read-home-slider').val(0);
    	}
    };
    var changeCheckbox = document.querySelector('#delete-home-slider-button');
    var init = new Switchery(changeCheckbox);
    changeCheckbox.onchange = function() {
         // alert(changeCheckbox.checked);
        if ($(this).is(':checked')) {
        	$('#is-delete-home-slider').val(1);
    	}else{
    		$('#is-delete-home-slider').val(0);
    	}
    };

    // var switchStatus = false;
</script>
<script>
    var changeCheckbox = document.querySelector('#create-new-offer-button');
    var init = new Switchery(changeCheckbox);
    changeCheckbox.onchange = function() {
         // alert(changeCheckbox.checked);
        if ($(this).is(':checked')) {
            $('#is-create-new-offer').val(1);
        }else{
            $('#is-create-new-offer').val(0);
        }
    };
    var changeCheckbox = document.querySelector('#read-new-offer-button');
    var init = new Switchery(changeCheckbox);
    changeCheckbox.onchange = function() {
         // alert(changeCheckbox.checked);
       if ($(this).is(':checked')) {
            $('#is-read-new-offer').val(1);
        }else{
            $('#is-read-new-offer').val(0);
        }
    };
    var changeCheckbox = document.querySelector('#delete-new-offer-button');
    var init = new Switchery(changeCheckbox);
    changeCheckbox.onchange = function() {
         // alert(changeCheckbox.checked);
        if ($(this).is(':checked')) {
            $('#is-delete-new-offer').val(1);
        }else{
            $('#is-delete-new-offer').val(0);
        }
    };

    // var switchStatus = false;
</script>
<script>
    var changeCheckbox = document.querySelector('#create-promo-button');
    var init = new Switchery(changeCheckbox);
    changeCheckbox.onchange = function() {
         // alert(changeCheckbox.checked);
        if ($(this).is(':checked')) {
            $('#is-create-promo').val(1);
        }else{
            $('#is-create-promo').val(0);
        }
    };
    var changeCheckbox = document.querySelector('#read-promo-button');
    var init = new Switchery(changeCheckbox);
    changeCheckbox.onchange = function() {
         // alert(changeCheckbox.checked);
       if ($(this).is(':checked')) {
            $('#is-read-promo').val(1);
        }else{
            $('#is-read-promo').val(0);
        }
    };
    var changeCheckbox = document.querySelector('#update-promo-button');
    var init = new Switchery(changeCheckbox);
    changeCheckbox.onchange = function() {
         // alert(changeCheckbox.checked);
        if ($(this).is(':checked')) {
            $('#is-update-promo').val(1);
        }else{
            $('#is-update-promo').val(0);
        }
    };
    var changeCheckbox = document.querySelector('#delete-promo-button');
    var init = new Switchery(changeCheckbox);
    changeCheckbox.onchange = function() {
         // alert(changeCheckbox.checked);
        if ($(this).is(':checked')) {
            $('#is-delete-promo').val(1);
        }else{
            $('#is-delete-promo').val(0);
        }
    };
    // var switchStatus = false;
</script>
<script>
    var changeCheckbox = document.querySelector('#create-featured-button');
    var init = new Switchery(changeCheckbox);
    changeCheckbox.onchange = function() {
         // alert(changeCheckbox.checked);
        if ($(this).is(':checked')) {
            $('#is-create-featured').val(1);
        }else{
            $('#is-create-featured').val(0);
        }
    };
    var changeCheckbox = document.querySelector('#read-featured-button');
    var init = new Switchery(changeCheckbox);
    changeCheckbox.onchange = function() {
         // alert(changeCheckbox.checked);
       if ($(this).is(':checked')) {
            $('#is-read-featured').val(1);
        }else{
            $('#is-read-featured').val(0);
        }
    };
    var changeCheckbox = document.querySelector('#update-featured-button');
    var init = new Switchery(changeCheckbox);
    changeCheckbox.onchange = function() {
         // alert(changeCheckbox.checked);
        if ($(this).is(':checked')) {
            $('#is-update-featured').val(1);
        }else{
            $('#is-update-featured').val(0);
        }
    };
    var changeCheckbox = document.querySelector('#delete-featured-button');
    var init = new Switchery(changeCheckbox);
    changeCheckbox.onchange = function() {
         // alert(changeCheckbox.checked);
        if ($(this).is(':checked')) {
            $('#is-delete-featured').val(1);
        }else{
            $('#is-delete-featured').val(0);
        }
    };
    // var switchStatus = false;
</script>
<script>
    var changeCheckbox = document.querySelector('#read-customers-button');
    var init = new Switchery(changeCheckbox);
    changeCheckbox.onchange = function() {
         // alert(changeCheckbox.checked);
       if ($(this).is(':checked')) {
            $('#is-read-customers').val(1);
        }else{
            $('#is-read-customers').val(0);
        }
    };
    // var switchStatus = false;
</script>
<script>
    var changeCheckbox = document.querySelector('#read-payment-button');
    var init = new Switchery(changeCheckbox);
    changeCheckbox.onchange = function() {
         // alert(changeCheckbox.checked);
       if ($(this).is(':checked')) {
            $('#is-read-payment').val(1);
        }else{
            $('#is-read-payment').val(0);
        }
    };
    var changeCheckbox = document.querySelector('#update-payment-button');
    var init = new Switchery(changeCheckbox);
    changeCheckbox.onchange = function() {
         // alert(changeCheckbox.checked);
       if ($(this).is(':checked')) {
            $('#is-update-payment').val(1);
        }else{
            $('#is-update-payment').val(0);
        }
    };
    // var switchStatus = false;
</script>
<script>
    var changeCheckbox = document.querySelector('#create-delivery-button');
    var init = new Switchery(changeCheckbox);
    changeCheckbox.onchange = function() {
         // alert(changeCheckbox.checked);
        if ($(this).is(':checked')) {
            $('#is-create-delivery').val(1);
        }else{
            $('#is-create-delivery').val(0);
        }
    };
    var changeCheckbox = document.querySelector('#read-delivery-button');
    var init = new Switchery(changeCheckbox);
    changeCheckbox.onchange = function() {
         // alert(changeCheckbox.checked);
       if ($(this).is(':checked')) {
            $('#is-read-delivery').val(1);
        }else{
            $('#is-read-delivery').val(0);
        }
    };
    var changeCheckbox = document.querySelector('#update-delivery-button');
    var init = new Switchery(changeCheckbox);
    changeCheckbox.onchange = function() {
         // alert(changeCheckbox.checked);
        if ($(this).is(':checked')) {
            $('#is-update-delivery').val(1);
        }else{
            $('#is-update-delivery').val(0);
        }
    };
    var changeCheckbox = document.querySelector('#delete-delivery-button');
    var init = new Switchery(changeCheckbox);
    changeCheckbox.onchange = function() {
         // alert(changeCheckbox.checked);
        if ($(this).is(':checked')) {
            $('#is-delete-delivery').val(1);
        }else{
            $('#is-delete-delivery').val(0);
        }
    };
    // var switchStatus = false;
</script>
<script>

    var changeCheckbox = document.querySelector('#read-return-button');
    var init = new Switchery(changeCheckbox);
    changeCheckbox.onchange = function() {
         // alert(changeCheckbox.checked);
       if ($(this).is(':checked')) {
            $('#is-read-return').val(1);
        }else{
            $('#is-read-return').val(0);
        }
    };
    var changeCheckbox = document.querySelector('#update-return-button');
    var init = new Switchery(changeCheckbox);
    changeCheckbox.onchange = function() {
         // alert(changeCheckbox.checked);
        if ($(this).is(':checked')) {
            $('#is-update-return').val(1);
        }else{
            $('#is-update-return').val(0);
        }
    };
    var changeCheckbox = document.querySelector('#delete-return-button');
    var init = new Switchery(changeCheckbox);
    changeCheckbox.onchange = function() {
         // alert(changeCheckbox.checked);
        if ($(this).is(':checked')) {
            $('#is-delete-return').val(1);
        }else{
            $('#is-delete-return').val(0);
        }
    };
    // var switchStatus = false;
</script>
<script>
    var changeCheckbox = document.querySelector('#create-notification-button');
    var init = new Switchery(changeCheckbox);
    changeCheckbox.onchange = function() {
         // alert(changeCheckbox.checked);
       if ($(this).is(':checked')) {
            $('#is-create-notification').val(1);
        }else{
            $('#is-create-notification').val(0);
        }
    };
    var changeCheckbox = document.querySelector('#read-notification-button');
    var init = new Switchery(changeCheckbox);
    changeCheckbox.onchange = function() {
         // alert(changeCheckbox.checked);
       if ($(this).is(':checked')) {
            $('#is-read-notification').val(1);
        }else{
            $('#is-read-notification').val(0);
        }
    };
    var changeCheckbox = document.querySelector('#delete-notification-button');
    var init = new Switchery(changeCheckbox);
    changeCheckbox.onchange = function() {
         // alert(changeCheckbox.checked);
       if ($(this).is(':checked')) {
            $('#is-delete-notification').val(1);
        }else{
            $('#is-delete-notification').val(0);
        }
    };
    // var switchStatus = false;
</script>
<script>
    var changeCheckbox = document.querySelector('#read-transaction-button');
    var init = new Switchery(changeCheckbox);
    changeCheckbox.onchange = function() {
         // alert(changeCheckbox.checked);
       if ($(this).is(':checked')) {
            $('#is-read-fund').val(1);
        }else{
            $('#is-read-fund').val(0);
        }
    };
    // var switchStatus = false;
</script>
<script>
    var changeCheckbox = document.querySelector('#read-fund-button');
    var init = new Switchery(changeCheckbox);
    changeCheckbox.onchange = function() {
         // alert(changeCheckbox.checked);
       if ($(this).is(':checked')) {
            $('#is-read-transaction').val(1);
        }else{
            $('#is-read-transaction').val(0);
        }
    };

    // var switchStatus = false;
</script>
<script>
  var changeCheckbox = document.querySelector('#read-settings-button');
    var init = new Switchery(changeCheckbox);
    changeCheckbox.onchange = function() {
         // alert(changeCheckbox.checked);
       if ($(this).is(':checked')) {
            $('#is-read-settings').val(1);
        }else{
            $('#is-read-settings').val(0);
        }
    };
    var changeCheckbox = document.querySelector('#update-settings-button');
    var init = new Switchery(changeCheckbox);
    changeCheckbox.onchange = function() {
         // alert(changeCheckbox.checked);
       if ($(this).is(':checked')) {
            $('#is-update-settings').val(1);
        }else{
            $('#is-update-settings').val(0);
        }
    };

    // var switchStatus = false;
</script>
<script>
    var changeCheckbox = document.querySelector('#create-location-button');
    var init = new Switchery(changeCheckbox);
    changeCheckbox.onchange = function() {
         // alert(changeCheckbox.checked);
        if ($(this).is(':checked')) {
            $('#is-create-location').val(1);
        }else{
            $('#is-create-location').val(0);
        }
    };
    var changeCheckbox = document.querySelector('#read-location-button');
    var init = new Switchery(changeCheckbox);
    changeCheckbox.onchange = function() {
         // alert(changeCheckbox.checked);
       if ($(this).is(':checked')) {
            $('#is-read-location').val(1);
        }else{
            $('#is-read-location').val(0);
        }
    };
    var changeCheckbox = document.querySelector('#update-location-button');
    var init = new Switchery(changeCheckbox);
    changeCheckbox.onchange = function() {
         // alert(changeCheckbox.checked);
        if ($(this).is(':checked')) {
            $('#is-update-location').val(1);
        }else{
            $('#is-update-location').val(0);
        }
    };
    var changeCheckbox = document.querySelector('#delete-location-button');
    var init = new Switchery(changeCheckbox);
    changeCheckbox.onchange = function() {
         // alert(changeCheckbox.checked);
        if ($(this).is(':checked')) {
            $('#is-delete-location').val(1);
        }else{
            $('#is-delete-location').val(0);
        }
    };
    // var switchStatus = false;
</script>
<script>
    var changeCheckbox = document.querySelector('#create-report-button');
    var init = new Switchery(changeCheckbox);
    changeCheckbox.onchange = function() {
         // alert(changeCheckbox.checked);
       if ($(this).is(':checked')) {
            $('#is-create-report').val(1);
        }else{
            $('#is-create-report').val(0);
        }
    };
    var changeCheckbox = document.querySelector('#read-report-button');
    var init = new Switchery(changeCheckbox);
    changeCheckbox.onchange = function() {
         // alert(changeCheckbox.checked);
       if ($(this).is(':checked')) {
            $('#is-read-report').val(1);
        }else{
            $('#is-read-report').val(0);
        }
    };

    // var switchStatus = false;
</script>
<script>
    var changeCheckbox = document.querySelector('#create-faq-button');
    var init = new Switchery(changeCheckbox);
    changeCheckbox.onchange = function() {
         // alert(changeCheckbox.checked);
       if ($(this).is(':checked')) {
            $('#is-create-faq').val(1);
        }else{
            $('#is-create-faq').val(0);
        }
    };
    var changeCheckbox = document.querySelector('#read-faq-button');
    var init = new Switchery(changeCheckbox);
    changeCheckbox.onchange = function() {
         // alert(changeCheckbox.checked);
       if ($(this).is(':checked')) {
            $('#is-read-faq').val(1);
        }else{
            $('#is-read-faq').val(0);
        }
    };
    var changeCheckbox = document.querySelector('#update-faq-button');
    var init = new Switchery(changeCheckbox);
    changeCheckbox.onchange = function() {
         // alert(changeCheckbox.checked);
       if ($(this).is(':checked')) {
            $('#is-update-faq').val(1);
        }else{
            $('#is-update-faq').val(0);
        }
    };
    var changeCheckbox = document.querySelector('#delete-faq-button');
    var init = new Switchery(changeCheckbox);
    changeCheckbox.onchange = function() {
         // alert(changeCheckbox.checked);
       if ($(this).is(':checked')) {
            $('#is-delete-faq').val(1);
        }else{
            $('#is-delete-faq').val(0);
        }
    };
    // var switchStatus = false;
</script>
<script>
  // var changeCheckbox = document.querySelector('#permission-create-order-button');
  // var init = new Switchery(changeCheckbox);
  // $('.switchery').trigger('click');
  // $('#permission-create-order-button').attr('checked', true);
</script>
<!-- removed  code goes here -->

<script>
window.actionEvents = {
    'click .edit-system-user': function (e, value, row, index) {
      permissions = row.permissions;
      permissions = JSON.parse(permissions);
      // console.log(permissions);
      $("#update_form").trigger( "reset" );


      $('#system_user_id').val(row.id);

      if(permissions.orders.create==1){
        // $('#permission-create-order-button').attr('checked', true);
        $('#permission-create-order-button').prop('checked', true);
        $('#permission-is-create-order').val(1);
      }else{
        $('#permission-create-order-button').attr('checked', false);
        $('#permission-is-create-order').val(0);
      }
      if(permissions.orders.read==1){
        $('#permission-read-order-button').attr('checked', true);
        $('#permission-is-read-order').val(1);
      }else{
        $('#permission-read-order-button').attr('checked', false);
        $('#permission-is-read-order').val(0);
      }
      if(permissions.orders.update==1){
        $('#permission-update-order-button').attr('checked', true);
        $('#permission-is-update-order').val(1);
      }else{
        $('#permission-update-order-button').attr('checked', false);
        $('#permission-is-update-order').val(0);
      }
      if(permissions.orders.delete==1){
        $('#permission-delete-order-button').attr('checked', true);
        $('#permission-is-delete-order').val(1);
      }else{
        $('#permission-delete-order-button').attr('checked', false);
        $('#permission-is-delete-order').val(0);
      }

      if(permissions.categories.create==1){
        $('#permission-create-category-button').attr('checked', true);
        $('#permission-is-create-category').val(1);
      }else{
        $('#permission-create-category-button').attr('checked', false);
        $('#permission-is-create-category').val(0);
      }
      if(permissions.categories.read==1){
        $('#permission-read-category-button').attr('checked', true);
        $('#permission-is-read-category').val(1);
      }else{
        $('#permission-read-category-button').attr('checked', false);
        $('#permission-is-read-category').val(0);
      }
      if(permissions.categories.update==1){
        $('#permission-update-category-button').attr('checked', true);
        $('#permission-is-update-category').val(1);
      }else{
        $('#permission-update-category-button').attr('checked', false);
        $('#permission-is-update-category').val(0);
      }
      if(permissions.categories.delete==1){
        $('#permission-delete-category-button').attr('checked', true);
        $('#permission-is-delete-category').val(1);
      }else{
        $('#permission-delete-category-button').attr('checked', false);
        $('#permission-is-delete-category').val(0);
      }


      if(permissions.subcategories.create==1){
        $('#permission-create-subcategory-button').attr('checked', true);
        $('#permission-is-create-subcategory').val(1);
      }else{
        $('#permission-create-subcategory-button').attr('checked', false);
        $('#permission-is-create-subcategory').val(0);
      }
      if(permissions.subcategories.read==1){
        $('#permission-read-subcategory-button').attr('checked', true);
        $('#permission-is-read-subcategory').val(1);
      }else{
        $('#permission-read-subcategory-button').attr('checked', false);
        $('#permission-is-read-subcategory').val(0);
      }
      if(permissions.subcategories.update==1){
        $('#permission-update-subcategory-button').attr('checked', true);
        $('#permission-is-update-subcategory').val(1);
      }else{
        $('#permission-update-subcategory-button').attr('checked', false);
        $('#permission-is-update-subcategory').val(0);
      }
      if(permissions.subcategories.delete==1){
        $('#permission-delete-subcategory-button').attr('checked', true);
        $('#permission-is-delete-subcategory').val(1);
      }else{
        $('#permission-delete-subcategory-button').attr('checked', false);
        $('#permission-is-delete-subcategory').val(0);
      }


      if(permissions.products.create==1){
        $('#permission-create-product-button').attr('checked', true);
        $('#permission-is-create-product').val(1);
      }else{
        $('#permission-create-product-button').attr('checked', false);
        $('#permission-is-create-product').val(0);
      }
      if(permissions.products.read==1){
        $('#permission-read-product-button').attr('checked', true);
        $('#permission-is-read-product').val(1);
      }else{
         $('#permission-read-product-button').attr('checked', false);
        $('#permission-is-read-product').val(0);
      }
      if(permissions.products.update==1){
        $('#permission-update-product-button').attr('checked', true);
        $('#permission-is-update-product').val(1);
      }else{
        $('#permission-update-product-button').attr('checked', false);
        $('#permission-is-update-product').val(0);
      }
      if(permissions.products.delete==1){
        $('#permission-delete-product-button').attr('checked', true);
        $('#permission-is-delete-product').val(1);
      }else{
        $('#permission-delete-product-button').attr('checked', false);
        $('#permission-is-delete-product').val(0);
      }


      if(permissions.products_order.read==1){
        $('#permission-read-products-order-button').attr('checked', true);
        $('#permission-is-read-products-order').val(1);
      }else{
        $('#permission-read-products-order-button').attr('checked', false);
        $('#permission-is-read-products-order').val(0);
      }
      if(permissions.products_order.update==1){
        $('#permission-update-products-order-button').attr('checked', true);
        $('#permission-is-update-products-order').val(1);
      }else{
        $('#permission-update-products-order-button').attr('checked', false);
        $('#permission-is-update-products-order').val(0);
      }


      if(permissions.home_sliders.create==1){
        $('#permission-create-home-slider-button').attr('checked', true);
        $('#permission-is-create-home-slider').val(1);
      }else{
        $('#permission-create-home-slider-button').attr('checked', false);
        $('#permission-is-create-home-slider').val(0);
      }
      if(permissions.home_sliders.read==1){
        $('#permission-read-home-slider-button').attr('checked', true);
        $('#permission-is-read-home-slider').val(1);
      }else{
        $('#permission-read-home-slider-button').attr('checked', false);
        $('#permission-is-read-home-slider').val(0);
      }
      if(permissions.home_sliders.delete==1){
        $('#permission-delete-home-slider-button').attr('checked', true);
        $('#permission-is-delete-home-slider').val(1);
      }else{
        $('#permission-delete-home-slider-button').attr('checked', false);
        $('#permission-is-delete-home-slider').val(0);
      }


      if(permissions.new_offers.create==1){
        $('#permission-create-new-offer-button').attr('checked', true);
        $('#permission-is-create-new-offer').val(1);
      }else{
        $('#permission-create-new-offer-button').attr('checked', false);
        $('#permission-is-create-new-offer').val(0);
      }
      if(permissions.new_offers.read==1){
        $('#permission-read-new-offer-button').attr('checked', true);
        $('#permission-is-read-new-offer').val(1);
      }else{
        $('#permission-read-new-offer-button').attr('checked', false);
        $('#permission-is-read-new-offer').val(0);
      }
      if(permissions.new_offers.delete==1){
        $('#permission-delete-new-offer-button').attr('checked', true);
        $('#permission-is-delete-new-offer').val(1);
      }else{
        $('#permission-delete-new-offer-button').attr('checked', false);
        $('#permission-is-delete-new-offer').val(0);
      }

      if(permissions.promo_codes.create==1){
        $('#permission-create-promo-button').attr('checked', true);
        $('#permission-is-create-promo').val(1);
      }else{
        $('#permission-create-promo-button').attr('checked', false);
        $('#permission-is-create-promo').val(0);
      }
      if(permissions.promo_codes.read==1){
        $('#permission-read-promo-button').attr('checked', true);
        $('#permission-is-read-promo').val(1);
      }else{
        $('#permission-read-promo-button').attr('checked', false);
        $('#permission-is-read-promo').val(0);
      }
      if(permissions.promo_codes.update==1){
        $('#permission-update-promo-button').attr('checked', true);
        $('#permission-is-update-promo').val(1);
      }else{
        $('#permission-update-promo-button').attr('checked', false);
        $('#permission-is-update-promo').val(0);
      }
      if(permissions.promo_codes.delete==1){
        $('#permission-delete-promo-button').attr('checked', true);
        $('#permission-is-delete-promo').val(1);
      }else{
        $('#permission-delete-promo-button').attr('checked', false);
        $('#permission-is-delete-promo').val(0);
      }

      if(permissions.featured.create==1){
        $('#permission-create-featured-button').attr('checked', true);
        $('#permission-is-create-featured').val(1);
      }else{
        $('#permission-create-featured-button').attr('checked', false);
        $('#permission-is-create-featured').val(0);
      }
      if(permissions.featured.read==1){
        $('#permission-read-featured-button').attr('checked', true);
        $('#permission-is-read-featured').val(1);
      }else{
        $('#permission-read-featured-button').attr('checked', false);
        $('#permission-is-read-featured').val(0);
      }
      if(permissions.featured.update==1){
        $('#permission-update-featured-button').attr('checked', true);
        $('#permission-is-update-featured').val(1);
      }else{
        $('#permission-update-featured-button').attr('checked', false);
        $('#permission-is-update-featured').val(0);
      }
      if(permissions.featured.delete==1){
        $('#permission-delete-featured-button').attr('checked', true);
        $('#permission-is-delete-featured').val(1);
      }else{
        $('#permission-delete-featured-button').attr('checked', false);
        $('#permission-is-delete-featured').val(0);
      }


      if(permissions.customers.read==1){
        $('#permission-read-customers-button').attr('checked', true);
        $('#permission-is-read-customers').val(1);
      }else{
        $('#permission-read-customers-button').attr('checked', false);
        $('#permission-is-read-customers').val(0);
      }

      if(permissions.payment.read==1){
        $('#permission-read-payment-button').attr('checked', true);
        $('#permission-is-read-payment').val(1);
      }else{
        $('#permission-read-payment-button').attr('checked', false);
        $('#permission-is-read-payment').val(0);
      }
      if(permissions.payment.update==1){
        $('#permission-update-payment-button').attr('checked', true);
        $('#permission-is-update-payment').val(1);
      }else{
        $('#permission-update-payment-button').attr('checked', false);
        $('#permission-is-update-payment').val(0);
      }


      if(permissions.delivery_boys.create==1){
        $('#permission-create-delivery-button').attr('checked', true);
        $('#permission-is-create-delivery').val(1);
      }else{
        $('#permission-create-delivery-button').attr('checked', false);
        $('#permission-is-create-delivery').val(0);
      }
      if(permissions.delivery_boys.read==1){
        $('#permission-read-delivery-button').attr('checked', true);
        $('#permission-is-read-delivery').val(1);
      }else{
        $('#permission-read-delivery-button').attr('checked', false);
        $('#permission-is-read-delivery').val(0);
      }
      if(permissions.delivery_boys.update==1){
        $('#permission-update-delivery-button').attr('checked', true);
        $('#permission-is-update-delivery').val(1);
      }else{
        $('#permission-update-delivery-button').attr('checked', false);
        $('#permission-is-update-delivery').val(0);
      }
      if(permissions.delivery_boys.delete==1){
        $('#permission-delete-delivery-button').attr('checked', true);
        $('#permission-is-delete-delivery').val(1);
      }else{
        $('#permission-delete-delivery-button').attr('checked', false);
        $('#permission-is-delete-delivery').val(0);
      }


      if(permissions.return_requests.read==1){
        $('#permission-read-return-button').attr('checked', true);
        $('#permission-is-read-return').val(1);
      }else{
        $('#permission-read-return-button').attr('checked', false);
        $('#permission-is-read-return').val(0);
      }
      if(permissions.return_requests.update==1){
        $('#permission-update-return-button').attr('checked', true);
        $('#permission-is-update-return').val(1);
      }else{
        $('#permission-update-return-button').attr('checked', false);
        $('#permission-is-update-return').val(0);
      }
      if(permissions.return_requests.delete==1){
        $('#permission-delete-return-button').attr('checked', true);
        $('#permission-is-delete-return').val(1);
      }else{
        $('#permission-delete-return-button').attr('checked', false);
        $('#permission-is-delete-return').val(0);
      }

      if(permissions.notifications.create==1){
        $('#permission-create-notification-button').attr('checked', true);
        $('#permission-is-create-notification').val(1);
      }else{
        $('#permission-create-notification-button').attr('checked', false);
        $('#permission-is-create-notification').val(0);
      }
      if(permissions.notifications.read==1){
        $('#permission-read-notification-button').attr('checked', true);
        $('#permission-is-read-notification').val(1);
      }else{
        $('#permission-read-notification-button').attr('checked', false);
        $('#permission-is-read-notification').val(0);
      }
      if(permissions.notifications.delete==1){
        $('#permission-delete-notification-button').attr('checked', true);
        $('#permission-is-delete-notification').val(1);
      }else{
        $('#permission-delete-notification-button').attr('checked', false);
        $('#permission-is-delete-notification').val(0);
      }

      if(permissions.transactions.read==1){
        $('#permission-read-transaction-button').attr('checked', true);
        $('#permission-is-read-transaction').val(1);
      }else{
        $('#permission-read-transaction-button').attr('checked', false);
        $('#permission-is-read-transaction').val(0);
      }
      if(permissions.transactions.read==1){
        $('#permission-read-fund-button').attr('checked', true);
        $('#permission-is-read-transaction').val(1);
      }else{
        $('#permission-read-fund-button').attr('checked', false);
        $('#permission-is-read-transaction').val(0);
      }


      if(permissions.settings.read==1){
        $('#permission-read-settings-button').attr('checked', true);
        $('#permission-is-read-settings').val(1);
      }else{
        $('#permission-read-settings-button').attr('checked', false);
        $('#permission-is-read-settings').val(0);
      }

      if(permissions.settings.update==1){
        $('#permission-update-settings-button').attr('checked', true);
        $('#permission-is-update-settings').val(1);
      }else{
        $('#permission-update-settings-button').attr('checked', false);
        $('#permission-is-update-settings').val(0);
      }

      if(permissions.locations.create==1){
        $('#permission-create-location-button').attr('checked', true);
        $('#permission-is-create-location').val(1);
      }else{
        $('#permission-create-location-button').attr('checked', false);
        $('#permission-is-create-location').val(0);
      }

      if(permissions.locations.read==1){
        $('#permission-read-location-button').attr('checked', true);
        $('#permission-is-read-location').val(1);
      }else{
        $('#permission-read-location-button').attr('checked', false);
        $('#permission-is-read-location').val(0);
      }
      if(permissions.locations.update==1){
        $('#permission-update-location-button').attr('checked', true);
        $('#permission-is-update-location').val(1);
      }else{
        $('#permission-update-location-button').attr('checked', false);
        $('#permission-is-update-location').val(0);
      }

      if(permissions.locations.delete==1){
        $('#permission-delete-location-button').attr('checked', true);
        $('#permission-is-delete-location').val(1);
      }else{
        $('#permission-delete-location-button').attr('checked', false);
        $('#permission-is-delete-location').val(0);
      }

      if(permissions.reports.create==1){
        $('#permission-create-report-button').attr('checked', true);
        $('#permission-is-create-report').val(1);
      }else{
        $('#permission-create-report-button').attr('checked', false);
        $('#permission-is-create-report').val(0);
      }

      if(permissions.reports.read==1){
        $('#permission-read-report-button').attr('checked', true);
        $('#permission-is-read-report').val(1);
      }else{
        $('#permission-read-report-button').attr('checked', false);
        $('#permission-is-read-report').val(0);
      }


      if(permissions.faqs.create==1){
        
        $('#permission-create-faq-button').attr('checked', true);
        $('#permission-is-create-faq').val(1);
      }else{
        $('#permission-create-faq-button').attr('checked', false);
        $('#permission-is-create-faq').val(0);
      }

      if(permissions.faqs.read==1){
        $('#permission-read-faq-button').attr('checked', true);
        $('#permission-is-read-faq').val(1);
      }else{
        $('#permission-read-faq-button').attr('checked', false);
        $('#permission-is-read-faq').val(0);
      }

      if(permissions.faqs.update==1){
        $('#permission-update-faq-button').attr('checked', true);
        $('#permission-is-update-faq').val(1);
      }else{
        $('#permission-update-faq-button').attr('checked', false);
        $('#permission-is-update-faq').val(0);
      }

      if(permissions.faqs.delete==1){
        $('#permission-delete-faq-button').attr('checked', true);
        $('#permission-is-delete-faq').val(1);
      }else{
        $('#permission-delete-faq-button').attr('checked', false);
        $('#permission-is-delete-faq').val(0);
      }
    }
}
    //   var changeCheckbox = document.querySelector('#permission-create-order-button');
    // var init = new Switchery(changeCheckbox);
    // changeCheckbox.onchange = function() {
    //     if ($(this).is(':checked')) {
    //         $('#permission-is-create-order').val(1);
    //     }else{
    //         $('#permission-is-create-order').val(0);
    //     }
    // };
      $('#permission-create-order-button').change(function () {
        if ($(this).is(':checked')) {
              $('#permission-is-create-order').val(1);
          }else{
              $('#permission-is-create-order').val(0);
          }
      });
      $('#permission-read-order-button').change(function () {
        if ($(this).is(':checked')) {
              $('#permission-is-read-order').val(1);
          }else{
              $('#permission-is-read-order').val(0);
          }
      });
      $('#permission-update-order-button').change(function () {
        if ($(this).is(':checked')) {
              $('#permission-is-update-order').val(1);
          }else{
              $('#permission-is-update-order').val(0);
          }
      });
      $('#permission-delete-order-button').change(function () {
        if ($(this).is(':checked')) {
              $('#permission-is-delete-order').val(1);
          }else{
              $('#permission-is-delete-order').val(0);
          }
      });

      $('#permission-create-category-button').change(function () {
        if ($(this).is(':checked')) {
              $('#permission-is-create-category').val(1);
          }else{
              $('#permission-is-create-category').val(0);
          }
      });
      $('#permission-read-category-button').change(function () {
        if ($(this).is(':checked')) {
              $('#permission-is-read-category').val(1);
          }else{
              $('#permission-is-read-category').val(0);
          }
      });
      $('#permission-update-category-button').change(function () {
        if ($(this).is(':checked')) {
              $('#permission-is-update-category').val(1);
          }else{
              $('#permission-is-update-category').val(0);
          }
      });
      $('#permission-delete-category-button').change(function () {
        if ($(this).is(':checked')) {
              $('#permission-is-delete-category').val(1);
          }else{
              $('#permission-is-delete-category').val(0);
          }
      });

      $('#permission-create-subcategory-button').change(function () {
        if ($(this).is(':checked')) {
              $('#permission-is-create-subcategory').val(1);
          }else{
              $('#permission-is-create-subcategory').val(0);
          }
      });
      $('#permission-read-subcategory-button').change(function () {
        if ($(this).is(':checked')) {
              $('#permission-is-read-subcategory').val(1);
          }else{
              $('#permission-is-read-subcategory').val(0);
          }
      });
      $('#permission-update-subcategory-button').change(function () {
        if ($(this).is(':checked')) {
              $('#permission-is-update-subcategory').val(1);
          }else{
              $('#permission-is-update-subcategory').val(0);
          }
      });
      $('#permission-delete-subcategory-button').change(function () {
        if ($(this).is(':checked')) {
              $('#permission-is-delete-subcategory').val(1);
          }else{
              $('#permission-is-delete-subcategory').val(0);
          }
      });


      $('#permission-create-product-button').change(function () {
        if ($(this).is(':checked')) {
              $('#permission-is-create-product').val(1);
          }else{
              $('#permission-is-create-product').val(0);
          }
      });
      $('#permission-read-product-button').change(function () {
        if ($(this).is(':checked')) {
              $('#permission-is-read-product').val(1);
          }else{
              $('#permission-is-read-product').val(0);
          }
      });
      $('#permission-update-product-button').change(function () {
        if ($(this).is(':checked')) {
              $('#permission-is-update-product').val(1);
          }else{
              $('#permission-is-update-product').val(0);
          }
      });
      $('#permission-delete-product-button').change(function () {
        if ($(this).is(':checked')) {
              $('#permission-is-delete-product').val(1);
          }else{
              $('#permission-is-delete-product').val(0);
          }
      });


      $('#permission-read-products-order-button').change(function () {
        if ($(this).is(':checked')) {
              $('#permission-is-read-products-order').val(1);
          }else{
              $('#permission-is-read-products-order').val(0);
          }
      });
      $('#permission-update-products-order-button').change(function () {
        if ($(this).is(':checked')) {
              $('#permission-is-update-products-order').val(1);
          }else{
              $('#permission-is-update-products-order').val(0);
          }
      });


      $('#permission-create-home-slider-button').change(function () {
        if ($(this).is(':checked')) {
              $('#permission-is-create-home-slider').val(1);
          }else{
              $('#permission-is-create-home-slider').val(0);
          }
      });
      $('#permission-read-home-slider-button').change(function () {
        if ($(this).is(':checked')) {
              $('#permission-is-read-home-slider').val(1);
          }else{
              $('#permission-is-read-home-slider').val(0);
          }
      });
      $('#permission-update-home-slider-button').change(function () {
        if ($(this).is(':checked')) {
              $('#permission-is-update-home-slider').val(1);
          }else{
              $('#permission-is-update-home-slider').val(0);
          }
      });
      $('#permission-delete-home-slider-button').change(function () {
        if ($(this).is(':checked')) {
              $('#permission-is-delete-home-slider').val(1);
          }else{
              $('#permission-is-delete-home-slider').val(0);
          }
      });


      $('#permission-create-new-offer-button').change(function () {
        if ($(this).is(':checked')) {
              $('#permission-is-create-new-offer').val(1);
          }else{
              $('#permission-is-create-new-offer').val(0);
          }
      });
      $('#permission-read-new-offer-button').change(function () {
        if ($(this).is(':checked')) {
              $('#permission-is-read-new-offer').val(1);
          }else{
              $('#permission-is-read-new-offer').val(0);
          }
      });
      $('#permission-update-new-offer-button').change(function () {
        if ($(this).is(':checked')) {
              $('#permission-is-update-new-offer').val(1);
          }else{
              $('#permission-is-update-new-offer').val(0);
          }
      });
      $('#permission-delete-new-offer-button').change(function () {
        if ($(this).is(':checked')) {
              $('#permission-is-delete-new-offer').val(1);
          }else{
              $('#permission-is-delete-new-offer').val(0);
          }
      });

      $('#permission-create-promo-button').change(function () {
        if ($(this).is(':checked')) {
              $('#permission-is-create-promo').val(1);
          }else{
              $('#permission-is-create-promo').val(0);
          }
      });
      $('#permission-read-promo-button').change(function () {
        if ($(this).is(':checked')) {
              $('#permission-is-read-promo').val(1);
          }else{
              $('#permission-is-read-promo').val(0);
          }
      });
      $('#permission-update-promo-button').change(function () {
        if ($(this).is(':checked')) {
              $('#permission-is-update-promo').val(1);
          }else{
              $('#permission-is-update-promo').val(0);
          }
      });
      $('#permission-delete-promo-button').change(function () {
        if ($(this).is(':checked')) {
              $('#permission-is-delete-promo').val(1);
          }else{
              $('#permission-is-delete-promo').val(0);
          }
      });


      $('#permission-create-featured-button').change(function () {
        if ($(this).is(':checked')) {
              $('#permission-is-create-featured').val(1);
          }else{
              $('#permission-is-create-featured').val(0);
          }
      });
      $('#permission-read-featured-button').change(function () {
        if ($(this).is(':checked')) {
              $('#permission-is-read-featured').val(1);
          }else{
              $('#permission-is-read-featured').val(0);
          }
      });
      $('#permission-update-featured-button').change(function () {
        if ($(this).is(':checked')) {
              $('#permission-is-update-featured').val(1);
          }else{
              $('#permission-is-update-featured').val(0);
          }
      });
      $('#permission-delete-featured-button').change(function () {
        if ($(this).is(':checked')) {
              $('#permission-is-delete-featured').val(1);
          }else{
              $('#permission-is-delete-featured').val(0);
          }
      });

      $('#permission-read-customers-button').change(function () {
        if ($(this).is(':checked')) {
              $('#permission-is-read-customers').val(1);
          }else{
              $('#permission-is-read-customers').val(0);
          }
      });

      $('#permission-read-payment-button').change(function () {
        if ($(this).is(':checked')) {
              $('#permission-is-read-payment').val(1);
          }else{
              $('#permission-is-read-payment').val(0);
          }
      });

      $('#permission-update-payment-button').change(function () {
        if ($(this).is(':checked')) {
              $('#permission-is-update-payment').val(1);
          }else{
              $('#permission-is-update-payment').val(0);
          }
      });


       $('#permission-create-delivery-button').change(function () {
        if ($(this).is(':checked')) {
              $('#permission-is-create-delivery').val(1);
          }else{
              $('#permission-is-create-delivery').val(0);
          }
      });
      $('#permission-read-delivery-button').change(function () {
        if ($(this).is(':checked')) {
              $('#permission-is-read-delivery').val(1);
          }else{
              $('#permission-is-read-delivery').val(0);
          }
      });
      $('#permission-update-delivery-button').change(function () {
        if ($(this).is(':checked')) {
              $('#permission-is-update-delivery').val(1);
          }else{
              $('#permission-is-update-delivery').val(0);
          }
      });
      $('#permission-delete-delivery-button').change(function () {
        if ($(this).is(':checked')) {
              $('#permission-is-delete-delivery').val(1);
          }else{
              $('#permission-is-delete-delivery').val(0);
          }
      });


      $('#permission-read-return-button').change(function () {
        if ($(this).is(':checked')) {
              $('#permission-is-read-return').val(1);
          }else{
              $('#permission-is-read-return').val(0);
          }
      });
      $('#permission-update-return-button').change(function () {
        if ($(this).is(':checked')) {
              $('#permission-is-update-return').val(1);
          }else{
              $('#permission-is-update-return').val(0);
          }
      });
      $('#permission-delete-return-button').change(function () {
        if ($(this).is(':checked')) {
              $('#permission-is-delete-return').val(1);
          }else{
              $('#permission-is-delete-return').val(0);
          }
      });


      $('#permission-create-notification-button').change(function () {
        if ($(this).is(':checked')) {
              $('#permission-is-create-notification').val(1);
          }else{
              $('#permission-is-create-notification').val(0);
          }
      });
      $('#permission-read-notification-button').change(function () {
        if ($(this).is(':checked')) {
              $('#permission-is-read-notification').val(1);
          }else{
              $('#permission-is-read-notification').val(0);
          }
      });
      $('#permission-delete-notification-button').change(function () {
        if ($(this).is(':checked')) {
              $('#permission-is-delete-notification').val(1);
          }else{
              $('#permission-is-delete-notification').val(0);
          }
      });

      $('#permission-read-transaction-button').change(function () {
        if ($(this).is(':checked')) {
              $('#permission-is-read-transaction').val(1);
          }else{
              $('#permission-is-read-transaction').val(0);
          }
      });
      $('#permission-read-fund-button').change(function () {
        if ($(this).is(':checked')) {
              $('#permission-is-read-fund').val(1);
          }else{
              $('#permission-is-read-fund').val(0);
          }
      });


      $('#permission-read-settings-button').change(function () {
        if ($(this).is(':checked')) {
              $('#permission-is-read-settings').val(1);
          }else{
              $('#permission-is-read-settings').val(0);
          }
      });

      $('#permission-update-settings-button').change(function () {
        if ($(this).is(':checked')) {
              $('#permission-is-update-settings').val(1);
          }else{
              $('#permission-is-update-settings').val(0);
          }
      });

      $('#permission-create-location-button').change(function () {
        if ($(this).is(':checked')) {
              $('#permission-is-create-location').val(1);
          }else{
              $('#permission-is-create-location').val(0);
          }
      });

      $('#permission-read-location-button').change(function () {
        if ($(this).is(':checked')) {
              $('#permission-is-read-location').val(1);
          }else{
              $('#permission-is-read-location').val(0);
          }
      });
      $('#permission-update-location-button').change(function () {
        if ($(this).is(':checked')) {
              $('#permission-is-update-location').val(1);
          }else{
              $('#permission-is-update-location').val(0);
          }
      });

      $('#permission-delete-location-button').change(function () {
        if ($(this).is(':checked')) {
              $('#permission-is-delete-location').val(1);
          }else{
              $('#permission-is-delete-location').val(0);
          }
      });


      $('#permission-create-report-button').change(function () {
        if ($(this).is(':checked')) {
              $('#permission-is-create-report').val(1);
          }else{
              $('#permission-is-create-report').val(0);
          }
      });

      $('#permission-read-report-button').change(function () {
        if ($(this).is(':checked')) {
              $('#permission-is-read-report').val(1);
          }else{
              $('#permission-is-read-report').val(0);
          }
      });


      $('#permission-create-faq-button').change(function () {
        if ($(this).is(':checked')) {
              $('#permission-is-create-faq').val(1);
          }else{
              $('#permission-is-create-faq').val(0);
          }
      });

      $('#permission-read-faq-button').change(function () {
        if ($(this).is(':checked')) {
              $('#permission-is-read-faq').val(1);
          }else{
              $('#permission-is-read-faq').val(0);
          }
      });
      $('#permission-update-faq-button').change(function () {
        if ($(this).is(':checked')) {
              $('#permission-is-update-faq').val(1);
          }else{
              $('#permission-is-update-faq').val(0);
          }
      });

      $('#permission-delete-faq-button').change(function () {
        if ($(this).is(':checked')) {
              $('#permission-is-delete-faq').val(1);
          }else{
              $('#permission-is-delete-faq').val(0);
          }
      });
</script>
<script>
    $(document).on('click','.delete-system-user',function(){
          if(confirm('Are you sure? Want to delete system user.')){
              
              id = $(this).data("id");
          
              // image = $(this).data("image");
              $.ajax({
                  url : 'public/db-operation.php',
                  type: "get",
                  data: 'id='+id+'&delete_system_user=1',
                  success: function(result){
                      if(result==0){
                          $('#system-users').bootstrapTable('refresh');
                      }
                      else{
                          alert('Error! System user could not be deleted.');
                      }
                      
                  }
              });
          }
      });
</script>
<script>
  $('#city_ids').select2({
    width: 'element',
    placeholder: 'type in city name to search',
    // minimumInputLength: 3,
    /* ajax: {
      url: 'api/get-bootstrap-table-data.php',
      dataType: 'json',
      type: "GET",
      quietMillis: 1,
      data:function(params){
        return{
          products_list: 1,
          name:params.term,
        };
      },
      processResults:function(data) {
        // alert(JSON.stringify(data));
        return {
          results: data
        };
      },
    } */
  });
</script>


