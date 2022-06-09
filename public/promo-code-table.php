<?php

include_once('includes/crud.php');
$db = new Database();
$db->connect();
$db->sql("SET NAMES 'utf8'");

include('includes/variables.php');
include_once('includes/custom-functions.php');

$fn = new custom_functions;
$config = $fn->get_configurations();
?>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.16.0/jquery.validate.min.js"></script>
<section class="content-header">
  <h1>Promo Codes /<small><a href="home.php"><i class="fa fa-home"></i> Home</a></small></h1>
</section>
<!-- Main content -->
<section class="content">
  <!-- Main row -->
  <div class="row">
    <div class="col-md-6">
      <?php if ($permissions['promo_codes']['create'] == 0) { ?>
        <div class="alert alert-danger">You have no permission to create promo code.</div>
      <?php } ?>
      <!-- general form elements -->
      <div class="box box-primary">
        <div class="box-header with-border">
          <h3 class="box-title">Manage Promo Code</h3>

        </div><!-- /.box-header -->
        <!-- form start -->
        <form method="post" id="add_form" action="public/db-operation.php">
          <input type="hidden" id="add_promo_code" name="add_promo_code" required="" value="1" aria-required="true">
          <div class="box-body">
            <div class="form-group col-md-6">
              <label for="">Promo Code</label>
              <input type="text" class="form-control" name="promo_code">
            </div>
            <div class="form-group col-md-6">
              <label for="">Message</label>
              <input type="text" class="form-control" name="message">
            </div>
            <div class="form-group col-md-6">
              <label for="">Start Date</label>
              <input type="date" class="form-control" name="start_date" id="start_date">
            </div>
            <div class="form-group col-md-6">
              <label for="">End Date</label>
              <input type="date" class="form-control" name="end_date" id="end_date">
            </div>
            <div class="form-group col-md-6">
              <label for="">No. Of Users</label>
              <input type="number" class="form-control" name="no_of_users">
            </div>
            <div class="form-group col-md-6">
              <label for="">Minimum Order Amount</label>
              <input type="number" class="form-control" name="minimum_order_amount" step="any">
            </div>
            <div class="form-group col-md-6">
              <label for="">Discount</label>
              <input type="number" class="form-control" name="discount" id="discount" step="any">
            </div>
            <div class="form-group col-md-6">
              <label for="">Discount Type</label>
              <select name="discount_type" class="form-control">
                <option value="">Select</option>
                <option value="percentage">Percentage</option>
                <option value="amount">Amount</option>
              </select>
            </div>
            <div class="form-group col-md-6">
              <label for="">Max Discount Amount</label>
              <input type="text" class="form-control" name="max_discount_amount" id="max_discount_amount">
            </div>
            <div class="form-group col-md-6">
              <label for="">Repeat Usage</label>
              <select name="repeat_usage" id="repeat_usage" class="form-control">
                <option value="">Select</option>
                <option value="1">Allowed</option>
                <option value="0">Not Allowed</option>
              </select>
            </div>

            <div class="form-group col-md-6">
              <label for="">Status</label>
              <select name="status" id="status" class="form-control">
                <option value="">Select</option>
                <option value="1">Active</option>
                <option value="0">Deactive</option>
              </select>
            </div>
            <div class="form-group col-md-6" id="repeat_usage_block" style="display:none">
              <label for="">No. Of Repeat Usage</label>
              <input type="number" class="form-control" name="no_of_repeat_usage" id="no_of_repeat_usage">
            </div>


          </div><!-- /.box-body -->

          <div class="box-footer">
            <button type="submit" class="btn btn-primary" id="submit_btn" name="btnAdd">Add</button>
            <input type="reset" class="btn-warning btn" value="Clear" />

          </div>
          <div class="form-group">

            <div id="result" style="display: none;"></div>
          </div>
        </form>
      </div><!-- /.box -->
    </div>
    <!-- Left col -->
    <div class="col-xs-12">
      <div class="box">
        <div class="box-header">
          <h3 class="box-title">Promo Codes</h3>
        </div>
        <?php if ($permissions['promo_codes']['read'] == 1) { ?>
          <div class="box-body table-responsive">
            <table class="table table-hover" data-toggle="table" id="promo-codes" data-url="api-firebase/get-bootstrap-table-data.php?table=promo-codes" data-page-list="[5, 10, 20, 50, 100, 200]" data-show-refresh="true" data-show-columns="true" data-side-pagination="server" data-pagination="true" data-search="true" data-trim-on-search="false" data-sort-name="id" data-sort-order="desc">
              <thead>
                <tr>
                  <th data-field="id" data-sortable="true">ID</th>
                  <th data-field="promo_code" data-sortable="true">Promo Code</th>
                  <th data-field="message" data-sortable="true">Message</th>
                  <th data-field="start_date" data-sortable="true">Start Date</th>
                  <th data-field="end_date" data-sortable="true">End Date</th>
                  <th data-field="no_of_users" data-sortable="true">No Of Users</th>
                  <th data-field="minimum_order_amount" data-sortable="true">Minimum Order Amount</th>
                  <th data-field="discount" data-sortable="true">Discount</th>
                  <th data-field="discount_type" data-sortable="true">Discount Type</th>
                  <th data-field="max_discount_amount" data-sortable="true" data-visible="false">Max Discount Amount</th>
                  <th data-field="repeat_usage" data-sortable="true" data-visible="false">Repeat Usage</th>
                  <th data-field="no_of_repeat_usage" data-sortable="true" data-visible="false">No. Of Repeat Usage</th>
                  <th data-field="status">Status</th>
                  <th data-field="date_created">Date Created</th>
                  <th data-field="operate" data-events="actionEvents">Action</th>
                </tr>
              </thead>
            </table>
          </div>
        <?php } else { ?>
          <div class="alert alert-danger">You have no permission to view promo codes</div>
        <?php } ?>
      </div>
    </div>
    <div class="separator"> </div>
  </div>
  <div class="modal fade" id='editPromoCodeModal' tabindex="1" role="dialog" aria-labelledby="myLargeModalLabel">
    <div class="modal-dialog modal-md" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
          <h4 class="modal-title" id="myModalLabel">Edit Promo Code</h4>
        </div>

        <div class="modal-body">
          <?php if ($permissions['promo_codes']['update'] == 0) { ?>
            <div class="alert alert-danger">You have no permission to update promo code.</div>
          <?php } ?>
          <div class="box-body">
            <form id="update_form" method="POST" action="public/db-operation.php" data-parsley-validate class="form-horizontal form-label-left">
              <input type='hidden' name="promo_code_id" id="promo_code_id" value='' />
              <input type='hidden' name="update_promo_code" id="update_promo_code" value='1' />
              <div class="form-group">
                <label for="">Promo Code</label>
                <input type="text" class="form-control" name="update_promo" id="update_promo">
              </div>
              <div class="form-group">
                <label for="">Message</label>
                <input type="text" class="form-control" name="update_message" id="update_message">
              </div>
              <div class="form-group">
                <label for="">Start Date</label>
                <input type="date" class="form-control" name="update_start_date" id="update_start_date">
              </div>
              <div class="form-group">
                <label for="">End Date</label>
                <input type="date" class="form-control" name="update_end_date" id="update_end_date">
              </div>
              <div class="form-group">
                <label for="">No. Of Users</label>
                <input type="text" class="form-control" name="update_no_of_users" id="update_no_of_users">
              </div>
              <div class="form-group">
                <label for="">Minimum Order Amount</label>
                <input type="text" class="form-control" name="update_minimum_order_amount" id="update_minimum_order_amount">
              </div>
              <div class="form-group">
                <label for="">Discount</label>
                <input type="text" class="form-control" name="update_discount" id="update_discount">
              </div>
              <div class="form-group">
                <label for="">Discount Type</label>
                <select name="update_discount_type" id="update_discount_type" class="form-control">
                  <option value="">Select</option>
                  <option value="percentage">Percentage</option>
                  <option value="amount">Amount</option>
                </select>
              </div>
              <div class="form-group">
                <label for="">Max Discount Amount</label>
                <input type="text" class="form-control" name="update_max_discount_amount" id="update_max_discount_amount">
              </div>
              <div class="form-group">
                <label for="">Repeat Usage</label>
                <select name="update_repeat_usage" id="update_repeat_usage" class="form-control">
                  <option value="">Select</option>
                  <option value="1">Allowed</option>
                  <option value="0">Not Allowed</option>
                </select>
              </div>
              <div class="form-group" id="update_repeat_usage_block" style="display:none">
                <label for="">No. Of Repeat Usage</label>
                <input type="text" class="form-control" name="update_no_of_repeat_usage" id="update_no_of_repeat_usage">
              </div>
              <div class="form-group">
                <label class="control-label col-md-3 col-sm-3 col-xs-12">Status</label>
                <div class="col-md-6 col-sm-6 col-xs-12">
                  <div id="status" class="btn-group">
                    <label class="btn btn-default" data-toggle-class="btn-primary" data-toggle-passive-class="btn-default">
                      <input type="radio" name="status" value="0"> Deactive
                    </label>
                    <label class="btn btn-primary" data-toggle-class="btn-primary" data-toggle-passive-class="btn-default">
                      <input type="radio" name="status" value="1"> Active
                    </label>
                  </div>
                </div>
              </div>
              <input type="hidden" id="id" name="id">
              <div class="ln_solid"></div>
              <div class="form-group">
                <div class="col-md-6 col-sm-6 col-xs-12 col-md-offset-3">
                  <button type="submit" id="update_btn" class="btn btn-success">Update</button>
                </div>
              </div>
              <div class="form-group">

                <div class="row">
                  <div class="col-md-offset-3 col-md-8" style="display:none;" id="update_result"></div>
                </div>
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
    rules: {
      promo_code: "required",
      message: "required",
      start_date: "required",
      end_date: "required",
      no_of_users: "required",
      minimum_order_amount: "required",
      max_discount_amount: "required",
      discount: "required",
      discount_type: "required",
      repeat_usage: "required",
      status: "required",

    }
  });
  $('#update_form').validate({
    rules: {
      update_promo: "required",
      update_message: "required",
      update_start_date: "required",
      update_end_date: "required",
      update_no_of_users: "required",
      update_minimum_order_amount: "required",
      update_discount: "required",
      update_discount_type: "required",
      update_repeat_usage: "required",

    }
  });
  $('#add_form').on('submit', function(e) {
    e.preventDefault();
    var formData = new FormData(this);
    if ($("#add_form").validate().form()) {
      if (confirm('Are you sure?Want to Add Promo Code')) {
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
            $('#add_form')[0].reset();
            $('#promo-codes').bootstrapTable('refresh');
          }
        });
      }
    }
  });
  $('#update_form').on('submit', function(e) {
    e.preventDefault();
    var formData = new FormData(this);
    if ($("#update_form").validate().form()) {
      $.ajax({
        type: 'POST',
        url: $(this).attr('action'),
        data: formData,
        beforeSend: function() {
          $('#update_btn').html('Please wait..');
        },
        cache: false,
        contentType: false,
        processData: false,
        success: function(result) {
          $('#update_result').html(result);
          $('#update_result').show().delay(6000).fadeOut();
          $('#update_btn').html('Update');
          $('#update_form')[0].reset();
          $('#promo-codes').bootstrapTable('refresh');
          setTimeout(function() {
            $('#editPromoCodeModal').modal('hide');
          }, 3000);
        }
      });
    }
  });
  window.actionEvents = {

    'click .edit-promo-code': function(e, value, row, index) {
      $("input[name=status][value=1]").prop('checked', true);
      if ($(row.status).text() == 'Deactive')
        $("input[name=status][value=0]").prop('checked', true);
      $('#promo_code_id').val(row.id);
      $('#update_promo').val(row.promo_code);
      $('#update_message').val(row.message);
      $('#update_start_date').val(row.start_date);
      $('#update_end_date').val(row.end_date);
      $('#update_no_of_users').val(row.no_of_users);
      $('#update_minimum_order_amount').val(row.minimum_order_amount);
      $('#update_discount').val(row.discount);
      $('#update_discount_type').val(row.discount_type);
      $('#update_max_discount_amount').val(row.max_discount_amount);
      if (row.repeat_usage == 'Allowed') {
        $('#update_repeat_usage').val(1);
      } else {
        $('#update_repeat_usage').val(0);
      }
      if (row.repeat_usage == 'Allowed') {
        $('#update_repeat_usage_block').show();
        $('#update_no_of_repeat_usage').val(row.no_of_repeat_usage);

      }
    }
  }
  $(document).on('click', '.delete-promo-code', function() {
    if (confirm('Are you sure? Want to delete promo code.')) {
      id = $(this).data("id");
      $.ajax({
        url: 'public/db-operation.php',
        type: "get",
        data: 'id=' + id + '&delete_promo_code=1',
        success: function(result) {
          if (result == 0) {
            $('#promo-codes').bootstrapTable('refresh');
          }
          if (result == 2) {
            alert('You have no permission to delete promo code');
          }
          if (result == 1) {
            alert('Error! Promo code could not be deleted.');
          }
        }
      });
    }
  });
  $("#repeat_usage").change(function() {
    repeat_usage = $("#repeat_usage").val();
    if (repeat_usage == 1) {
      $("#repeat_usage_block").show();
    } else {
      $("#repeat_usage_block").hide();
    }
  });
  $("#update_repeat_usage").change(function() {
    update_repeat_usage = $("#update_repeat_usage").val();
    if (update_repeat_usage == 1) {
      $("#update_repeat_usage_block").show();
    } else {
      $("#update_repeat_usage_block").hide();
    }
  });
</script>